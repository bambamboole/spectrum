<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\ReferenceResolver;
use Bambamboole\OpenApi\Validation\Validator;

/**
 * The Link object represents a possible design-time link for a response.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#link-object
 */
readonly class Link extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'operationRef' => ['sometimes', 'string', 'filled'],
            'operationId' => ['sometimes', 'string', 'filled'],
            'parameters' => ['sometimes', 'array'],
            'requestBody' => ['sometimes'],
            'description' => ['sometimes', 'string', 'filled'],
            'server' => ['sometimes', 'array'],
        ];
    }

    public function __construct(
        public ?string $operationRef = null,
        public ?string $operationId = null,
        public ?array $parameters = null,
        public mixed $requestBody = null,
        public ?string $description = null,
        public ?Server $server = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        $data = ReferenceResolver::resolveRef($data);
        Validator::validate($data, self::rules(), $keyPrefix);

        $server = null;
        if (isset($data['server'])) {
            $server = Server::fromArray($data['server'], $keyPrefix.'.server');
        }

        return new self(
            operationRef: $data['operationRef'] ?? null,
            operationId: $data['operationId'] ?? null,
            parameters: $data['parameters'] ?? null,
            requestBody: $data['requestBody'] ?? null,
            description: $data['description'] ?? null,
            server: $server,
            x: self::extractX($data),
        );
    }
}
