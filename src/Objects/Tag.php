<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class Tag extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'filled'],
            'description' => ['sometimes', 'string'],
        ];
    }

    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?ExternalDocs $externalDocs = null,
    ) {}
}
