<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Validation\Validator;

/**
 * Allows referencing an external resource for extended documentation.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#external-documentation-object
 */
readonly class ExternalDocs extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'url' => ['required', 'url'],
            'description' => ['sometimes', 'string', 'filled'],
        ];
    }

    public function __construct(
        public string $url,
        public ?string $description = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            url: $data['url'],
            description: $data['description'] ?? null,
            x: self::extractX($data),
        );
    }
}
