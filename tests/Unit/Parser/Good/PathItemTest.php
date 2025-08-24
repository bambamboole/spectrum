<?php declare(strict_types=1);

use App\Objects\PathItem;
use App\OpenApiParser;

it('can parse minimal path item with single operation', function () {
    $pathItem = PathItem::fromArray([
        'get' => [
            'responses' => [
                '200' => [
                    'description' => 'Successful response',
                ],
            ],
        ],
    ]);

    expect($pathItem)->toBeInstanceOf(PathItem::class);
    expect($pathItem->get)->not->toBeNull();
    expect($pathItem->get->responses)->toHaveCount(1);
    expect($pathItem->get->responses['200']->description)->toBe('Successful response');
    expect($pathItem->post)->toBeNull();
    expect($pathItem->put)->toBeNull();
    expect($pathItem->delete)->toBeNull();
    expect($pathItem->summary)->toBeNull();
    expect($pathItem->description)->toBeNull();
});

it('can parse path item with summary and description', function () {
    $pathItem = PathItem::fromArray([
        'summary' => 'User operations',
        'description' => 'Operations available for user management',
        'get' => [
            'responses' => [
                '200' => ['description' => 'Success'],
            ],
        ],
    ]);

    expect($pathItem->summary)->toBe('User operations');
    expect($pathItem->description)->toBe('Operations available for user management');
    expect($pathItem->get)->not->toBeNull();
});

it('can parse path item with multiple HTTP methods', function () {
    $pathItem = PathItem::fromArray([
        'get' => [
            'summary' => 'Get user',
            'responses' => [
                '200' => ['description' => 'User retrieved'],
            ],
        ],
        'post' => [
            'summary' => 'Create user',
            'responses' => [
                '201' => ['description' => 'User created'],
            ],
        ],
        'put' => [
            'summary' => 'Update user',
            'responses' => [
                '200' => ['description' => 'User updated'],
            ],
        ],
        'delete' => [
            'summary' => 'Delete user',
            'responses' => [
                '204' => ['description' => 'User deleted'],
            ],
        ],
    ]);

    expect($pathItem->get)->not->toBeNull();
    expect($pathItem->get->summary)->toBe('Get user');
    expect($pathItem->post)->not->toBeNull();
    expect($pathItem->post->summary)->toBe('Create user');
    expect($pathItem->put)->not->toBeNull();
    expect($pathItem->put->summary)->toBe('Update user');
    expect($pathItem->delete)->not->toBeNull();
    expect($pathItem->delete->summary)->toBe('Delete user');
});

it('can parse path item with all HTTP methods', function () {
    $pathItem = PathItem::fromArray([
        'get' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
        'put' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
        'post' => [
            'responses' => ['201' => ['description' => 'Created']],
        ],
        'delete' => [
            'responses' => ['204' => ['description' => 'No Content']],
        ],
        'options' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
        'head' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
        'patch' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
        'trace' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
    ]);

    expect($pathItem->get)->not->toBeNull();
    expect($pathItem->put)->not->toBeNull();
    expect($pathItem->post)->not->toBeNull();
    expect($pathItem->delete)->not->toBeNull();
    expect($pathItem->options)->not->toBeNull();
    expect($pathItem->head)->not->toBeNull();
    expect($pathItem->patch)->not->toBeNull();
    expect($pathItem->trace)->not->toBeNull();

    $operations = $pathItem->getOperations();
    expect($operations)->toHaveCount(8);
    expect($operations)->toHaveKey('get');
    expect($operations)->toHaveKey('put');
    expect($operations)->toHaveKey('post');
    expect($operations)->toHaveKey('delete');
    expect($operations)->toHaveKey('options');
    expect($operations)->toHaveKey('head');
    expect($operations)->toHaveKey('patch');
    expect($operations)->toHaveKey('trace');
});

it('can parse path item with servers', function () {
    $pathItem = PathItem::fromArray([
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
        'get' => [
            'responses' => [
                '200' => ['description' => 'Success'],
            ],
        ],
    ]);

    expect($pathItem->servers)->toHaveCount(2);
    expect($pathItem->servers[0]->url)->toBe('https://api.example.com/v1');
    expect($pathItem->servers[0]->description)->toBe('Production server');
    expect($pathItem->servers[1]->url)->toBe('https://staging-api.example.com/v1');
    expect($pathItem->servers[1]->description)->toBe('Staging server');
});

it('can parse path item with common parameters', function () {
    $pathItem = PathItem::fromArray([
        'parameters' => [
            [
                'name' => 'userId',
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'integer'],
                'description' => 'The user ID',
            ],
            [
                'name' => 'version',
                'in' => 'header',
                'schema' => ['type' => 'string'],
                'description' => 'API version',
            ],
        ],
        'get' => [
            'responses' => [
                '200' => ['description' => 'User retrieved'],
            ],
        ],
        'put' => [
            'responses' => [
                '200' => ['description' => 'User updated'],
            ],
        ],
    ]);

    expect($pathItem->parameters)->toHaveCount(2);
    expect($pathItem->parameters[0]->name)->toBe('userId');
    expect($pathItem->parameters[0]->in)->toBe('path');
    expect($pathItem->parameters[0]->required)->toBeTrue();
    expect($pathItem->parameters[0]->description)->toBe('The user ID');
    expect($pathItem->parameters[1]->name)->toBe('version');
    expect($pathItem->parameters[1]->in)->toBe('header');
    expect($pathItem->parameters[1]->description)->toBe('API version');
});

it('can parse path item with all properties', function () {
    $pathItem = PathItem::fromArray([
        'summary' => 'User management endpoint',
        'description' => 'Comprehensive user management operations',
        'parameters' => [
            [
                'name' => 'userId',
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'integer'],
            ],
        ],
        'servers' => [
            [
                'url' => 'https://api.example.com',
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
                            'schema' => ['type' => 'object'],
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
                        'schema' => ['type' => 'object'],
                    ],
                ],
            ],
            'responses' => [
                '200' => ['description' => 'User updated'],
                '400' => ['description' => 'Invalid input'],
                '404' => ['description' => 'User not found'],
            ],
        ],
        'delete' => [
            'summary' => 'Delete user',
            'operationId' => 'deleteUser',
            'responses' => [
                '204' => ['description' => 'User deleted'],
                '404' => ['description' => 'User not found'],
            ],
        ],
    ]);

    expect($pathItem->summary)->toBe('User management endpoint');
    expect($pathItem->description)->toBe('Comprehensive user management operations');
    expect($pathItem->parameters)->toHaveCount(1);
    expect($pathItem->servers)->toHaveCount(1);
    expect($pathItem->get)->not->toBeNull();
    expect($pathItem->put)->not->toBeNull();
    expect($pathItem->delete)->not->toBeNull();
    expect($pathItem->post)->toBeNull();

    $operations = $pathItem->getOperations();
    expect($operations)->toHaveCount(3);
    expect($operations['get']->operationId)->toBe('getUserById');
    expect($operations['put']->operationId)->toBe('updateUser');
    expect($operations['delete']->operationId)->toBe('deleteUser');
});

it('can parse multiple path items', function () {
    $pathItems = PathItem::multiple([
        '/users' => [
            'get' => [
                'summary' => 'List users',
                'responses' => [
                    '200' => ['description' => 'Users list'],
                ],
            ],
            'post' => [
                'summary' => 'Create user',
                'responses' => [
                    '201' => ['description' => 'User created'],
                ],
            ],
        ],
        '/users/{id}' => [
            'parameters' => [
                [
                    'name' => 'id',
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'integer'],
                ],
            ],
            'get' => [
                'summary' => 'Get user',
                'responses' => [
                    '200' => ['description' => 'User found'],
                ],
            ],
            'delete' => [
                'summary' => 'Delete user',
                'responses' => [
                    '204' => ['description' => 'User deleted'],
                ],
            ],
        ],
    ]);

    expect($pathItems)->toHaveCount(2);
    expect($pathItems)->toHaveKey('/users');
    expect($pathItems)->toHaveKey('/users/{id}');

    expect($pathItems['/users'])->toBeInstanceOf(PathItem::class);
    expect($pathItems['/users']->get)->not->toBeNull();
    expect($pathItems['/users']->post)->not->toBeNull();
    expect($pathItems['/users']->get->summary)->toBe('List users');

    expect($pathItems['/users/{id}'])->toBeInstanceOf(PathItem::class);
    expect($pathItems['/users/{id}']->parameters)->toHaveCount(1);
    expect($pathItems['/users/{id}']->parameters[0]->name)->toBe('id');
    expect($pathItems['/users/{id}']->get)->not->toBeNull();
    expect($pathItems['/users/{id}']->delete)->not->toBeNull();
});

it('can parse path item with reference resolution', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [
            '/users/{userId}' => [
                'parameters' => [
                    ['$ref' => '#/components/parameters/UserIdParam'],
                ],
                'get' => [
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
            ],
        ],
        'components' => [
            'parameters' => [
                'UserIdParam' => [
                    'name' => 'userId',
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'integer'],
                ],
            ],
        ],
    ]);

    $pathItem = $document->paths['/users/{userId}'];

    expect($pathItem->parameters)->toHaveCount(1);
    expect($pathItem->parameters[0]->name)->toBe('userId');
    expect($pathItem->parameters[0]->in)->toBe('path');
    expect($pathItem->parameters[0]->required)->toBeTrue();
});

it('getOperations returns only defined operations', function () {
    $pathItem = PathItem::fromArray([
        'get' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
        'post' => [
            'responses' => ['201' => ['description' => 'Created']],
        ],
        // put, delete, etc. not defined
    ]);

    $operations = $pathItem->getOperations();
    expect($operations)->toHaveCount(2);
    expect($operations)->toHaveKey('get');
    expect($operations)->toHaveKey('post');
    expect($operations)->not->toHaveKey('put');
    expect($operations)->not->toHaveKey('delete');
    expect($operations)->not->toHaveKey('patch');
});
