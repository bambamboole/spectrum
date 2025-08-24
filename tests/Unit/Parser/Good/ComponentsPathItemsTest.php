<?php declare(strict_types=1);

use App\Objects\OpenApiDocument;
use App\OpenApiParser;

it('can parse components with pathItems', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Components PathItems',
            'version' => '1.0.0',
        ],
        'paths' => [
            '/users/{id}' => [
                '$ref' => '#/components/pathItems/UserPath',
            ],
            '/products/{id}' => [
                '$ref' => '#/components/pathItems/ProductPath',
            ],
        ],
        'components' => [
            'pathItems' => [
                'UserPath' => [
                    'summary' => 'User resource path',
                    'description' => 'Operations for a specific user',
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => [
                                'type' => 'integer',
                                'format' => 'int64',
                            ],
                            'description' => 'User ID',
                        ],
                    ],
                    'get' => [
                        'summary' => 'Get user by ID',
                        'operationId' => 'getUserById',
                        'responses' => [
                            '200' => [
                                'description' => 'User found',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/User',
                                        ],
                                    ],
                                ],
                            ],
                            '404' => [
                                'description' => 'User not found',
                            ],
                        ],
                    ],
                    'put' => [
                        'summary' => 'Update user',
                        'operationId' => 'updateUser',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/User',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'User updated',
                            ],
                        ],
                    ],
                ],
                'ProductPath' => [
                    'summary' => 'Product resource path',
                    'get' => [
                        'summary' => 'Get product by ID',
                        'responses' => [
                            '200' => [
                                'description' => 'Product found',
                            ],
                        ],
                    ],
                ],
            ],
            'schemas' => [
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                            'format' => 'int64',
                        ],
                        'name' => [
                            'type' => 'string',
                        ],
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                        ],
                    ],
                    'required' => ['id', 'name', 'email'],
                ],
            ],
        ],
    ]));

    expect($document)->toBeInstanceOf(OpenApiDocument::class);
    expect($document->components->pathItems)->toHaveCount(2);
    expect($document->components->pathItems)->toHaveKey('UserPath');
    expect($document->components->pathItems)->toHaveKey('ProductPath');

    // Test UserPath
    $userPath = $document->components->pathItems['UserPath'];
    expect($userPath->summary)->toBe('User resource path');
    expect($userPath->description)->toBe('Operations for a specific user');
    expect($userPath->parameters)->toHaveCount(1);
    expect($userPath->parameters[0]->name)->toBe('id');
    expect($userPath->parameters[0]->in)->toBe('path');
    expect($userPath->parameters[0]->required)->toBeTrue();

    expect($userPath->get)->not->toBeNull();
    expect($userPath->get->summary)->toBe('Get user by ID');
    expect($userPath->get->operationId)->toBe('getUserById');
    expect($userPath->get->responses)->toHaveKey('200');
    expect($userPath->get->responses)->toHaveKey('404');

    expect($userPath->put)->not->toBeNull();
    expect($userPath->put->summary)->toBe('Update user');
    expect($userPath->put->operationId)->toBe('updateUser');
    expect($userPath->put->requestBody)->not->toBeNull();
    expect($userPath->put->requestBody->required)->toBeTrue();

    // Test operations collection
    $userOperations = $userPath->getOperations();
    expect($userOperations)->toHaveCount(2);
    expect($userOperations)->toHaveKey('get');
    expect($userOperations)->toHaveKey('put');

    // Test ProductPath
    $productPath = $document->components->pathItems['ProductPath'];
    expect($productPath->summary)->toBe('Product resource path');
    expect($productPath->get)->not->toBeNull();
    expect($productPath->get->summary)->toBe('Get product by ID');

    $productOperations = $productPath->getOperations();
    expect($productOperations)->toHaveCount(1);
    expect($productOperations)->toHaveKey('get');
});

it('can parse components with empty pathItems', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Empty PathItems',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'pathItems' => [],
        ],
    ]));

    expect($document->components->pathItems)->toBeArray();
    expect($document->components->pathItems)->toHaveCount(0);
});

it('can parse components without pathItems', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API without PathItems',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                    ],
                ],
            ],
        ],
    ]));

    expect($document->components->pathItems)->toBeArray();
    expect($document->components->pathItems)->toHaveCount(0);
});

it('can parse pathItems with extension properties', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Extended PathItems',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'pathItems' => [
                'ExtendedPath' => [
                    'get' => [
                        'responses' => [
                            '200' => [
                                'description' => 'Success',
                            ],
                        ],
                    ],
                    'x-custom-path' => 'custom-value',
                ],
            ],
        ],
    ]));

    $extendedPath = $document->components->pathItems['ExtendedPath'];
    expect($extendedPath->x)->toHaveKey('x-custom-path');
    expect($extendedPath->x['x-custom-path'])->toBe('custom-value');
});
