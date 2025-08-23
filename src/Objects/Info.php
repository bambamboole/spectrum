<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Validation\Validator;

/**
 * The Info Object provides metadata about the API.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#info-object
 */
readonly class Info extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'title' => ['required', 'string', 'filled'],
            'version' => ['required', 'string', 'filled'],
            'description' => ['sometimes', 'string', 'filled'],
            'termsOfService' => ['sometimes', 'url'],
        ];
    }

    public function __construct(
        public string $title,
        public string $version,
        public ?string $description = null,
        public ?string $termsOfService = null,
        public ?Contact $contact = null,
        public ?License $license = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);
        $contact = isset($data['contact']) ? Contact::fromArray($data['contact'], $keyPrefix.'.contact') : null;
        $license = isset($data['license']) ? License::fromArray($data['license'], $keyPrefix.'.license') : null;

        return new Info(
            title: $data['title'],
            version: $data['version'],
            description: $data['description'] ?? null,
            termsOfService: $data['termsOfService'] ?? null,
            contact: $contact,
            license: $license,
            x: self::extractX($data),
        );
    }
}
