<?php declare(strict_types=1);

namespace App\Objects;

use App\Validation\Validator;

/**
 * Describes a single request body.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#request-body-object
 */
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
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            content: MediaType::multiple($data['content'], $keyPrefix.'.content'),
            description: $data['description'] ?? null,
            required: $data['required'] ?? false,
            x: self::extractX($data),
        );
    }
}
