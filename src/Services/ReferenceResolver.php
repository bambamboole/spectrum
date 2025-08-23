<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Services;

use Bambamboole\OpenApi\Exceptions\ReferenceResolutionException;

class ReferenceResolver
{
    private static ?self $instance = null;

    private array $resolvedCache = [];

    private array $resolutionStack = [];

    public function __construct(
        private readonly array $document
    ) {}

    public static function initialize(array $data): void
    {
        self::$instance = new self($data);
    }

    public static function resolveRef(array $data): array
    {
        if (self::$instance === null) {
            self::initialize($data);
        }
        if (isset($data['$ref'])) {
            $resolvedData = self::$instance->resolve($data['$ref']);
            if (is_array($resolvedData)) {
                return self::resolveRef($resolvedData);
            }
            throw new \InvalidArgumentException('Resolved reference must be an array');
        }

        return $data;
    }

    public static function clear(): void
    {
        self::$instance = null;
    }

    public function resolve(string $ref): mixed
    {
        // Check cache first
        if (isset($this->resolvedCache[$ref])) {
            return $this->resolvedCache[$ref];
        }

        // Check for circular reference
        if ($this->isCircularReference($ref)) {
            throw new ReferenceResolutionException("Circular reference detected: {$ref}");
        }

        // Add to resolution stack
        $this->resolutionStack[] = $ref;

        try {
            $resolved = $this->resolveReference($ref);
            $this->resolvedCache[$ref] = $resolved;

            return $resolved;
        } finally {
            // Remove from resolution stack
            array_pop($this->resolutionStack);
        }
    }

    public function isCircularReference(string $ref): bool
    {
        return in_array($ref, $this->resolutionStack, true);
    }

    private function resolveReference(string $ref): mixed
    {
        // Only support local references for now (starting with #/)
        if (! str_starts_with($ref, '#/')) {
            throw new ReferenceResolutionException("External references not supported: {$ref}");
        }

        // Parse JSON Pointer (RFC 6901)
        $pointer = substr($ref, 2); // Remove '#/' prefix
        $path = $this->parseJsonPointer($pointer);

        return $this->resolveJsonPointer($path);
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

    private function resolveJsonPointer(array $path): mixed
    {
        $current = $this->document;

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
