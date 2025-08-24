<?php declare(strict_types=1);

use App\Exceptions\ParseException;
use App\OpenApiParser;

it('rejects header with empty description', function () {
    $this->expectSchema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Invalid Header API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'headers' => [
                'InvalidHeader' => [
                    'description' => '', // Empty description not allowed
                    'schema' => ['type' => 'string'],
                ],
            ],
        ],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toHaveKey('components.headers.InvalidHeader.description');
        expect($e->getMessages()['components.headers.InvalidHeader.description'])
            ->toContain('description must be filled in.');
    });
});

it('rejects header with empty style', function () {
    $this->expectSchema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Invalid Header API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'headers' => [
                'InvalidHeader' => [
                    'style' => '', // Empty style not allowed
                    'schema' => ['type' => 'string'],
                ],
            ],
        ],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toHaveKey('components.headers.InvalidHeader.style');
        expect($e->getMessages()['components.headers.InvalidHeader.style'])
            ->toContain('style must be filled in.');
    });
});

it('accepts header with valid optional fields', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Valid Header API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'headers' => [
                'ValidHeader' => [
                    'description' => 'This is a valid header',
                    'required' => true,
                    'deprecated' => false,
                    'allowEmptyValue' => true,
                    'style' => 'simple',
                    'explode' => false,
                    'schema' => ['type' => 'string'],
                ],
            ],
        ],
    ]);

    expect($document->components->headers)->toHaveKey('ValidHeader');
    expect($document->components->headers['ValidHeader']->description)
        ->toBe('This is a valid header');
});

it('accepts header with minimal properties', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Minimal Header API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'headers' => [
                'MinimalHeader' => [
                    'schema' => ['type' => 'string'],
                ],
            ],
        ],
    ]);

    expect($document->components->headers)->toHaveKey('MinimalHeader');
    expect($document->components->headers['MinimalHeader']->schema->type)
        ->toBe('string');
    expect($document->components->headers['MinimalHeader']->required)
        ->toBeFalse(); // Default value
    expect($document->components->headers['MinimalHeader']->deprecated)
        ->toBeFalse(); // Default value
});

it('accepts header with no schema (content-based)', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Content Header API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'headers' => [
                'ContentHeader' => [
                    'description' => 'Header with content instead of schema',
                    'content' => [
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    expect($document->components->headers)->toHaveKey('ContentHeader');
    expect($document->components->headers['ContentHeader']->description)
        ->toBe('Header with content instead of schema');
    expect($document->components->headers['ContentHeader']->content)
        ->toBeArray();
    expect($document->components->headers['ContentHeader']->schema)
        ->toBeNull();
});
