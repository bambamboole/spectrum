<?php declare(strict_types=1);

namespace App\Objects;

use App\Validation\Validator;

/**
 * Holds a set of reusable objects for different aspects of the OAS.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#components-object
 */
readonly class Components extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'schemas' => ['sometimes', 'array'],
            'responses' => ['sometimes', 'array'],
            'parameters' => ['sometimes', 'array'],
            'examples' => ['sometimes', 'array'],
            'requestBodies' => ['sometimes', 'array'],
            'headers' => ['sometimes', 'array'],
            'securitySchemes' => ['sometimes', 'array'],
            'links' => ['sometimes', 'array'],
            'callbacks' => ['sometimes', 'array'],
            'pathItems' => ['sometimes', 'array'],
        ];
    }

    public function __construct(
        /** @var Schema[] */
        public array $schemas = [],
        /** @var Response[] */
        public array $responses = [],
        /** @var Parameter[] */
        public array $parameters = [],
        /** @var Example[] */
        public array $examples = [],
        /** @var RequestBody[] */
        public array $requestBodies = [],
        /** @var Header[] */
        public array $headers = [],
        /** @var SecurityScheme[] */
        public array $securitySchemes = [],
        /** @var Link[] */
        public array $links = [],
        /** @var Callback[] */
        public array $callbacks = [],
        /** @var PathItem[] */
        public array $pathItems = [],
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            schemas: Schema::multiple($data['schemas'] ?? [], 'components.schemas'),
            responses: Response::multiple($data['responses'] ?? [], 'components.responses'),
            parameters: Parameter::multiple($data['parameters'] ?? [], 'components.parameters'),
            examples: Example::multiple($data['examples'] ?? [], 'components.examples'),
            requestBodies: RequestBody::multiple($data['requestBodies'] ?? [], 'components.requestBodies'),
            headers: Header::multiple($data['headers'] ?? [], 'components.headers'),
            securitySchemes: SecurityScheme::multiple($data['securitySchemes'] ?? [], 'components.securitySchemes'),
            links: Link::multiple($data['links'] ?? [], 'components.links'),
            callbacks: Callback::multiple($data['callbacks'] ?? [], 'components.callbacks'),
            pathItems: PathItem::multiple($data['pathItems'] ?? [], 'components.pathItems'),
            x: self::extractX($data),
        );
    }
}
