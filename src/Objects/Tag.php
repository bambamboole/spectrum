<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\ReferenceResolver;
use Bambamboole\OpenApi\Validation\Validator;

/**
 * Adds metadata to a single tag that is used by the Operation Object.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#tag-object
 */
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
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        $data = ReferenceResolver::resolveRef($data);
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            externalDocs: isset($data['externalDocs']) ? ExternalDocs::fromArray($data['externalDocs'], $keyPrefix) : null,
            x: self::extractX($data),
        );
    }
}
