<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\RequestBody;
use Bambamboole\OpenApi\ReferenceResolver;

it('can parse minimal request body with content only', function () {

    $requestBody = RequestBody::fromArray([
        'content' => [
            'application/json' => [
                'schema' => ['type' => 'object'],
            ],
        ],
    ]);

    expect($requestBody)->toBeInstanceOf(RequestBody::class);
    expect($requestBody->content)->toHaveCount(1);
    expect($requestBody->content)->toHaveKey('application/json');
    expect($requestBody->description)->toBeNull();
    expect($requestBody->required)->toBeFalse();
});

it('can parse request body with description and required', function () {

    $requestBody = RequestBody::fromArray([
        'description' => 'User data for creating account',
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
    ]);

    expect($requestBody->description)->toBe('User data for creating account');
    expect($requestBody->required)->toBeTrue();
    expect($requestBody->content['application/json']->schema->type)->toBe('object');
    expect($requestBody->content['application/json']->schema->properties)->toHaveKey('name');
    expect($requestBody->content['application/json']->schema->properties)->toHaveKey('email');
});

it('can parse request body with multiple content types', function () {

    $requestBody = RequestBody::fromArray([
        'description' => 'User data in multiple formats',
        'content' => [
            'application/json' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'age' => ['type' => 'integer'],
                    ],
                ],
                'example' => ['name' => 'John Doe', 'age' => 30],
            ],
            'application/xml' => [
                'schema' => [
                    'type' => 'object',
                    'xml' => ['name' => 'User'],
                ],
                'example' => '<User><name>John Doe</name><age>30</age></User>',
            ],
            'application/x-www-form-urlencoded' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'age' => ['type' => 'integer'],
                    ],
                ],
                'encoding' => [
                    'name' => ['style' => 'form'],
                    'age' => ['style' => 'form'],
                ],
            ],
        ],
    ]);

    expect($requestBody->content)->toHaveCount(3);
    expect($requestBody->content)->toHaveKey('application/json');
    expect($requestBody->content)->toHaveKey('application/xml');
    expect($requestBody->content)->toHaveKey('application/x-www-form-urlencoded');
    expect($requestBody->content['application/json']->example['name'])->toBe('John Doe');
    expect($requestBody->content['application/xml']->schema->type)->toBe('object');
    expect($requestBody->content['application/x-www-form-urlencoded']->encoding)->toHaveKey('name');
});

it('can parse request body with complex nested schema', function () {

    $requestBody = RequestBody::fromArray([
        'description' => 'Complex order creation payload',
        'required' => true,
        'content' => [
            'application/json' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'customer' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'integer'],
                                'name' => ['type' => 'string'],
                                'address' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'street' => ['type' => 'string'],
                                        'city' => ['type' => 'string'],
                                        'zipCode' => ['type' => 'string'],
                                    ],
                                    'required' => ['street', 'city'],
                                ],
                            ],
                            'required' => ['id', 'name'],
                        ],
                        'items' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'productId' => ['type' => 'integer'],
                                    'quantity' => ['type' => 'integer', 'minimum' => 1],
                                    'price' => ['type' => 'number', 'minimum' => 0],
                                ],
                                'required' => ['productId', 'quantity'],
                            ],
                            'minItems' => 1,
                        ],
                        'metadata' => [
                            'type' => 'object',
                            'additionalProperties' => true,
                        ],
                    ],
                    'required' => ['customer', 'items'],
                ],
                'examples' => [
                    'simple_order' => [
                        'summary' => 'Simple order example',
                        'value' => [
                            'customer' => [
                                'id' => 123,
                                'name' => 'John Doe',
                                'address' => [
                                    'street' => '123 Main St',
                                    'city' => 'Anytown',
                                    'zipCode' => '12345',
                                ],
                            ],
                            'items' => [
                                [
                                    'productId' => 456,
                                    'quantity' => 2,
                                    'price' => 29.99,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    expect($requestBody->description)->toBe('Complex order creation payload');
    expect($requestBody->required)->toBeTrue();
    expect($requestBody->content['application/json']->schema->properties['customer']->properties)->toHaveKey('address');
    expect($requestBody->content['application/json']->schema->properties['items']->type)->toBe('array');
    expect($requestBody->content['application/json']->schema->properties['items']->items->properties)->toHaveKey('productId');
    expect($requestBody->content['application/json']->examples)->toHaveKey('simple_order');
    expect($requestBody->content['application/json']->examples['simple_order']['value']['customer']['id'])->toBe(123);
});

it('can parse request body with schema reference', function () {
    ReferenceResolver::initialize([
        'openapi' => '3.0.0',
        'info' => [],
        'paths' => [],
        'components' => [
            'schemas' => [
                'CreateUserRequest' => [
                    'type' => 'object',
                    'properties' => [
                        'username' => ['type' => 'string', 'minLength' => 3],
                        'password' => ['type' => 'string', 'minLength' => 8],
                        'email' => ['type' => 'string', 'format' => 'email'],
                    ],
                    'required' => ['username', 'password'],
                ],
            ],
        ],
    ]);

    $requestBody = RequestBody::fromArray([
        'description' => 'User creation data using schema reference',
        'required' => true,
        'content' => [
            'application/json' => [
                'schema' => [
                    '$ref' => '#/components/schemas/CreateUserRequest',
                ],
            ],
        ],
    ]);

    expect($requestBody->description)->toBe('User creation data using schema reference');
    expect($requestBody->content['application/json']->schema->type)->toBe('object');
    expect($requestBody->content['application/json']->schema->properties)->toHaveKey('username');
    expect($requestBody->content['application/json']->schema->properties)->toHaveKey('password');
    expect($requestBody->content['application/json']->schema->properties['username']->minLength)->toBe(3);

    ReferenceResolver::clear();
});

it('can parse multiple request bodies in components', function () {

    $requestBodies = RequestBody::multiple([
        'UserCreate' => [
            'description' => 'User creation payload',
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'email' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ],
        'UserUpdate' => [
            'description' => 'User update payload',
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'bio' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ],
        'FileUpload' => [
            'description' => 'File upload payload',
            'required' => true,
            'content' => [
                'multipart/form-data' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'file' => ['type' => 'string', 'format' => 'binary'],
                            'filename' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    expect($requestBodies)->toHaveCount(3);
    expect($requestBodies)->toHaveKey('UserCreate');
    expect($requestBodies)->toHaveKey('UserUpdate');
    expect($requestBodies)->toHaveKey('FileUpload');
    expect($requestBodies['UserCreate'])->toBeInstanceOf(RequestBody::class);
    expect($requestBodies['UserCreate']->required)->toBeTrue();
    expect($requestBodies['UserUpdate']->required)->toBeFalse();
    expect($requestBodies['FileUpload']->content)->toHaveKey('multipart/form-data');
    expect($requestBodies['FileUpload']->content['multipart/form-data']->schema->properties)->toHaveKey('file');
});

it('can parse file upload request body with encoding', function () {

    $requestBody = RequestBody::fromArray([
        'description' => 'File upload with metadata',
        'required' => true,
        'content' => [
            'multipart/form-data' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'file' => [
                            'type' => 'string',
                            'format' => 'binary',
                        ],
                        'filename' => [
                            'type' => 'string',
                        ],
                        'metadata' => [
                            'type' => 'object',
                            'properties' => [
                                'tags' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                ],
                                'description' => ['type' => 'string'],
                            ],
                        ],
                    ],
                    'required' => ['file'],
                ],
                'encoding' => [
                    'file' => [
                        'contentType' => 'image/*',
                    ],
                    'metadata' => [
                        'contentType' => 'application/json',
                    ],
                ],
            ],
        ],
    ]);

    expect($requestBody->description)->toBe('File upload with metadata');
    expect($requestBody->required)->toBeTrue();
    expect($requestBody->content['multipart/form-data']->schema->properties)->toHaveKey('file');
    expect($requestBody->content['multipart/form-data']->schema->properties['file']->format)->toBe('binary');
    expect($requestBody->content['multipart/form-data']->encoding)->toHaveKey('file');
    expect($requestBody->content['multipart/form-data']->encoding['file']['contentType'])->toBe('image/*');
});
