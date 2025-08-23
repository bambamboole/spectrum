<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class Example extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'summary' => ['sometimes', 'string', 'filled'],
            'description' => ['sometimes', 'string', 'filled'],
            'value' => ['sometimes'],
            'externalValue' => ['sometimes', 'string', 'url'],
        ];
    }

    public function __construct(
        public ?string $summary = null,
        public ?string $description = null,
        public mixed $value = null,
        public ?string $externalValue = null,
    ) {}
}
