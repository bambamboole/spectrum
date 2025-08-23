<?php declare(strict_types=1);

use Bambamboole\OpenApi\Context\ParsingContext;
use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Factories\ComponentsFactory;

it('accepts media type with all fields empty or missing', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    // MediaType with no fields should be valid
    $mediaType = $factory->createMediaType([]);

    expect($mediaType)->toBeInstanceOf(\Bambamboole\OpenApi\Objects\MediaType::class);
    expect($mediaType->schema)->toBeNull();
    expect($mediaType->example)->toBeNull();
    expect($mediaType->examples)->toBeNull();
    expect($mediaType->encoding)->toBeNull();
});

it('rejects media type with invalid schema', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createMediaType([
            'schema' => [
                'type' => 'string',
                'minLength' => -1, // Invalid constraint
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('schema.minLength');
        expect($e->getMessages()['schema.minLength'])->toContain('The min length must be at least 0.');
    }
});

it('accepts media type with valid examples array', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $mediaType = $factory->createMediaType([
        'schema' => ['type' => 'string'],
        'examples' => [
            'example1' => [
                'summary' => 'First example',
                'value' => 'Hello World',
            ],
            'example2' => [
                'summary' => 'Second example',
                'value' => 'Goodbye World',
            ],
        ],
    ]);

    expect($mediaType->examples)->toBeArray();
    expect($mediaType->examples)->toHaveCount(2);
    expect($mediaType->examples['example1']['value'])->toBe('Hello World');
});

it('accepts media type with encoding for multipart content', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $mediaType = $factory->createMediaType([
        'schema' => [
            'type' => 'object',
            'properties' => [
                'file' => ['type' => 'string', 'format' => 'binary'],
                'description' => ['type' => 'string'],
            ],
        ],
        'encoding' => [
            'file' => [
                'contentType' => 'application/octet-stream',
                'style' => 'form',
            ],
            'description' => [
                'contentType' => 'text/plain',
            ],
        ],
    ]);

    expect($mediaType->encoding)->toBeArray();
    expect($mediaType->encoding)->toHaveKey('file');
    expect($mediaType->encoding)->toHaveKey('description');
    expect($mediaType->encoding['file']['contentType'])->toBe('application/octet-stream');
});

it('handles media type with complex nested schema', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $mediaType = $factory->createMediaType([
        'schema' => [
            'type' => 'object',
            'properties' => [
                'data' => [
                    'oneOf' => [
                        [
                            'type' => 'object',
                            'properties' => [
                                'user' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'integer'],
                                        'name' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
        ],
        'examples' => [
            'user_example' => [
                'value' => [
                    'data' => [
                        'user' => ['id' => 1, 'name' => 'John'],
                    ],
                ],
            ],
            'array_example' => [
                'value' => [
                    'data' => ['item1', 'item2', 'item3'],
                ],
            ],
        ],
    ]);

    expect($mediaType->schema->properties['data']->oneOf)->toHaveCount(2);
    expect($mediaType->examples)->toHaveCount(2);
    expect($mediaType->examples['user_example']['value']['data']['user']['name'])->toBe('John');
});

it('handles media type with various example types', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    // Test with string example
    $stringMediaType = $factory->createMediaType([
        'schema' => ['type' => 'string'],
        'example' => 'Simple string example',
    ]);

    // Test with number example
    $numberMediaType = $factory->createMediaType([
        'schema' => ['type' => 'number'],
        'example' => 42.5,
    ]);

    // Test with boolean example
    $booleanMediaType = $factory->createMediaType([
        'schema' => ['type' => 'boolean'],
        'example' => true,
    ]);

    // Test with null example
    $nullMediaType = $factory->createMediaType([
        'example' => null,
    ]);

    expect($stringMediaType->example)->toBe('Simple string example');
    expect($numberMediaType->example)->toBe(42.5);
    expect($booleanMediaType->example)->toBeTrue();
    expect($nullMediaType->example)->toBeNull();
});
