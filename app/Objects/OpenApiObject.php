<?php declare(strict_types=1);
namespace App\Objects;

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

    public static function multiple(array $components, string $keyPrefix = ''): array
    {
        $parsed = [];
        foreach ($components as $key => $component) {
            $componentKeyPrefix = "{$keyPrefix}.{$key}";
            /** @phpstan-ignore-next-line */
            $parsed[$key] = static::fromArray($component, $componentKeyPrefix);
        }

        return $parsed;
    }

    protected static function extractX(array $data): array
    {
        return array_filter($data, fn ($key) => str_starts_with($key, 'x-'), ARRAY_FILTER_USE_KEY);
    }
}
