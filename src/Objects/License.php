<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Validation\Validator;

/**
 * License information for the exposed API.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#license-object
 */
readonly class License extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'url' => ['sometimes', 'url'],
        ];
    }

    public function __construct(
        public string $name,
        public ?string $url = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new License(
            name: $data['name'],
            url: $data['url'] ?? null,
            x: self::extractX($data),
        );
    }
}
