<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class Components
{
    public function __construct(
        public array $schemas = [],
        public array $responses = [],
        public array $parameters = [],
        public array $examples = [],
        public array $requestBodies = [],
        public array $headers = [],
        public array $securitySchemes = [],
        public array $links = [],
        public array $callbacks = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            schemas: self::parseSchemas($data['schemas'] ?? []),
            responses: $data['responses'] ?? [],
            parameters: $data['parameters'] ?? [],
            examples: $data['examples'] ?? [],
            requestBodies: $data['requestBodies'] ?? [],
            headers: $data['headers'] ?? [],
            securitySchemes: $data['securitySchemes'] ?? [],
            links: $data['links'] ?? [],
            callbacks: $data['callbacks'] ?? [],
        );
    }

    private static function parseSchemas(array $schemas): array
    {
        $parsed = [];
        foreach ($schemas as $key => $schema) {
            $parsed[$key] = Schema::fromArray($schema);
        }

        return $parsed;
    }
}
