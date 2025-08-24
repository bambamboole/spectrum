<?php declare(strict_types=1);

namespace App\Objects;

use App\Validation\Validator;

/**
 * A single encoding definition applied to a single schema property.
 * Properties with an encoding object can be used to specify how parameters
 * should be serialized when the media type is `application/x-www-form-urlencoded`
 * or `multipart/form-data`.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#encoding-object
 */
readonly class Encoding extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'contentType' => ['sometimes', 'string', 'filled'],
            'headers' => ['sometimes', 'array'],
            'style' => ['sometimes', 'string', 'in:form,spaceDelimited,pipeDelimited,deepObject'],
            'explode' => ['sometimes', 'boolean'],
            'allowReserved' => ['sometimes', 'boolean'],
        ];
    }

    public function __construct(
        public ?string $contentType = null,
        /** @var array<string, Header>|null */
        public ?array $headers = null,
        public ?string $style = null,
        public ?bool $explode = null,
        public ?bool $allowReserved = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            contentType: $data['contentType'] ?? null,
            headers: isset($data['headers']) ? Header::multiple($data['headers'], $keyPrefix.'.headers') : null,
            style: $data['style'] ?? null,
            explode: $data['explode'] ?? null,
            allowReserved: $data['allowReserved'] ?? null,
            x: self::extractX($data),
        );
    }

    /**
     * Get the default explode value based on style.
     * When style is "form", explode defaults to true. For all other styles, it defaults to false.
     */
    public function getEffectiveExplode(): bool
    {
        if ($this->explode !== null) {
            return $this->explode;
        }

        return $this->style === 'form';
    }

    /**
     * Check if reserved characters are allowed.
     */
    public function allowsReservedCharacters(): bool
    {
        return $this->allowReserved === true;
    }

    /**
     * Get a specific header by name.
     */
    public function getHeader(string $name): ?Header
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Check if a header exists.
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }
}
