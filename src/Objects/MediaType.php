<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class MediaType extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'schema' => ['sometimes', 'array'],
            'example' => ['sometimes'],
            'examples' => ['sometimes', 'array'],
            'encoding' => ['sometimes', 'array'],
        ];
    }

    public function __construct(
        public ?Schema $schema = null,
        public mixed $example = null,
        public ?array $examples = null,
        public ?array $encoding = null,
    ) {}
}
