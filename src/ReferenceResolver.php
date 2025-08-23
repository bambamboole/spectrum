<?php declare(strict_types=1);

namespace Bambamboole\OpenApi;

use Bambamboole\OpenApi\Exceptions\ReferenceResolutionException;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class ReferenceResolver
{
    private static ?self $instance = null;

    private array $resolvedCache = [];

    private array $resolutionStack = [];

    private array $fileCache = [];

    public function __construct(
        private readonly array $document,
        private readonly ?string $currentFilePath = null,
        private readonly Filesystem $filesystem = new Filesystem
    ) {}

    public static function initialize(array $data, ?string $currentFilePath = null): void
    {
        if (self::$instance) {
            return;
        }
        self::$instance = new self($data, $currentFilePath);
    }

    public static function resolveRef(array $data): array
    {
        if (! isset($data['$ref'])) {
            return $data;
        }
        if (self::$instance === null) {
            throw new ReferenceResolutionException('ReferenceResolver not initialized. Call ReferenceResolver::initialize() first.');
        }
        $resolvedData = self::$instance->resolve($data['$ref']);
        if (is_array($resolvedData)) {
            return self::resolveRef($resolvedData);
        }
        throw new \InvalidArgumentException('Resolved reference must be an array');
    }

    public static function clear(): void
    {
        if (self::$instance) {
            self::$instance->fileCache = [];
        }
        self::$instance = null;
    }

    public function resolve(string $ref): mixed
    {
        $fullRef = $this->buildFullReference($ref);

        if (isset($this->resolvedCache[$fullRef])) {
            return $this->resolvedCache[$fullRef];
        }

        if ($this->isCircularReference($fullRef)) {
            throw new ReferenceResolutionException("Circular reference detected: {$ref}");
        }

        $this->resolutionStack[] = $fullRef;

        try {
            $resolved = $this->resolveReference($ref);
            $this->resolvedCache[$fullRef] = $resolved;

            return $resolved;
        } finally {
            array_pop($this->resolutionStack);
        }
    }

    private function buildFullReference(string $ref): string
    {
        if (str_starts_with($ref, '#/')) {
            $currentFile = $this->currentFilePath ?? '';

            return $currentFile.$ref;
        }

        [$filePath, $jsonPointer] = $this->parseExternalReference($ref);
        $resolvedPath = $this->resolveFilePath($filePath);

        return $resolvedPath.'#'.$jsonPointer;
    }

    public function isCircularReference(string $ref): bool
    {
        return in_array($ref, $this->resolutionStack, true);
    }

    private function resolveReference(string $ref): mixed
    {
        if (str_starts_with($ref, '#/')) {
            $pointer = substr($ref, 2);
            $path = $this->parseJsonPointer($pointer);

            return $this->resolveJsonPointer($path, $this->document);
        }

        return $this->resolveExternalReference($ref);
    }

    private function resolveExternalReference(string $ref): mixed
    {
        [$filePath, $jsonPointer] = $this->parseExternalReference($ref);
        $resolvedPath = $this->resolveFilePath($filePath);

        $this->validateFilePath($resolvedPath);
        $externalDocument = $this->loadExternalFile($resolvedPath);

        if ($jsonPointer === '') {
            return $externalDocument;
        }

        $path = $this->parseJsonPointer(substr($jsonPointer, 1));

        return $this->resolveJsonPointer($path, $externalDocument);
    }

    private function parseExternalReference(string $ref): array
    {
        $parts = explode('#', $ref, 2);

        return [$parts[0], $parts[1] ?? ''];
    }

    private function resolveFilePath(string $filePath): string
    {
        if (str_starts_with($filePath, '/')) {
            return $filePath;
        }

        if (! $this->currentFilePath) {
            throw new ReferenceResolutionException("Cannot resolve relative path without current file context: {$filePath}");
        }

        $resolvedPath = dirname($this->currentFilePath).'/'.$filePath;

        return str_replace('//', '/', $resolvedPath);
    }

    private function validateFilePath(string $path): void
    {
        if (! file_exists($path)) {
            throw new ReferenceResolutionException("File not found: {$path}");
        }

        $realPath = realpath($path);
        $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        if (! in_array($extension, ['json', 'yaml', 'yml'])) {
            throw new ReferenceResolutionException("Unsupported file type: {$path}");
        }

        if (str_contains($path, '../')) {
            $normalizedPath = realpath($path);
            if ($this->currentFilePath) {
                $currentDir = dirname(realpath($this->currentFilePath));
                if (! str_starts_with($normalizedPath, $currentDir)) {
                    throw new ReferenceResolutionException("Directory traversal not allowed: {$path}");
                }
            }
        }
    }

    private function loadExternalFile(string $path): array
    {
        $realPath = realpath($path);

        if (isset($this->fileCache[$realPath])) {
            return $this->fileCache[$realPath];
        }

        if (! $this->filesystem->exists($path)) {
            throw new ReferenceResolutionException("File not found: {$path}");
        }

        $content = $this->filesystem->get($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        try {
            $data = match ($extension) {
                'json' => json_decode($content, true, 512, JSON_THROW_ON_ERROR),
                'yaml', 'yml' => Yaml::parse($content),
                default => throw new ReferenceResolutionException("Unsupported file type: {$path}")
            };
        } catch (\Exception $e) {
            throw new ReferenceResolutionException("Failed to parse file {$path}: ".$e->getMessage());
        }

        if (! is_array($data)) {
            throw new ReferenceResolutionException("External file must contain an object or array: {$path}");
        }

        $this->fileCache[$realPath] = $data;

        return $data;
    }

    private function parseJsonPointer(string $pointer): array
    {
        if ($pointer === '') {
            return [];
        }

        return array_map(
            fn (string $segment) => str_replace(['~1', '~0'], ['/', '~'], $segment),
            explode('/', $pointer)
        );
    }

    private function resolveJsonPointer(array $path, array $document): mixed
    {
        $current = $document;

        foreach ($path as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                throw new ReferenceResolutionException(
                    'Reference path not found: #/'.implode('/', $path)
                );
            }
            $current = $current[$segment];
        }

        return $current;
    }
}
