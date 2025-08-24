<?php declare(strict_types=1);

namespace App\Objects;

use App\Validation\Validator;

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
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new Server(
            url: $data['url'],
            description: $data['description'] ?? null,
            variables: $data['variables'] ?? null,
            x: self::extractX($data),
        );
    }
}
