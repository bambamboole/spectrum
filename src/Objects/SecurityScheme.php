<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Services\ReferenceResolver;
use Bambamboole\OpenApi\Validation\Validator;

/**
 * Defines a security scheme that can be used by the operations.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#security-scheme-object
 */
readonly class SecurityScheme extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:apiKey,http,oauth2,openIdConnect'],
            'description' => ['sometimes', 'string'],

            // apiKey specific
            'name' => ['required_if:type,apiKey', 'string', 'filled'],
            'in' => ['required_if:type,apiKey', 'string', 'in:query,header,cookie'],

            // http specific
            'scheme' => ['required_if:type,http', 'string', 'filled'],
            'bearerFormat' => ['sometimes', 'string', 'filled'],

            // oauth2 specific
            'flows' => ['required_if:type,oauth2', 'array'],

            // openIdConnect specific
            'openIdConnectUrl' => ['required_if:type,openIdConnect', 'url'],
        ];
    }

    public function __construct(
        public string $type,
        public ?string $description = null,
        public ?string $name = null,
        public ?string $in = null,
        public ?string $scheme = null,
        public ?string $bearerFormat = null,
        public ?array $flows = null,
        public ?string $openIdConnectUrl = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        $data = ReferenceResolver::resolveRef($data);
        Validator::validate($data, self::rules(), $keyPrefix);

        return new SecurityScheme(
            type: $data['type'],
            description: $data['description'] ?? null,
            name: $data['name'] ?? null,
            in: $data['in'] ?? null,
            scheme: $data['scheme'] ?? null,
            bearerFormat: $data['bearerFormat'] ?? null,
            flows: $data['flows'] ?? null,
            openIdConnectUrl: $data['openIdConnectUrl'] ?? null,
            x: self::extractX($data),
        );
    }
}
