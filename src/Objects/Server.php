<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class Server extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'url' => ['required', 'string', 'filled'],
            'description' => ['sometimes', 'string'],
            'variables' => ['sometimes', 'array'],
        ];
    }

    public function __construct(
        public string $url,
        public ?string $description = null,
        public ?array $variables = null,
    ) {}
}
