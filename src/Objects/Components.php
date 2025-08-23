<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

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
    ) {}
}
