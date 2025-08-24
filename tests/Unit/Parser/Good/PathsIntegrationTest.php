<?php declare(strict_types=1);

use App\Objects\OpenApiDocument;
use App\Objects\Operation;
use App\Objects\PathItem;
use App\OpenApiParser;

it('can parse complete OpenAPI document with paths and operations', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.0.3',
        'info' => [
            'title' => 'User Management API',
            'version' => '1.0.0',
            'description' => 'A comprehensive API for user management',
        ],
        'servers' => [
            [
                'url' => 'https://api.example.com/v1',
                'description' => 'Production server',
            ],
        ],
        'paths' => [
            '/users' => [
                'summary' => 'User collection operations',
                'description' => 'Operations for managing the collection of users',
                'get' => [
                    'tags' => ['users'],
                    'summary' => 'List all users',
                    'description' => 'Retrieve a paginated list of all users',
                    'operationId' => 'listUsers',
                    'parameters' => [
                        [
                            'name' => 'page',
                            'in' => 'query',
                            'schema' => ['type' => 'integer', 'minimum' => 1],
                            'description' => 'Page number for pagination',
                        ],
                        [
                            'name' => 'limit',
                            'in' => 'query',
                            'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
                            'description' => 'Number of users per page',
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'List of users',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'users' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'id' => ['type' => 'integer'],
                                                        'name' => ['type' => 'string'],
                                                        'email' => ['type' => 'string', 'format' => 'email'],
                                                    ],
                                                ],
                                            ],
                                            'pagination' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'page' => ['type' => 'integer'],
                                                    'totalPages' => ['type' => 'integer'],
                                                    'totalUsers' => ['type' => 'integer'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '400' => [
                            'description' => 'Invalid query parameters',
                        ],
                    ],
                ],
                'post' => [
                    'tags' => ['users'],
                    'summary' => 'Create a new user',
                    'description' => 'Create a new user account',
                    'operationId' => 'createUser',
                    'requestBody' => [
                        'description' => 'User data',
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => ['type' => 'string'],
                                        'email' => ['type' => 'string', 'format' => 'email'],
                                        'password' => ['type' => 'string', 'minLength' => 8],
                                    ],
                                    'required' => ['name', 'email', 'password'],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'User created successfully',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'integer'],
                                            'name' => ['type' => 'string'],
                                            'email' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '400' => [
                            'description' => 'Invalid user data',
                        ],
                        '409' => [
                            'description' => 'User already exists',
                        ],
                    ],
                ],
            ],
            '/users/{userId}' => [
                'summary' => 'Individual user operations',
                'description' => 'Operations for managing individual users',
                'parameters' => [
                    [
                        'name' => 'userId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                        'description' => 'The unique identifier of the user',
                    ],
                ],
                'get' => [
                    'tags' => ['users'],
                    'summary' => 'Get user by ID',
                    'description' => 'Retrieve a specific user by their ID',
                    'operationId' => 'getUserById',
                    'responses' => [
                        '200' => [
                            'description' => 'User found',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => ['type' => 'integer'],
                                            'name' => ['type' => 'string'],
                                            'email' => ['type' => 'string'],
                                            'createdAt' => ['type' => 'string', 'format' => 'date-time'],
                                        ],
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
                    'tags' => ['users'],
                    'summary' => 'Update user',
                    'description' => 'Update an existing user',
                    'operationId' => 'updateUser',
                    'requestBody' => [
                        'description' => 'Updated user data',
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => ['type' => 'string'],
                                        'email' => ['type' => 'string', 'format' => 'email'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'User updated successfully',
                        ],
                        '400' => [
                            'description' => 'Invalid user data',
                        ],
                        '404' => [
                            'description' => 'User not found',
                        ],
                    ],
                ],
                'delete' => [
                    'tags' => ['users'],
                    'summary' => 'Delete user',
                    'description' => 'Delete an existing user',
                    'operationId' => 'deleteUser',
                    'responses' => [
                        '204' => [
                            'description' => 'User deleted successfully',
                        ],
                        '404' => [
                            'description' => 'User not found',
                        ],
                    ],
                ],
            ],
        ],
        'components' => [
            'securitySchemes' => [
                'BearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                ],
            ],
        ],
        'security' => [
            ['BearerAuth' => []],
        ],
    ]));

    // Verify document structure
    expect($document)->toBeInstanceOf(OpenApiDocument::class);
    expect($document->openapi)->toBe('3.0.3');
    expect($document->info->title)->toBe('User Management API');
    expect($document->paths)->toHaveCount(2);

    // Verify paths structure
    expect($document->paths)->toHaveKey('/users');
    expect($document->paths)->toHaveKey('/users/{userId}');
    expect($document->paths['/users'])->toBeInstanceOf(PathItem::class);
    expect($document->paths['/users/{userId}'])->toBeInstanceOf(PathItem::class);

    // Verify /users path item
    $usersPath = $document->paths['/users'];
    expect($usersPath->summary)->toBe('User collection operations');
    expect($usersPath->description)->toBe('Operations for managing the collection of users');
    expect($usersPath->get)->toBeInstanceOf(Operation::class);
    expect($usersPath->post)->toBeInstanceOf(Operation::class);
    expect($usersPath->put)->toBeNull();
    expect($usersPath->delete)->toBeNull();

    // Verify GET /users operation
    $getUsersOp = $usersPath->get;
    expect($getUsersOp->tags)->toBe(['users']);
    expect($getUsersOp->summary)->toBe('List all users');
    expect($getUsersOp->operationId)->toBe('listUsers');
    expect($getUsersOp->parameters)->toHaveCount(2);
    expect($getUsersOp->parameters[0]->name)->toBe('page');
    expect($getUsersOp->parameters[0]->in)->toBe('query');
    expect($getUsersOp->parameters[1]->name)->toBe('limit');
    expect($getUsersOp->parameters[1]->in)->toBe('query');
    expect($getUsersOp->responses)->toHaveCount(2);
    expect($getUsersOp->responses)->toHaveKey('200');
    expect($getUsersOp->responses)->toHaveKey('400');

    // Verify POST /users operation
    $createUserOp = $usersPath->post;
    expect($createUserOp->tags)->toBe(['users']);
    expect($createUserOp->summary)->toBe('Create a new user');
    expect($createUserOp->operationId)->toBe('createUser');
    expect($createUserOp->requestBody)->not->toBeNull();
    expect($createUserOp->requestBody->required)->toBeTrue();
    expect($createUserOp->requestBody->content)->toHaveKey('application/json');
    expect($createUserOp->responses)->toHaveCount(3);
    expect($createUserOp->responses)->toHaveKey('201');
    expect($createUserOp->responses)->toHaveKey('400');
    expect($createUserOp->responses)->toHaveKey('409');

    // Verify /users/{userId} path item
    $userByIdPath = $document->paths['/users/{userId}'];
    expect($userByIdPath->summary)->toBe('Individual user operations');
    expect($userByIdPath->parameters)->toHaveCount(1);
    expect($userByIdPath->parameters[0]->name)->toBe('userId');
    expect($userByIdPath->parameters[0]->in)->toBe('path');
    expect($userByIdPath->parameters[0]->required)->toBeTrue();

    $operations = $userByIdPath->getOperations();
    expect($operations)->toHaveCount(3);
    expect($operations)->toHaveKey('get');
    expect($operations)->toHaveKey('put');
    expect($operations)->toHaveKey('delete');

    // Verify GET /users/{userId} operation
    expect($operations['get']->operationId)->toBe('getUserById');
    expect($operations['get']->responses)->toHaveKey('200');
    expect($operations['get']->responses)->toHaveKey('404');

    // Verify PUT /users/{userId} operation
    expect($operations['put']->operationId)->toBe('updateUser');
    expect($operations['put']->requestBody)->not->toBeNull();
    expect($operations['put']->responses)->toHaveKey('200');
    expect($operations['put']->responses)->toHaveKey('400');
    expect($operations['put']->responses)->toHaveKey('404');

    // Verify DELETE /users/{userId} operation
    expect($operations['delete']->operationId)->toBe('deleteUser');
    expect($operations['delete']->responses)->toHaveKey('204');
    expect($operations['delete']->responses)->toHaveKey('404');
});
