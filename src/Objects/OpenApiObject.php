<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

abstract readonly class OpenApiObject
{
    /** @return array<string, string[]> */
    public static function rules(): array
    {
        return [];
    }

    /** @return array<string, string> */
    public static function messages(): array
    {
        return [];
    }
}
