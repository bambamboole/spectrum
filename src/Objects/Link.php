<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

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
    ) {}
}
