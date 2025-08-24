<?php declare(strict_types=1);

use App\Objects\OpenApiDocument;
use App\OpenApiParser;

it('can parse OpenAPI schema with components')
    ->expect(fn () => OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.0.3',
        'info' => [
            'title' => 'API with Components',
            'version' => '1.0.0',
        ],
        'servers' => [
            [
                'url' => 'https://api.example.com/v1',
                'description' => 'Production server',
            ],
            [
                'url' => 'https://staging-api.example.com/v1',
                'description' => 'Staging server',
            ],
        ],
        'components' => [
            'schemas' => [
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                            'format' => 'int64',
                            'minimum' => 1,
                        ],
                        'name' => [
                            'type' => 'string',
                            'minLength' => 1,
                            'maxLength' => 100,
                        ],
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                        ],
                        'roles' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                                'enum' => ['admin', 'user', 'guest'],
                            ],
                            'uniqueItems' => true,
                        ],
                    ],
                    'required' => ['id', 'name', 'email'],
                    'additionalProperties' => false,
                ],
                'ApiResponse' => [
                    'type' => 'object',
                    'properties' => [
                        'success' => [
                            'type' => 'boolean',
                        ],
                        'message' => [
                            'type' => 'string',
                        ],
                        'data' => [
                            'oneOf' => [
                                ['$ref' => '#/components/schemas/User'],
                                ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/User']],
                            ],
                        ],
                    ],
                    'required' => ['success'],
                ],
            ],
        ],
    ])))
    ->toBeInstanceOf(OpenApiDocument::class);
