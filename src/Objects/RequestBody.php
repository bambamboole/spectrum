<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class RequestBody extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'description' => ['sometimes', 'string', 'min:1'],
            'content' => ['required', 'array', 'min:1'],
            'required' => ['sometimes', 'boolean'],
        ];
    }

    public function __construct(
        public array $content,
        public ?string $description = null,
        public bool $required = false,
    ) {}
}
