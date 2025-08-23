<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

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
    ) {}
}
