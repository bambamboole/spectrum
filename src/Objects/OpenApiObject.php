<?php declare(strict_types=1);
namespace Bambamboole\OpenApi\Objects;

use Illuminate\Contracts\Validation\ValidationRule;

abstract readonly class OpenApiObject
{
    /** @return array<string, string[]|ValidationRule[]> */
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
