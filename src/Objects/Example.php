<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Services\ReferenceResolver;
use Bambamboole\OpenApi\Validation\Validator;

/**
 * In all cases, the example value is expected to be compatible with the type schema of its associated value.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#example-object
 */
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
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        $data = ReferenceResolver::resolveRef($data);
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            summary: $data['summary'] ?? null,
            description: $data['description'] ?? null,
            value: $data['value'] ?? null,
            externalValue: $data['externalValue'] ?? null,
            x: self::extractX($data),
        );
    }
}
