<?php declare(strict_types=1);

namespace App\Objects;

use App\Validation\Validator;

/**
 * A metadata object that allows for more fine-tuned XML model definitions.
 * When using arrays, XML element names are not inferred (for singular/plural forms)
 * and the XML element name for the child elements of the array is the same as the name
 * of the element that contains the array. This can be overridden using the name property.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#xml-object
 */
readonly class XML extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'filled'],
            'namespace' => ['sometimes', 'string', 'filled'],
            'prefix' => ['sometimes', 'string', 'filled'],
            'attribute' => ['sometimes', 'boolean'],
            'wrapped' => ['sometimes', 'boolean'],
        ];
    }

    public function __construct(
        public ?string $name = null,
        public ?string $namespace = null,
        public ?string $prefix = null,
        public ?bool $attribute = null,
        public ?bool $wrapped = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            name: $data['name'] ?? null,
            namespace: $data['namespace'] ?? null,
            prefix: $data['prefix'] ?? null,
            attribute: $data['attribute'] ?? null,
            wrapped: $data['wrapped'] ?? null,
            x: self::extractX($data),
        );
    }

    /**
     * Check if this XML definition represents an attribute.
     */
    public function isAttribute(): bool
    {
        return $this->attribute === true;
    }

    /**
     * Check if this XML definition represents a wrapped array.
     */
    public function isWrapped(): bool
    {
        return $this->wrapped === true;
    }

    /**
     * Get the qualified name including prefix if available.
     */
    public function getQualifiedName(): ?string
    {
        if ($this->name === null) {
            return null;
        }

        if ($this->prefix !== null) {
            return $this->prefix.':'.$this->name;
        }

        return $this->name;
    }
}
