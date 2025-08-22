<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Exceptions\ParseException;

readonly class Schema
{
    public function __construct(
        public ?string $type = null,
        public ?string $format = null,
        public ?string $title = null,
        public ?string $description = null,
        public mixed $default = null,
        public mixed $example = null,
        public ?int $minLength = null,
        public ?int $maxLength = null,
        public ?string $pattern = null,
        public int|float|null $minimum = null,
        public int|float|null $maximum = null,
        public ?bool $exclusiveMinimum = null,
        public ?bool $exclusiveMaximum = null,
        public int|float|null $multipleOf = null,
        public ?int $minItems = null,
        public ?int $maxItems = null,
        public ?bool $uniqueItems = null,
        public ?Schema $items = null,
        public ?array $properties = null,
        public ?array $required = null,
        public bool|Schema|null $additionalProperties = null,
        public ?int $minProperties = null,
        public ?int $maxProperties = null,
        public ?array $enum = null,
        public ?array $allOf = null,
        public ?array $anyOf = null,
        public ?array $oneOf = null,
        public ?Schema $not = null,
        public ?string $ref = null,
    ) {}

    public static function fromArray(array $data): self
    {
        self::validateSchema($data);

        return new self(
            type: $data['type'] ?? null,
            format: $data['format'] ?? null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            default: $data['default'] ?? null,
            example: $data['example'] ?? null,
            minLength: $data['minLength'] ?? null,
            maxLength: $data['maxLength'] ?? null,
            pattern: $data['pattern'] ?? null,
            minimum: $data['minimum'] ?? null,
            maximum: $data['maximum'] ?? null,
            exclusiveMinimum: $data['exclusiveMinimum'] ?? null,
            exclusiveMaximum: $data['exclusiveMaximum'] ?? null,
            multipleOf: $data['multipleOf'] ?? null,
            minItems: $data['minItems'] ?? null,
            maxItems: $data['maxItems'] ?? null,
            uniqueItems: $data['uniqueItems'] ?? null,
            items: isset($data['items']) ? self::fromArray($data['items']) : null,
            properties: self::parseProperties($data['properties'] ?? null),
            required: $data['required'] ?? null,
            additionalProperties: self::parseAdditionalProperties($data['additionalProperties'] ?? null),
            minProperties: $data['minProperties'] ?? null,
            maxProperties: $data['maxProperties'] ?? null,
            enum: $data['enum'] ?? null,
            allOf: self::parseSchemaArray($data['allOf'] ?? null),
            anyOf: self::parseSchemaArray($data['anyOf'] ?? null),
            oneOf: self::parseSchemaArray($data['oneOf'] ?? null),
            not: isset($data['not']) ? self::fromArray($data['not']) : null,
            ref: $data['$ref'] ?? null,
        );
    }

    private static function validateSchema(array $data): void
    {
        // Validate type
        if (isset($data['type'])) {
            $validTypes = ['string', 'number', 'integer', 'boolean', 'array', 'object', 'null'];
            if (! in_array($data['type'], $validTypes)) {
                throw new ParseException("Invalid schema type: {$data['type']}");
            }
        }

        // Validate string constraints
        if (isset($data['minLength']) && $data['minLength'] < 0) {
            throw new ParseException('minLength must be >= 0');
        }

        if (isset($data['maxLength']) && $data['maxLength'] < 0) {
            throw new ParseException('maxLength must be >= 0');
        }

        // Validate array constraints
        if (isset($data['minItems']) && $data['minItems'] < 0) {
            throw new ParseException('minItems must be >= 0');
        }

        if (isset($data['maxItems']) && $data['maxItems'] < 0) {
            throw new ParseException('maxItems must be >= 0');
        }

        // Validate object constraints
        if (isset($data['minProperties']) && $data['minProperties'] < 0) {
            throw new ParseException('minProperties must be >= 0');
        }

        if (isset($data['maxProperties']) && $data['maxProperties'] < 0) {
            throw new ParseException('maxProperties must be >= 0');
        }
    }

    private static function parseProperties(?array $properties): ?array
    {
        if ($properties === null) {
            return null;
        }

        $parsed = [];
        foreach ($properties as $key => $property) {
            $parsed[$key] = self::fromArray($property);
        }

        return $parsed;
    }

    private static function parseAdditionalProperties(mixed $additionalProperties): bool|Schema|null
    {
        if ($additionalProperties === null) {
            return null;
        }

        if (is_bool($additionalProperties)) {
            return $additionalProperties;
        }

        if (is_array($additionalProperties)) {
            return self::fromArray($additionalProperties);
        }

        return null;
    }

    private static function parseSchemaArray(?array $schemas): ?array
    {
        if ($schemas === null) {
            return null;
        }

        return array_map(fn ($schema) => self::fromArray($schema), $schemas);
    }
}
