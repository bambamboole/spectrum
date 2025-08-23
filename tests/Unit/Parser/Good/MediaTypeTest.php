<?php declare(strict_types=1);

use Bambamboole\OpenApi\Context\ParsingContext;
use Bambamboole\OpenApi\Factories\ComponentsFactory;
use Bambamboole\OpenApi\Objects\MediaType;

it('can parse basic media type with schema', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $mediaType = $factory->createMediaType([
        'schema' => [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'name' => ['type' => 'string'],
            ],
            'required' => ['id', 'name'],
        ],
    ]);

    expect($mediaType)->toBeInstanceOf(MediaType::class);
    expect($mediaType->schema->type)->toBe('object');
    expect($mediaType->schema->properties)->toHaveKey('id');
    expect($mediaType->schema->properties)->toHaveKey('name');
    expect($mediaType->schema->required)->toBe(['id', 'name']);
    expect($mediaType->example)->toBeNull();
    expect($mediaType->examples)->toBeNull();
});

it('can parse media type with example', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $mediaType = $factory->createMediaType([
        'schema' => ['type' => 'string'],
        'example' => 'Hello World',
    ]);

    expect($mediaType->schema->type)->toBe('string');
    expect($mediaType->example)->toBe('Hello World');
});

it('can parse media type with multiple examples', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $mediaType = $factory->createMediaType([
        'schema' => [
            'type' => 'object',
            'properties' => [
                'message' => ['type' => 'string'],
            ],
        ],
        'examples' => [
            'success' => [
                'summary' => 'Success response',
                'value' => ['message' => 'Operation completed successfully'],
            ],
            'error' => [
                'summary' => 'Error response',
                'value' => ['message' => 'Something went wrong'],
            ],
        ],
    ]);

    expect($mediaType->schema->type)->toBe('object');
    expect($mediaType->examples)->toHaveKey('success');
    expect($mediaType->examples)->toHaveKey('error');
    expect($mediaType->examples['success']['summary'])->toBe('Success response');
    expect($mediaType->examples['success']['value']['message'])->toBe('Operation completed successfully');
});

it('can parse media type with complex schema', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $mediaType = $factory->createMediaType([
        'schema' => [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'tags' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                ],
                'required' => ['id', 'name'],
            ],
            'minItems' => 1,
        ],
        'example' => [
            ['id' => 1, 'name' => 'Item 1', 'tags' => ['tag1', 'tag2']],
            ['id' => 2, 'name' => 'Item 2', 'tags' => ['tag3']],
        ],
    ]);

    expect($mediaType->schema->type)->toBe('array');
    expect($mediaType->schema->items->type)->toBe('object');
    expect($mediaType->schema->items->properties)->toHaveKey('id');
    expect($mediaType->schema->items->properties)->toHaveKey('name');
    expect($mediaType->schema->items->properties)->toHaveKey('tags');
    expect($mediaType->schema->minItems)->toBe(1);
    expect($mediaType->example)->toBeArray();
    expect($mediaType->example)->toHaveCount(2);
});

it('can parse multiple media types in content', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $mediaTypes = $factory->createMediaTypes([
        'application/json' => [
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'string'],
                ],
            ],
            'example' => ['data' => 'JSON example'],
        ],
        'application/xml' => [
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'string'],
                ],
            ],
            'example' => '<data>XML example</data>',
        ],
        'text/plain' => [
            'schema' => ['type' => 'string'],
            'example' => 'Plain text example',
        ],
    ]);

    expect($mediaTypes)->toHaveCount(3);
    expect($mediaTypes)->toHaveKey('application/json');
    expect($mediaTypes)->toHaveKey('application/xml');
    expect($mediaTypes)->toHaveKey('text/plain');

    expect($mediaTypes['application/json'])->toBeInstanceOf(MediaType::class);
    expect($mediaTypes['application/json']->schema->type)->toBe('object');
    expect($mediaTypes['application/json']->example['data'])->toBe('JSON example');

    expect($mediaTypes['application/xml']->schema->type)->toBe('object');
    expect($mediaTypes['application/xml']->example)->toBe('<data>XML example</data>');

    expect($mediaTypes['text/plain']->schema->type)->toBe('string');
    expect($mediaTypes['text/plain']->example)->toBe('Plain text example');
});

it('can parse media type with encoding', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $mediaType = $factory->createMediaType([
        'schema' => [
            'type' => 'object',
            'properties' => [
                'file' => ['type' => 'string', 'format' => 'binary'],
                'metadata' => ['type' => 'string'],
            ],
        ],
        'encoding' => [
            'file' => [
                'contentType' => 'image/png, image/jpeg',
                'headers' => [
                    'X-Custom-Header' => [
                        'schema' => ['type' => 'string'],
                    ],
                ],
            ],
            'metadata' => [
                'contentType' => 'application/json',
            ],
        ],
    ]);

    expect($mediaType->schema->type)->toBe('object');
    expect($mediaType->encoding)->toHaveKey('file');
    expect($mediaType->encoding)->toHaveKey('metadata');
    expect($mediaType->encoding['file']['contentType'])->toBe('image/png, image/jpeg');
    expect($mediaType->encoding['metadata']['contentType'])->toBe('application/json');
});

it('can parse minimal media type with no schema', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $mediaType = $factory->createMediaType([
        'example' => 'Just an example without schema',
    ]);

    expect($mediaType->schema)->toBeNull();
    expect($mediaType->example)->toBe('Just an example without schema');
    expect($mediaType->examples)->toBeNull();
    expect($mediaType->encoding)->toBeNull();
});

it('can parse media type with schema reference', function () {
    $context = ParsingContext::fromDocument([
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
    $factory = ComponentsFactory::create($context);

    $mediaType = $factory->createMediaType([
        'schema' => [
            '$ref' => '#/components/schemas/User',
        ],
        'example' => ['id' => 1, 'name' => 'John Doe'],
    ]);

    expect($mediaType->schema->type)->toBe('object');
    expect($mediaType->schema->properties)->toHaveKey('id');
    expect($mediaType->schema->properties)->toHaveKey('name');
    expect($mediaType->example['id'])->toBe(1);
    expect($mediaType->example['name'])->toBe('John Doe');
});
