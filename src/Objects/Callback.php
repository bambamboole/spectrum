<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

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
    ) {}
}
