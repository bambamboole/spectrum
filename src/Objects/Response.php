<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

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
        public ?array $headers = null,
        public ?array $content = null,
        public ?array $links = null,
    ) {}
}
