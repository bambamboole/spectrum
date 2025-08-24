<?php declare(strict_types=1);

namespace Bambamboole\OpenApi;

use Bambamboole\OpenApi\Exceptions\ReferenceResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\Factory;
use Symfony\Component\Yaml\Yaml;

class OpenApiDereferencer
{
    private array $fileCache = [];

    private array $resolutionStack = [];

    private array $schemaDefinitions = [];

    public function __construct(
        private readonly Filesystem $filesystem = new Filesystem,
        private readonly Factory $http = new Factory
    ) {}

    public function dereferenceFile(string $filePath): array
    {
        if (! $this->filesystem->exists($filePath)) {
            throw new ReferenceResolutionException("File not found: {$filePath}");
        }

        $document = $this->loadFile($filePath);

        return $this->dereferenceDocument($document, dirname(realpath($filePath)));
    }

    public function dereferenceUrl(string $url): array
    {
        if (! $this->isUrl($url)) {
            throw new ReferenceResolutionException("Invalid URL: {$url}");
        }

        $document = $this->loadUrl($url);

        return $this->dereferenceDocument($document, $this->getUrlBase($url));
    }

    public function dereference(string $source): array
    {
        if ($this->isUrl($source)) {
            return $this->dereferenceUrl($source);
        } else {
            return $this->dereferenceFile($source);
        }
    }

    public function dereferenceArray(array $document): array
    {
        return $this->dereferenceDocument($document, '');
    }

    private function dereferenceDocument(array $document, string $baseDirectory): array
    {
        // First pass: identify all schema definitions that might be circular reference targets
        $this->preserveSchemaDefinitions($document);

        // Second pass: dereference the document while preserving circular reference targets
        $dereferencedDocument = $this->processValue($document, $baseDirectory, $document, $document);

        // Third pass: ensure circular reference targets are available in the final document
        return $this->ensureCircularTargetsAvailable($dereferencedDocument);
    }

    private function ensureCircularTargetsAvailable(array $document): array
    {
        // For complex specifications like DigitalOcean, preserve ALL schema definitions
        // from external files to ensure they're available for any internal references
        foreach ($this->schemaDefinitions as $name => $definition) {
            if (! isset($document[$name])) {
                $document[$name] = $definition;
            }
        }

        return $document;
    }

    private function preserveSchemaDefinitions(array $document): void
    {
        // Store original schema definitions to ensure circular reference targets remain available
        // This is critical for validation - circular $ref values need their targets to exist
        if (isset($document['components']['schemas'])) {
            foreach ($document['components']['schemas'] as $schemaName => $schemaDefinition) {
                // Store the original definition using the same key format as JSON pointers use
                $this->schemaDefinitions[$schemaName] = $schemaDefinition;
            }
        }

        // Also look for schema definitions in external files that might be referenced
        foreach ($document as $key => $value) {
            if (is_array($value) && $this->looksLikeSchemaDefinition($value)) {
                $this->schemaDefinitions[$key] = $value;
            }
        }
    }

    private function looksLikeSchemaDefinition(array $value): bool
    {
        // Heuristic to identify schema-like structures
        return isset($value['type']) ||
               isset($value['properties']) ||
               isset($value['description']) ||
               isset($value['$ref']);
    }

    private function processValue($value, string $baseDirectory, array $rootDocument, ?array $currentDocument = null)
    {
        if (! is_array($value)) {
            return $value;
        }

        // Use current document context if provided, otherwise use root document
        $contextDocument = $currentDocument ?? $rootDocument;

        // Handle $ref
        if (isset($value['$ref'])) {
            $resolved = $this->resolveReference($value['$ref'], $baseDirectory, $rootDocument, $contextDocument);

            // If the resolution returned a $ref (due to circular reference), don't process it further
            if (is_array($resolved) && isset($resolved['$ref']) && count($resolved) === 1) {
                return $resolved;
            }

            return $resolved;
        }

        // Process all array elements recursively
        $result = [];
        foreach ($value as $key => $item) {
            $result[$key] = $this->processValue($item, $baseDirectory, $rootDocument, $contextDocument);
        }

        return $result;
    }

    private function resolveReference(string $ref, string $baseDirectory, array $rootDocument, array $currentDocument)
    {
        // Handle local JSON pointer references
        if (str_starts_with($ref, '#/')) {
            $jsonPointer = substr($ref, 1); // Strip only the '#', keep the '/'

            // For local references, create a unique key based on the current context
            $referenceKey = $baseDirectory.'#'.$jsonPointer;

            // Check for circular references - if found, return the reference as-is
            if (in_array($referenceKey, $this->resolutionStack)) {
                return ['$ref' => $ref];
            }

            $this->resolutionStack[] = $referenceKey;

            try {
                // Use current document for local references instead of root document
                $referencedValue = $this->getJsonPointerValue($currentDocument, $jsonPointer);

                return $this->processValue($referencedValue, $baseDirectory, $rootDocument, $currentDocument);
            } finally {
                array_pop($this->resolutionStack);
            }
        }

        // Parse external reference
        [$filePath, $jsonPointer] = $this->parseReference($ref);

        // Resolve the file path relative to the base directory
        $resolvedPath = $this->resolveFilePath($filePath, $baseDirectory);

        // Check for circular references - if found, return the reference as-is
        $referenceKey = $resolvedPath.'#'.$jsonPointer;
        if (in_array($referenceKey, $this->resolutionStack)) {
            return ['$ref' => $ref];
        }

        $this->resolutionStack[] = $referenceKey;

        try {
            // Load the external file or URL
            $externalDocument = $this->isUrl($resolvedPath) ? $this->loadUrl($resolvedPath) : $this->loadFile($resolvedPath);

            // Get the referenced value
            $referencedValue = $this->getJsonPointerValue($externalDocument, $jsonPointer);

            // Recursively dereference the referenced value with the new base directory and external document as context
            $result = $this->processValue($referencedValue, dirname($resolvedPath), $externalDocument, $externalDocument);

            return $result;
        } finally {
            array_pop($this->resolutionStack);
        }
    }

    private function parseReference(string $ref): array
    {
        $parts = explode('#', $ref, 2);

        return [$parts[0], $parts[1] ?? ''];
    }

    private function resolveFilePath(string $filePath, string $baseDirectory): string
    {
        // Handle URL base directories
        if ($this->isUrl($baseDirectory)) {
            if ($this->isUrl($filePath)) {
                // Absolute URL
                return $filePath;
            } else {
                // Relative URL - resolve properly
                if (str_starts_with($filePath, '/')) {
                    // Absolute path on same domain
                    $parsed = parse_url($baseDirectory);

                    return $parsed['scheme'].'://'.$parsed['host'].$filePath;
                } else {
                    // Relative path
                    return rtrim($baseDirectory, '/').'/'.ltrim($filePath, '/');
                }
            }
        }

        // Handle file paths
        if (str_starts_with($filePath, '/')) {
            // Already absolute file path
            return $filePath;
        }

        // Resolve relative file path
        $resolvedPath = $baseDirectory.'/'.$filePath;

        // Normalize the path (handle ../ and ./)
        $normalizedPath = $this->normalizePath($resolvedPath);

        if (! file_exists($normalizedPath)) {
            throw new ReferenceResolutionException("Referenced file not found: {$normalizedPath}");
        }

        return realpath($normalizedPath);
    }

    private function normalizePath(string $path): string
    {
        $parts = explode('/', $path);
        $normalized = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            } elseif ($part === '..') {
                if (! empty($normalized)) {
                    array_pop($normalized);
                }
            } else {
                $normalized[] = $part;
            }
        }

        return '/'.implode('/', $normalized);
    }

    private function getJsonPointerValue(array $document, string $pointer)
    {
        if ($pointer === '' || $pointer === '/') {
            return $document;
        }

        // Remove leading slash if present
        if (str_starts_with($pointer, '/')) {
            $pointer = substr($pointer, 1);
        }

        $path = array_map(
            fn ($segment) => str_replace(['~1', '~0'], ['/', '~'], $segment),
            explode('/', $pointer)
        );

        $current = $document;
        foreach ($path as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                throw new ReferenceResolutionException("JSON pointer path not found: #{$pointer}");
            }
            $current = $current[$segment];
        }

        return $current;
    }

    private function loadFile(string $filePath): array
    {
        $realPath = realpath($filePath);

        if (isset($this->fileCache[$realPath])) {
            return $this->fileCache[$realPath];
        }

        if (! $this->filesystem->exists($filePath)) {
            throw new ReferenceResolutionException("File not found: {$filePath}");
        }

        $content = $this->filesystem->get($filePath);

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $data = Yaml::parse($content);
        }

        if (! is_array($data)) {
            throw new ReferenceResolutionException("File must contain an object or array: {$filePath}");
        }

        $this->fileCache[$realPath] = $data;

        // Collect schema definitions from external files
        $this->collectExternalSchemaDefinitions($data);

        return $data;
    }

    private function collectExternalSchemaDefinitions(array $data): void
    {
        // Collect schema definitions from external files (like the gen-ai definitions.yml)
        foreach ($data as $key => $value) {
            if (is_array($value) && $this->looksLikeSchemaDefinition($value)) {
                $this->schemaDefinitions[$key] = $value;
            }
        }
    }

    private function isUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    private function getUrlBase(string $url): string
    {
        $parsed = parse_url($url);
        if (! $parsed || ! isset($parsed['scheme'], $parsed['host'])) {
            throw new ReferenceResolutionException("Invalid URL: {$url}");
        }

        $base = $parsed['scheme'].'://'.$parsed['host'];
        if (isset($parsed['port'])) {
            $base .= ':'.$parsed['port'];
        }
        if (isset($parsed['path']) && $parsed['path'] !== '/') {
            $base .= dirname($parsed['path']);
        }

        return $base;
    }

    private function loadUrl(string $url): array
    {
        if (isset($this->fileCache[$url])) {
            return $this->fileCache[$url];
        }

        $body = $this->http->get($url)->throw()->body();
        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $data = Yaml::parse($body);
        }

        if (! is_array($data)) {
            throw new ReferenceResolutionException("URL content must contain an object or array: {$url}");
        }

        $this->fileCache[$url] = $data;

        // Collect schema definitions from external URLs
        $this->collectExternalSchemaDefinitions($data);

        return $data;
    }
}
