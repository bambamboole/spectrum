<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Validation\Validator;

/**
 * Contact information for the exposed API.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#contact-object
 */
readonly class Contact extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'filled'],
            'email' => ['sometimes', 'email'],
            'url' => ['sometimes', 'url'],
        ];
    }

    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $url = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new Contact(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            url: $data['url'] ?? null,
            x: self::extractX($data),
        );
    }
}
