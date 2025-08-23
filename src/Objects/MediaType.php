<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\ReferenceResolver;
use Bambamboole\OpenApi\Validation\Validator;

/**
 * Each Media Type Object provides schema and examples for the media type identified by its key.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#media-type-object
 */
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
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        $data = ReferenceResolver::resolveRef($data);
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            schema: isset($data['schema']) ? Schema::fromArray($data['schema'], $keyPrefix.'.schema') : null,
            example: $data['example'] ?? null,
            examples: $data['examples'] ?? null,
            encoding: $data['encoding'] ?? null,
            x: self::extractX($data),
        );
    }
}
