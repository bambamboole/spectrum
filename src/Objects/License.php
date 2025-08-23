<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

/**
 * License information for the exposed API.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#license-object
 */
readonly class License extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'url' => ['sometimes', 'url'],
        ];
    }

    public function __construct(
        public string $name,
        public ?string $url = null,
    ) {}
}
