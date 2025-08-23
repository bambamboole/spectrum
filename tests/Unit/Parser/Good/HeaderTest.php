<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\Header;
use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\OpenApiParser;

it('can parse basic header in components', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Header Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'headers' => [
                'X-Rate-Limit' => [
                    'description' => 'The number of allowed requests in the current period',
                    'schema' => [
                        'type' => 'integer',
                    ],
                ],
            ],
        ],
    ]);

    expect($document)->toBeInstanceOf(OpenApiDocument::class);
    expect($document->components->headers)->toHaveKey('X-Rate-Limit');

    $header = $document->components->headers['X-Rate-Limit'];
    expect($header)->toBeInstanceOf(Header::class);
    expect($header->description)->toBe('The number of allowed requests in the current period');
    expect($header->schema->type)->toBe('integer');
    expect($header->required)->toBeFalse(); // Default value
    expect($header->deprecated)->toBeFalse(); // Default value
});

it('can parse header with all properties', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Advanced Header Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'headers' => [
                'X-Custom-Header' => [
                    'description' => 'Custom header with all properties',
                    'required' => true,
                    'deprecated' => false,
                    'allowEmptyValue' => true,
                    'style' => 'simple',
                    'explode' => false,
                    'schema' => [
                        'type' => 'string',
                        'enum' => ['value1', 'value2', 'value3'],
                    ],
                    'example' => 'value1',
                ],
            ],
        ],
    ]);

    $header = $document->components->headers['X-Custom-Header'];

    expect($header->description)->toBe('Custom header with all properties');
    expect($header->required)->toBeTrue();
    expect($header->deprecated)->toBeFalse();
    expect($header->allowEmptyValue)->toBeTrue();
    expect($header->style)->toBe('simple');
    expect($header->explode)->toBeFalse();
    expect($header->schema->type)->toBe('string');
    expect($header->schema->enum)->toBe(['value1', 'value2', 'value3']);
    expect($header->example)->toBe('value1');
});

it('can parse multiple headers', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Multiple Headers Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'headers' => [
                'X-Rate-Limit-Limit' => [
                    'description' => 'The number of allowed requests in the current period',
                    'schema' => ['type' => 'integer'],
                ],
                'X-Rate-Limit-Remaining' => [
                    'description' => 'The number of remaining requests in the current period',
                    'schema' => ['type' => 'integer'],
                ],
                'X-Rate-Limit-Reset' => [
                    'description' => 'The timestamp when the current period ends',
                    'schema' => ['type' => 'integer', 'format' => 'int64'],
                ],
                'X-Request-ID' => [
                    'description' => 'Unique request identifier',
                    'required' => true,
                    'schema' => ['type' => 'string', 'format' => 'uuid'],
                ],
            ],
        ],
    ]);

    expect($document->components->headers)->toHaveCount(4);

    expect($document->components->headers['X-Rate-Limit-Limit']->description)
        ->toBe('The number of allowed requests in the current period');
    expect($document->components->headers['X-Rate-Limit-Remaining']->schema->type)
        ->toBe('integer');
    expect($document->components->headers['X-Rate-Limit-Reset']->schema->format)
        ->toBe('int64');
    expect($document->components->headers['X-Request-ID']->required)
        ->toBeTrue();
    expect($document->components->headers['X-Request-ID']->schema->format)
        ->toBe('uuid');
});

it('can parse header references', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Header Reference Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'headers' => [
                'BaseHeader' => [
                    'description' => 'Base header for rate limiting',
                    'schema' => ['type' => 'integer'],
                ],
                'ReferenceHeader' => [
                    '$ref' => '#/components/headers/BaseHeader',
                ],
            ],
        ],
    ]);

    expect($document->components->headers)->toHaveCount(2);

    $baseHeader = $document->components->headers['BaseHeader'];
    $refHeader = $document->components->headers['ReferenceHeader'];

    expect($baseHeader->description)->toBe('Base header for rate limiting');
    expect($baseHeader->schema->type)->toBe('integer');

    // The referenced header should be resolved
    expect($refHeader->description)->toBe('Base header for rate limiting');
    expect($refHeader->schema->type)->toBe('integer');
});

it('can parse header with complex schema', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Complex Header Schema Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'headers' => [
                'X-Complex-Header' => [
                    'description' => 'Header with complex array schema',
                    'schema' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'key' => ['type' => 'string'],
                                'value' => ['type' => 'string'],
                            ],
                            'required' => ['key', 'value'],
                        ],
                        'minItems' => 1,
                        'maxItems' => 10,
                    ],
                    'style' => 'simple',
                    'explode' => false,
                ],
            ],
        ],
    ]);

    $header = $document->components->headers['X-Complex-Header'];

    expect($header->description)->toBe('Header with complex array schema');
    expect($header->schema->type)->toBe('array');
    expect($header->schema->items->type)->toBe('object');
    expect($header->schema->items->properties)->toHaveKey('key');
    expect($header->schema->items->properties)->toHaveKey('value');
    expect($header->schema->items->required)->toBe(['key', 'value']);
    expect($header->schema->minItems)->toBe(1);
    expect($header->schema->maxItems)->toBe(10);
    expect($header->style)->toBe('simple');
    expect($header->explode)->toBeFalse();
});

it('can parse header with examples', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Header Examples Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'headers' => [
                'X-Example-Header' => [
                    'description' => 'Header with examples',
                    'schema' => ['type' => 'string'],
                    'example' => 'simple-example',
                    'examples' => [
                        'example1' => [
                            'summary' => 'First example',
                            'value' => 'first-value',
                        ],
                        'example2' => [
                            'summary' => 'Second example',
                            'value' => 'second-value',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $header = $document->components->headers['X-Example-Header'];

    expect($header->description)->toBe('Header with examples');
    expect($header->example)->toBe('simple-example');
    expect($header->examples)->toHaveKey('example1');
    expect($header->examples)->toHaveKey('example2');
    expect($header->examples['example1']['summary'])->toBe('First example');
    expect($header->examples['example1']['value'])->toBe('first-value');
});
