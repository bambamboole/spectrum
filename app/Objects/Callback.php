<?php declare(strict_types=1);

namespace App\Objects;

use App\Validation\Validator;

/**
 * A map of possible out-of band callbacks related to the parent operation.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#callback-object
 */
readonly class Callback extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            // Callback is a map of expressions to Path Item Objects
            // Each key should be a valid expression, each value should be a valid Path Item
            '*' => ['sometimes', 'array'], // Dynamic keys with Path Item Object values
        ];
    }

    public function __construct(
        /** @var array<string, array> Map of callback expressions to Path Item data */
        public array $expressions = [],
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            expressions: $data,
            x: self::extractX($data),
        );
    }
}
