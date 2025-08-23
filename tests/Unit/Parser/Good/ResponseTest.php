<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\Response;
use Bambamboole\OpenApi\ReferenceResolver;

it('can parse minimal response with description only', function () {
    $response = Response::fromArray([
        'description' => 'Successful response',
    ]);

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->description)->toBe('Successful response');
    expect($response->headers)->toBeNull();
    expect($response->content)->toBeNull();
    expect($response->links)->toBeNull();
});

it('can parse response with headers', function () {

    $response = Response::fromArray([
        'description' => 'Response with headers',
        'headers' => [
            'X-Rate-Limit' => [
                'description' => 'Number of allowed requests per hour',
                'schema' => ['type' => 'integer'],
            ],
            'X-Expires-After' => [
                'description' => 'Date when token expires',
                'schema' => ['type' => 'string', 'format' => 'date-time'],
            ],
        ],
    ]);

    expect($response->description)->toBe('Response with headers');
    expect($response->headers)->toHaveCount(2);
    expect($response->headers)->toHaveKey('X-Rate-Limit');
    expect($response->headers)->toHaveKey('X-Expires-After');
    expect($response->headers['X-Rate-Limit']->description)->toBe('Number of allowed requests per hour');
    expect($response->headers['X-Rate-Limit']->schema->type)->toBe('integer');
});

it('can parse response with content', function () {

    $response = Response::fromArray([
        'description' => 'User object response',
        'content' => [
            'application/json' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                        'email' => ['type' => 'string', 'format' => 'email'],
                    ],
                    'required' => ['id', 'name'],
                ],
                'example' => [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
            ],
            'text/plain' => [
                'schema' => ['type' => 'string'],
                'example' => 'User: John Doe (john@example.com)',
            ],
        ],
    ]);

    expect($response->description)->toBe('User object response');
    expect($response->content)->toHaveCount(2);
    expect($response->content)->toHaveKey('application/json');
    expect($response->content)->toHaveKey('text/plain');
    expect($response->content['application/json']->schema->type)->toBe('object');
    expect($response->content['application/json']->schema->properties)->toHaveKey('id');
    expect($response->content['application/json']->example['name'])->toBe('John Doe');
    expect($response->content['text/plain']->schema->type)->toBe('string');
});

it('can parse response with all properties', function () {

    $response = Response::fromArray([
        'description' => 'Complete response with all features',
        'headers' => [
            'X-Response-Id' => [
                'description' => 'Unique response identifier',
                'schema' => ['type' => 'string'],
            ],
        ],
        'content' => [
            'application/json' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'data' => ['type' => 'string'],
                        'meta' => [
                            'type' => 'object',
                            'properties' => [
                                'timestamp' => ['type' => 'string', 'format' => 'date-time'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'links' => [
            'GetUserByUserId' => [
                'operationRef' => '#/paths/~1users~1{userId}/get',
                'parameters' => [
                    'userId' => '$response.body#/id',
                ],
            ],
        ],
    ]);

    expect($response->description)->toBe('Complete response with all features');
    expect($response->headers)->toHaveCount(1);
    expect($response->headers['X-Response-Id']->description)->toBe('Unique response identifier');
    expect($response->content)->toHaveCount(1);
    expect($response->content['application/json']->schema->properties)->toHaveKey('data');
    expect($response->content['application/json']->schema->properties)->toHaveKey('meta');
    expect($response->links)->toHaveKey('GetUserByUserId');
    expect($response->links['GetUserByUserId']->operationRef)->toBe('#/paths/~1users~1{userId}/get');
});

it('can parse response with schema reference', function () {
    ReferenceResolver::initialize([
        'openapi' => '3.0.0',
        'info' => [],
        'paths' => [],
        'components' => [
            'schemas' => [
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                    ],
                ],
            ],
        ],
    ]);

    $response = Response::fromArray([
        'description' => 'User response using schema reference',
        'content' => [
            'application/json' => [
                'schema' => [
                    '$ref' => '#/components/schemas/User',
                ],
            ],
        ],
    ]);

    expect($response->description)->toBe('User response using schema reference');
    expect($response->content['application/json']->schema->type)->toBe('object');
    expect($response->content['application/json']->schema->properties)->toHaveKey('id');
    expect($response->content['application/json']->schema->properties)->toHaveKey('name');

    ReferenceResolver::clear();
});

it('can parse multiple responses in components', function () {

    $responses = Response::multiple([
        'Success' => [
            'description' => 'Successful operation',
            'content' => [
                'application/json' => [
                    'schema' => ['type' => 'object'],
                ],
            ],
        ],
        'NotFound' => [
            'description' => 'Resource not found',
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'error' => ['type' => 'string'],
                            'code' => ['type' => 'integer'],
                        ],
                    ],
                ],
            ],
        ],
        'Unauthorized' => [
            'description' => 'Authentication required',
        ],
    ]);

    expect($responses)->toHaveCount(3);
    expect($responses)->toHaveKey('Success');
    expect($responses)->toHaveKey('NotFound');
    expect($responses)->toHaveKey('Unauthorized');
    expect($responses['Success'])->toBeInstanceOf(Response::class);
    expect($responses['Success']->description)->toBe('Successful operation');
    expect($responses['NotFound']->content['application/json']->schema->properties)->toHaveKey('error');
    expect($responses['Unauthorized']->content)->toBeNull();
});

it('can parse response with complex nested content structure', function () {

    $response = Response::fromArray([
        'description' => 'Paginated list response',
        'content' => [
            'application/json' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'data' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'integer'],
                                    'attributes' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'name' => ['type' => 'string'],
                                            'createdAt' => ['type' => 'string', 'format' => 'date-time'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'meta' => [
                            'type' => 'object',
                            'properties' => [
                                'pagination' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'page' => ['type' => 'integer'],
                                        'perPage' => ['type' => 'integer'],
                                        'total' => ['type' => 'integer'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'examples' => [
                    'users_page' => [
                        'summary' => 'First page of users',
                        'value' => [
                            'data' => [
                                [
                                    'id' => 1,
                                    'attributes' => [
                                        'name' => 'John Doe',
                                        'createdAt' => '2023-01-01T00:00:00Z',
                                    ],
                                ],
                            ],
                            'meta' => [
                                'pagination' => [
                                    'page' => 1,
                                    'perPage' => 10,
                                    'total' => 100,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    expect($response->description)->toBe('Paginated list response');
    expect($response->content['application/json']->schema->properties['data']->type)->toBe('array');
    expect($response->content['application/json']->schema->properties['data']->items->properties)->toHaveKey('id');
    expect($response->content['application/json']->schema->properties['meta']->properties)->toHaveKey('pagination');
    expect($response->content['application/json']->examples)->toHaveKey('users_page');
    expect($response->content['application/json']->examples['users_page']['value']['data'][0]['id'])->toBe(1);
});
