<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\ReferenceResolver;
use Bambamboole\OpenApi\Validation\Validator;

/**
 * Describes a single API operation on a path.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#operation-object
 */
readonly class Operation extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string', 'filled'],
            'summary' => ['sometimes', 'string', 'filled'],
            'description' => ['sometimes', 'string', 'filled'],
            'operationId' => ['sometimes', 'string', 'filled'],
            'responses' => ['required', 'array', 'min:1'],
            'deprecated' => ['sometimes', 'boolean'],
            'parameters' => ['sometimes', 'array'],
            'requestBody' => ['sometimes', 'array'],
            'callbacks' => ['sometimes', 'array'],
            'security' => ['sometimes', 'array'],
            'servers' => ['sometimes', 'array'],
            'externalDocs' => ['sometimes', 'array'],
        ];
    }

    public function __construct(
        /** @var array<string, Response> */
        public array $responses,
        /** @var string[] */
        public array $tags = [],
        public ?string $summary = null,
        public ?string $description = null,
        public ?ExternalDocs $externalDocs = null,
        public ?string $operationId = null,
        /** @var Parameter[]|null */
        public ?array $parameters = null,
        public ?RequestBody $requestBody = null,
        /** @var array<string, Callback>|null */
        public ?array $callbacks = null,
        public bool $deprecated = false,
        /** @var Security[]|null */
        public ?array $security = null,
        /** @var Server[]|null */
        public ?array $servers = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        $data = ReferenceResolver::resolveRef($data);
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            responses: Response::multiple($data['responses'], $keyPrefix.'.responses'),
            tags: $data['tags'] ?? [],
            summary: $data['summary'] ?? null,
            description: $data['description'] ?? null,
            externalDocs: isset($data['externalDocs']) ? ExternalDocs::fromArray($data['externalDocs'], $keyPrefix.'.externalDocs') : null,
            operationId: $data['operationId'] ?? null,
            parameters: isset($data['parameters']) ? Parameter::multiple($data['parameters'], $keyPrefix.'.parameters') : null,
            requestBody: isset($data['requestBody']) ? RequestBody::fromArray($data['requestBody'], $keyPrefix.'.requestBody') : null,
            callbacks: isset($data['callbacks']) ? Callback::multiple($data['callbacks'], $keyPrefix.'.callbacks') : null,
            deprecated: $data['deprecated'] ?? false,
            security: isset($data['security']) ? Security::multiple($data['security'], $keyPrefix.'.security') : null,
            servers: isset($data['servers']) ? Server::multiple($data['servers'], $keyPrefix.'.servers') : null,
            x: self::extractX($data),
        );
    }
}
