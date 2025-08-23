<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class Header extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'description' => ['sometimes', 'string', 'filled'],
            'required' => ['sometimes', 'boolean'],
            'deprecated' => ['sometimes', 'boolean'],
            'allowEmptyValue' => ['sometimes', 'boolean'],
            'style' => ['sometimes', 'string', 'filled'],
            'explode' => ['sometimes', 'boolean'],
            'schema' => ['sometimes', 'array'],
            'example' => ['sometimes'],
            'examples' => ['sometimes', 'array'],
            'content' => ['sometimes', 'array'],
        ];
    }

    public function __construct(
        public ?string $description = null,
        public bool $required = false,
        public bool $deprecated = false,
        public ?bool $allowEmptyValue = null,
        public ?string $style = null,
        public ?bool $explode = null,
        public ?Schema $schema = null,
        public mixed $example = null,
        public ?array $examples = null,
        public ?array $content = null,
    ) {}
}
