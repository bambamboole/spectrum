<?php declare(strict_types=1);

namespace App\Objects;

use App\Validation\Validator;

/**
 * When request bodies or response payloads may be one of a number of different schemas,
 * a discriminator object can be used to aid in serialization, deserialization, and validation.
 * The discriminator is a specific object in a schema which is used to inform the consumer
 * of the document of an alternative schema based on the value associated with it.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#discriminator-object
 */
readonly class Discriminator extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'propertyName' => ['required', 'string', 'filled'],
            'mapping' => ['sometimes', 'array'],
        ];
    }

    public function __construct(
        public string $propertyName,
        /** @var array<string, string>|null Mapping between payload values and schema names/references */
        public ?array $mapping = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            propertyName: $data['propertyName'],
            mapping: $data['mapping'] ?? null,
            x: self::extractX($data),
        );
    }

    /**
     * Get the schema reference for a given discriminator value.
     */
    public function getSchemaForValue(string $value): ?string
    {
        return $this->mapping[$value] ?? null;
    }

    /**
     * Check if a mapping exists for a discriminator value.
     */
    public function hasMappingForValue(string $value): bool
    {
        return isset($this->mapping[$value]);
    }

    /**
     * Get all discriminator values that have mappings.
     *
     * @return string[]
     */
    public function getMappedValues(): array
    {
        return array_keys($this->mapping ?? []);
    }
}
