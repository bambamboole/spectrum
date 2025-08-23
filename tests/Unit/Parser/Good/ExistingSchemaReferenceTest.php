<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Objects\Schema;
use Bambamboole\OpenApi\OpenApiParser;

it('resolves references in existing SchemaWithComponents test case', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.3',
        'info' => [
            'title' => 'API with Components',
            'version' => '1.0.0',
        ],
        'paths' => [],
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
    ]);

    expect($document)->toBeInstanceOf(OpenApiDocument::class);

    // Check that the ApiResponse schema has proper oneOf resolution
    $apiResponseSchema = $document->components->schemas['ApiResponse'];
    expect($apiResponseSchema)->toBeInstanceOf(Schema::class);
    expect($apiResponseSchema->properties['data'])->toBeInstanceOf(Schema::class);

    $dataProperty = $apiResponseSchema->properties['data'];
    expect($dataProperty->oneOf)->toBeArray();
    expect($dataProperty->oneOf)->toHaveCount(2);

    // First oneOf option should be resolved User schema
    $firstOption = $dataProperty->oneOf[0];
    expect($firstOption)->toBeInstanceOf(Schema::class);
    expect($firstOption->type)->toBe('object');
    expect($firstOption->properties)->toHaveKey('id');
    expect($firstOption->properties)->toHaveKey('name');
    expect($firstOption->properties)->toHaveKey('email');
    expect($firstOption->required)->toBe(['id', 'name', 'email']);

    // Second oneOf option should be array with resolved User items
    $secondOption = $dataProperty->oneOf[1];
    expect($secondOption)->toBeInstanceOf(Schema::class);
    expect($secondOption->type)->toBe('array');
    expect($secondOption->items)->toBeInstanceOf(Schema::class);
    expect($secondOption->items->type)->toBe('object');
    expect($secondOption->items->properties)->toHaveKey('id');
    expect($secondOption->items->properties)->toHaveKey('name');
    expect($secondOption->items->properties)->toHaveKey('email');
});
