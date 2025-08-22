<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\Components;
use Bambamboole\OpenApi\Objects\Schema;

it('can create empty Components', function () {
    $components = Components::fromArray([]);

    expect($components->schemas)->toBeEmpty()
        ->and($components->responses)->toBeEmpty()
        ->and($components->parameters)->toBeEmpty()
        ->and($components->examples)->toBeEmpty()
        ->and($components->requestBodies)->toBeEmpty()
        ->and($components->headers)->toBeEmpty()
        ->and($components->securitySchemes)->toBeEmpty()
        ->and($components->links)->toBeEmpty()
        ->and($components->callbacks)->toBeEmpty();
});

it('can create Components with schemas', function () {
    $components = Components::fromArray([
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
                ],
                'required' => ['id', 'name'],
            ],
            'Pet' => [
                'type' => 'object',
                'properties' => [
                    'name' => [
                        'type' => 'string',
                    ],
                    'species' => [
                        'type' => 'string',
                        'enum' => ['dog', 'cat', 'bird'],
                    ],
                ],
                'required' => ['name', 'species'],
            ],
        ],
    ]);

    expect($components->schemas)->toHaveCount(2)
        ->and($components->schemas['User'])->toBeInstanceOf(Schema::class)
        ->and($components->schemas['Pet'])->toBeInstanceOf(Schema::class)
        ->and($components->schemas['User']->type)->toBe('object')
        ->and($components->schemas['Pet']->type)->toBe('object');
});

it('can create Components with responses', function () {
    $components = Components::fromArray([
        'responses' => [
            'NotFound' => [
                'description' => 'Entity not found.',
            ],
            'IllegalInput' => [
                'description' => 'Illegal input for operation.',
            ],
        ],
    ]);

    expect($components->responses)->toHaveCount(2)
        ->and($components->responses['NotFound'])->toBeArray()
        ->and($components->responses['IllegalInput'])->toBeArray();
});

it('can create Components with parameters', function () {
    $components = Components::fromArray([
        'parameters' => [
            'skipParam' => [
                'name' => 'skip',
                'in' => 'query',
                'description' => 'number of items to skip',
                'required' => true,
                'schema' => [
                    'type' => 'integer',
                    'format' => 'int32',
                ],
            ],
            'limitParam' => [
                'name' => 'limit',
                'in' => 'query',
                'description' => 'max records to return',
                'required' => true,
                'schema' => [
                    'type' => 'integer',
                    'format' => 'int32',
                ],
            ],
        ],
    ]);

    expect($components->parameters)->toHaveCount(2)
        ->and($components->parameters['skipParam'])->toBeArray()
        ->and($components->parameters['limitParam'])->toBeArray();
});

it('can create Components with all sections', function () {
    $components = Components::fromArray([
        'schemas' => [
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'code' => [
                        'type' => 'integer',
                        'format' => 'int32',
                    ],
                    'message' => [
                        'type' => 'string',
                    ],
                ],
                'required' => ['code', 'message'],
            ],
        ],
        'responses' => [
            'GenericError' => [
                'description' => 'An error occurred',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Error',
                        ],
                    ],
                ],
            ],
        ],
        'parameters' => [
            'offsetParam' => [
                'name' => 'offset',
                'in' => 'query',
                'required' => false,
                'schema' => [
                    'type' => 'integer',
                    'minimum' => 0,
                    'default' => 0,
                ],
            ],
        ],
        'examples' => [
            'user-example' => [
                'summary' => 'A user object example',
                'value' => [
                    'id' => 1,
                    'name' => 'John Doe',
                ],
            ],
        ],
        'requestBodies' => [
            'UserArray' => [
                'description' => 'user to add to the system',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/components/schemas/User',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'headers' => [
            'X-Rate-Limit-Limit' => [
                'description' => 'The number of allowed requests in the current period',
                'schema' => [
                    'type' => 'integer',
                ],
            ],
        ],
        'securitySchemes' => [
            'api_key' => [
                'type' => 'apiKey',
                'name' => 'api_key',
                'in' => 'header',
            ],
        ],
        'links' => [
            'UserRepositories' => [
                'operationId' => 'getRepositoriesByOwner',
            ],
        ],
        'callbacks' => [
            'myEvent' => [
                '{$request.query.queryUrl}' => [
                    'post' => [
                        'requestBody' => [
                            'description' => 'Callback payload',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    expect($components->schemas)->toHaveCount(1)
        ->and($components->responses)->toHaveCount(1)
        ->and($components->parameters)->toHaveCount(1)
        ->and($components->examples)->toHaveCount(1)
        ->and($components->requestBodies)->toHaveCount(1)
        ->and($components->headers)->toHaveCount(1)
        ->and($components->securitySchemes)->toHaveCount(1)
        ->and($components->links)->toHaveCount(1)
        ->and($components->callbacks)->toHaveCount(1);
});
