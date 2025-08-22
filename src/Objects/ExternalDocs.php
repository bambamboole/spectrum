<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

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
    ) {}
}
