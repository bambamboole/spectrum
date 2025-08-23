<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

/**
 * Describes a single operation parameter.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#parameter-object
 */
readonly class Parameter extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'filled'],
            'in' => ['required', 'string', 'in:query,header,path,cookie'],
            'description' => ['sometimes', 'string', 'filled'],
            'required' => ['sometimes', 'boolean'],
            'deprecated' => ['sometimes', 'boolean'],
            'allowEmptyValue' => ['sometimes', 'boolean'],
            'style' => ['sometimes', 'string', 'filled'],
            'explode' => ['sometimes', 'boolean'],
            'allowReserved' => ['sometimes', 'boolean'],
            'schema' => ['sometimes', 'array'],
            'example' => ['sometimes'],
            'examples' => ['sometimes', 'array'],
            'content' => ['sometimes', 'array'],
        ];
    }

    public function __construct(
        public string $name,
        public string $in,
        public ?string $description = null,
        public bool $required = false,
        public bool $deprecated = false,
        public ?bool $allowEmptyValue = null,
        public ?string $style = null,
        public ?bool $explode = null,
        public ?bool $allowReserved = null,
        public ?Schema $schema = null,
        public mixed $example = null,
        public ?array $examples = null,
        public ?array $content = null,
    ) {}
}
