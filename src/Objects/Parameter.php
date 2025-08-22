<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class Parameter
{
    public function __construct(
        public string $name,
        public string $in,
        public ?string $description = null,
        public bool $required = false,
        public bool $deprecated = false,
        public bool $allowEmptyValue = false,
        public ?string $style = null,
        public ?bool $explode = null,
        public bool $allowReserved = false,
        public ?Schema $schema = null,
        public mixed $example = null,
        public ?array $examples = null,
    ) {}

    public static function create(array $data): self
    {
        return new self(
            name: $data['name'],
            in: $data['in'],
            description: $data['description'] ?? null,
            required: $data['required'] ?? false,
            deprecated: $data['deprecated'] ?? false,
            allowEmptyValue: $data['allowEmptyValue'] ?? false,
            style: $data['style'] ?? null,
            explode: $data['explode'] ?? null,
            allowReserved: $data['allowReserved'] ?? false,
            schema: isset($data['schema']) ? Schema::create($data['schema']) : null,
            example: $data['example'] ?? null,
            examples: $data['examples'] ?? null,
        );
    }
}
