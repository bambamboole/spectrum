<?php declare(strict_types=1);

namespace App\Objects;

use App\Validation\Validator;

/**
 * Describes a single response from an API Operation.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#response-object
 */
readonly class Response extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'description' => ['required', 'string', 'min:1'],
            'headers' => ['sometimes', 'array'],
            'content' => ['sometimes', 'array'],
            'links' => ['sometimes', 'array'],
        ];
    }

    public function __construct(
        public string $description,
        /** @var Header[]|null */
        public ?array $headers = null,
        /** @var MediaType[]|null */
        public ?array $content = null,
        /** @var Link[]|null */
        public ?array $links = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            description: $data['description'],
            headers: isset($data['headers']) ? Header::multiple($data['headers'], $keyPrefix.'.headers') : null,
            content: isset($data['content']) ? MediaType::multiple($data['content'], $keyPrefix.'.content') : null,
            links: isset($data['links']) ? Link::multiple($data['links'], $keyPrefix.'.links') : null,
            x: self::extractX($data),
        );
    }
}
