<?php declare(strict_types=1);
use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\RequestBody;

it('rejects request body missing required content', function () {

    try {
        RequestBody::fromArray([
            'description' => 'Missing content field',
            'required' => true,
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('content');
        expect($e->getMessages()['content'])->toContain('content is required.');
    }
});

it('rejects request body with empty content array', function () {

    try {
        RequestBody::fromArray([
            'description' => 'Empty content array',
            'content' => [],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('content');
        expect($e->getMessages()['content'])->toContain('content is required.');
    }
});

it('rejects request body with non-array content', function () {

    try {
        RequestBody::fromArray([
            'description' => 'Invalid content type',
            'content' => 'not-an-array',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('content');
        expect($e->getMessages()['content'])->toContain('content must be an array.');
    }
});

it('rejects request body with non-string description', function () {

    try {
        RequestBody::fromArray([
            'description' => 123,
            'content' => [
                'application/json' => [
                    'schema' => ['type' => 'object'],
                ],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('description');
        expect($e->getMessages()['description'])->toContain('description must be a string.');
    }
});

it('rejects request body with invalid schema in content', function () {

    try {
        RequestBody::fromArray([
            'description' => 'Request body with invalid schema',
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'array',
                        'minItems' => -1, // Invalid constraint
                    ],
                ],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('content.application/json.schema.minItems');
        expect($e->getMessages()['content.application/json.schema.minItems'])->toContain('The min items must be at least 0.');
    }
});

it('accepts request body with valid optional fields', function () {

    $requestBody = RequestBody::fromArray([
        'description' => 'Valid request body with all fields',
        'required' => true,
        'content' => [
            'application/json' => [
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'age' => ['type' => 'integer', 'minimum' => 0],
                    ],
                ],
                'example' => ['name' => 'John', 'age' => 25],
            ],
        ],
    ]);

    expect($requestBody->description)->toBe('Valid request body with all fields');
    expect($requestBody->required)->toBeTrue();
    expect($requestBody->content)->toHaveCount(1);
    expect($requestBody->content['application/json']->schema->properties)->toHaveKey('name');
    expect($requestBody->content['application/json']->example['name'])->toBe('John');
});

it('accepts request body without optional description', function () {

    $requestBody = RequestBody::fromArray([
        'content' => [
            'application/json' => [
                'schema' => ['type' => 'string'],
            ],
        ],
    ]);

    expect($requestBody->description)->toBeNull();
    expect($requestBody->required)->toBeFalse();
    expect($requestBody->content)->toHaveCount(1);
});

it('accepts request body with false required flag', function () {

    $requestBody = RequestBody::fromArray([
        'description' => 'Optional request body',
        'required' => false,
        'content' => [
            'application/json' => [
                'schema' => ['type' => 'object'],
            ],
        ],
    ]);

    expect($requestBody->description)->toBe('Optional request body');
    expect($requestBody->required)->toBeFalse();
});
