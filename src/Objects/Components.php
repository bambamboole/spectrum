<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

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
        public array $responses = [],
        /** @var Parameter[] */
        public array $parameters = [],
        public array $examples = [],
        public array $requestBodies = [],
        /** @var Header[] */
        public array $headers = [],
        /** @var SecurityScheme[] */
        public array $securitySchemes = [],
        public array $links = [],
        public array $callbacks = [],
    ) {}
}
