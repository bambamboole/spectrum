<?php declare(strict_types=1);

use Bambamboole\OpenApi\Context\ParsingContext;
use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Factories\ComponentsFactory;

it('rejects request body missing required content', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createRequestBody([
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
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createRequestBody([
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
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createRequestBody([
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
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createRequestBody([
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
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createRequestBody([
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
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $requestBody = $factory->createRequestBody([
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
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $requestBody = $factory->createRequestBody([
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
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $requestBody = $factory->createRequestBody([
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
