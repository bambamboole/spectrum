<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\Operation;
use Bambamboole\OpenApi\ReferenceResolver;

it('can parse minimal operation with responses only', function () {
    $operation = Operation::fromArray([
        'responses' => [
            '200' => [
                'description' => 'Successful response',
            ],
        ],
    ]);

    expect($operation)->toBeInstanceOf(Operation::class);
    expect($operation->responses)->toHaveCount(1);
    expect($operation->responses)->toHaveKey('200');
    expect($operation->responses['200']->description)->toBe('Successful response');
    expect($operation->tags)->toBe([]);
    expect($operation->summary)->toBeNull();
    expect($operation->description)->toBeNull();
    expect($operation->operationId)->toBeNull();
    expect($operation->parameters)->toBeNull();
    expect($operation->requestBody)->toBeNull();
    expect($operation->deprecated)->toBeFalse();
});

it('can parse operation with all basic properties', function () {
    $operation = Operation::fromArray([
        'tags' => ['users', 'authentication'],
        'summary' => 'Get user profile',
        'description' => 'Retrieve the authenticated user profile information',
        'operationId' => 'getUserProfile',
        'deprecated' => true,
        'responses' => [
            '200' => [
                'description' => 'User profile retrieved successfully',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'integer'],
                                'name' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
            '401' => [
                'description' => 'Unauthorized',
            ],
        ],
    ]);

    expect($operation->tags)->toBe(['users', 'authentication']);
    expect($operation->summary)->toBe('Get user profile');
    expect($operation->description)->toBe('Retrieve the authenticated user profile information');
    expect($operation->operationId)->toBe('getUserProfile');
    expect($operation->deprecated)->toBeTrue();
    expect($operation->responses)->toHaveCount(2);
    expect($operation->responses)->toHaveKey('200');
    expect($operation->responses)->toHaveKey('401');
});

it('can parse operation with parameters', function () {
    $operation = Operation::fromArray([
        'parameters' => [
            [
                'name' => 'userId',
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'integer'],
            ],
            [
                'name' => 'include',
                'in' => 'query',
                'schema' => ['type' => 'string'],
            ],
        ],
        'responses' => [
            '200' => [
                'description' => 'Success',
            ],
        ],
    ]);

    expect($operation->parameters)->toHaveCount(2);
    expect($operation->parameters[0]->name)->toBe('userId');
    expect($operation->parameters[0]->in)->toBe('path');
    expect($operation->parameters[0]->required)->toBeTrue();
    expect($operation->parameters[1]->name)->toBe('include');
    expect($operation->parameters[1]->in)->toBe('query');
    expect($operation->parameters[1]->required)->toBeFalse();
});

it('can parse operation with request body', function () {
    $operation = Operation::fromArray([
        'requestBody' => [
            'description' => 'User data to create',
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'email' => ['type' => 'string', 'format' => 'email'],
                        ],
                        'required' => ['name', 'email'],
                    ],
                ],
            ],
        ],
        'responses' => [
            '201' => [
                'description' => 'User created',
            ],
        ],
    ]);

    expect($operation->requestBody)->not->toBeNull();
    expect($operation->requestBody->description)->toBe('User data to create');
    expect($operation->requestBody->required)->toBeTrue();
    expect($operation->requestBody->content)->toHaveKey('application/json');
});

it('can parse operation with external docs', function () {
    $operation = Operation::fromArray([
        'externalDocs' => [
            'description' => 'Find more info here',
            'url' => 'https://example.com/docs',
        ],
        'responses' => [
            '200' => [
                'description' => 'Success',
            ],
        ],
    ]);

    expect($operation->externalDocs)->not->toBeNull();
    expect($operation->externalDocs->description)->toBe('Find more info here');
    expect($operation->externalDocs->url)->toBe('https://example.com/docs');
});

it('can parse operation with callbacks', function () {
    $operation = Operation::fromArray([
        'callbacks' => [
            'paymentCallback' => [
                '{$request.body#/callbackUrl}' => [
                    'post' => [
                        'requestBody' => [
                            'description' => 'Payment result',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['type' => 'object'],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Callback processed',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'responses' => [
            '200' => [
                'description' => 'Success',
            ],
        ],
    ]);

    expect($operation->callbacks)->toHaveCount(1);
    expect($operation->callbacks)->toHaveKey('paymentCallback');
    expect($operation->callbacks['paymentCallback']->expressions)->toHaveKey('{$request.body#/callbackUrl}');
});

it('can parse operation with security requirements', function () {
    $operation = Operation::fromArray([
        'security' => [
            ['ApiKeyAuth' => []],
            ['OAuth2Auth' => ['read', 'write']],
        ],
        'responses' => [
            '200' => [
                'description' => 'Success',
            ],
        ],
    ]);

    expect($operation->security)->toHaveCount(2);
    expect($operation->security[0]->requirements)->toHaveKey('ApiKeyAuth');
    expect($operation->security[0]->requirements['ApiKeyAuth'])->toBe([]);
    expect($operation->security[1]->requirements)->toHaveKey('OAuth2Auth');
    expect($operation->security[1]->requirements['OAuth2Auth'])->toBe(['read', 'write']);
});

it('can parse operation with servers', function () {
    $operation = Operation::fromArray([
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
        'responses' => [
            '200' => [
                'description' => 'Success',
            ],
        ],
    ]);

    expect($operation->servers)->toHaveCount(2);
    expect($operation->servers[0]->url)->toBe('https://api.example.com/v1');
    expect($operation->servers[0]->description)->toBe('Production server');
    expect($operation->servers[1]->url)->toBe('https://staging-api.example.com/v1');
    expect($operation->servers[1]->description)->toBe('Staging server');
});

it('can parse operation with all features combined', function () {
    $operation = Operation::fromArray([
        'tags' => ['users'],
        'summary' => 'Update user',
        'description' => 'Update user information',
        'operationId' => 'updateUser',
        'externalDocs' => [
            'url' => 'https://docs.example.com',
            'description' => 'More info',
        ],
        'parameters' => [
            [
                'name' => 'userId',
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'integer'],
            ],
        ],
        'requestBody' => [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => ['type' => 'object'],
                ],
            ],
        ],
        'responses' => [
            '200' => [
                'description' => 'User updated successfully',
            ],
            '400' => [
                'description' => 'Invalid input',
            ],
            '404' => [
                'description' => 'User not found',
            ],
        ],
        'callbacks' => [
            'webhook' => [
                '{$request.body#/webhook}' => [
                    'post' => [
                        'responses' => [
                            '200' => ['description' => 'OK'],
                        ],
                    ],
                ],
            ],
        ],
        'deprecated' => false,
        'security' => [
            ['BearerAuth' => []],
        ],
        'servers' => [
            [
                'url' => 'https://api.example.com',
            ],
        ],
    ]);

    expect($operation->tags)->toBe(['users']);
    expect($operation->summary)->toBe('Update user');
    expect($operation->description)->toBe('Update user information');
    expect($operation->operationId)->toBe('updateUser');
    expect($operation->externalDocs->url)->toBe('https://docs.example.com');
    expect($operation->parameters)->toHaveCount(1);
    expect($operation->requestBody)->not->toBeNull();
    expect($operation->responses)->toHaveCount(3);
    expect($operation->callbacks)->toHaveCount(1);
    expect($operation->deprecated)->toBeFalse();
    expect($operation->security)->toHaveCount(1);
    expect($operation->servers)->toHaveCount(1);
});

it('can parse operation with reference resolution', function () {
    ReferenceResolver::initialize([
        'openapi' => '3.0.0',
        'info' => [],
        'paths' => [],
        'components' => [
            'parameters' => [
                'UserIdParam' => [
                    'name' => 'userId',
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'integer'],
                ],
            ],
            'responses' => [
                'UserResponse' => [
                    'description' => 'User information',
                    'content' => [
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $operation = Operation::fromArray([
        'parameters' => [
            ['$ref' => '#/components/parameters/UserIdParam'],
        ],
        'responses' => [
            '200' => ['$ref' => '#/components/responses/UserResponse'],
        ],
    ]);

    expect($operation->parameters)->toHaveCount(1);
    expect($operation->parameters[0]->name)->toBe('userId');
    expect($operation->parameters[0]->in)->toBe('path');
    expect($operation->responses)->toHaveCount(1);
    expect($operation->responses['200']->description)->toBe('User information');

    ReferenceResolver::clear();
});
