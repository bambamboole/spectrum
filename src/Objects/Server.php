<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

/**
 * An object representing a Server.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#server-object
 */
readonly class Server extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'url' => ['required', 'string', 'filled'],
            'description' => ['sometimes', 'string'],
            'variables' => ['sometimes', 'array'],
        ];
    }

    public function __construct(
        public string $url,
        public ?string $description = null,
        public ?array $variables = null,
    ) {}
}
