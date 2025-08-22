<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

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
    ) {}
}
