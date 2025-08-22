<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class Schema extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', 'in:string,number,integer,boolean,array,object,null'],
            'format' => ['sometimes', 'string', 'filled'],
            'title' => ['sometimes', 'string', 'filled'],
            'description' => ['sometimes', 'string', 'filled'],
            '$ref' => ['sometimes', 'string', 'filled'],

            // String constraints
            'minLength' => ['sometimes', 'integer', 'min:0'],
            'maxLength' => ['sometimes', 'integer', 'min:0', 'gte:minLength'],
            'pattern' => ['sometimes', 'string', 'filled'],

            // Numeric constraints
            'minimum' => ['sometimes', 'numeric'],
            'maximum' => ['sometimes', 'numeric', 'gte:minimum'],
            'exclusiveMinimum' => ['sometimes', 'boolean'],
            'exclusiveMaximum' => ['sometimes', 'boolean'],
            'multipleOf' => ['sometimes', 'numeric', 'gt:0'],

            // Array constraints
            'minItems' => ['sometimes', 'integer', 'min:0'],
            'maxItems' => ['sometimes', 'integer', 'min:0', 'gte:minItems'],
            'uniqueItems' => ['sometimes', 'boolean'],
            'items' => ['sometimes', 'array'],

            // Object constraints
            'minProperties' => ['sometimes', 'integer', 'min:0'],
            'maxProperties' => ['sometimes', 'integer', 'min:0', 'gte:minProperties'],
            'required' => ['sometimes', 'array'],
            'properties' => ['sometimes', 'array'],
            'additionalProperties' => ['sometimes'],

            // Enumeration
            'enum' => ['sometimes', 'array', 'min:1'],

            // Composition keywords
            'allOf' => ['sometimes', 'array', 'min:1'],
            'anyOf' => ['sometimes', 'array', 'min:1'],
            'oneOf' => ['sometimes', 'array', 'min:1'],
            'not' => ['sometimes', 'array'],
        ];
    }

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

    public static function create(array $data): self
    {
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
            items: null, // Will be set by ObjectFactory
            properties: null, // Will be set by ObjectFactory
            required: $data['required'] ?? null,
            additionalProperties: null, // Will be set by ObjectFactory
            minProperties: $data['minProperties'] ?? null,
            maxProperties: $data['maxProperties'] ?? null,
            enum: $data['enum'] ?? null,
            allOf: null, // Will be set by ObjectFactory
            anyOf: null, // Will be set by ObjectFactory
            oneOf: null, // Will be set by ObjectFactory
            not: null, // Will be set by ObjectFactory
            ref: $data['$ref'] ?? null,
        );
    }
}
