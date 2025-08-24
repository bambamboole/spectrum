<?php declare(strict_types=1);

use App\Objects\OpenApiDocument;
use App\Objects\Parameter;
use App\OpenApiParser;

it('can parse basic parameter in components', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Parameter Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'parameters' => [
                'LimitParam' => [
                    'name' => 'limit',
                    'in' => 'query',
                    'description' => 'Number of items to return',
                    'required' => false,
                    'schema' => [
                        'type' => 'integer',
                        'minimum' => 1,
                        'maximum' => 100,
                        'default' => 20,
                    ],
                ],
            ],
        ],
    ]);

    expect($document)->toBeInstanceOf(OpenApiDocument::class);
    expect($document->components->parameters)->toHaveKey('LimitParam');

    $limitParam = $document->components->parameters['LimitParam'];
    expect($limitParam)->toBeInstanceOf(Parameter::class);
    expect($limitParam->name)->toBe('limit');
    expect($limitParam->in)->toBe('query');
    expect($limitParam->description)->toBe('Number of items to return');
    expect($limitParam->required)->toBeFalse();
    expect($limitParam->schema->type)->toBe('integer');
    expect($limitParam->schema->minimum)->toBe(1);
    expect($limitParam->schema->maximum)->toBe(100);
    expect($limitParam->schema->default)->toBe(20);
});

it('can parse parameter with different locations', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Parameter Locations Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'parameters' => [
                'QueryParam' => [
                    'name' => 'filter',
                    'in' => 'query',
                    'schema' => ['type' => 'string'],
                ],
                'HeaderParam' => [
                    'name' => 'X-Request-ID',
                    'in' => 'header',
                    'required' => true,
                    'schema' => ['type' => 'string'],
                ],
                'PathParam' => [
                    'name' => 'id',
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'integer'],
                ],
                'CookieParam' => [
                    'name' => 'session',
                    'in' => 'cookie',
                    'schema' => ['type' => 'string'],
                ],
            ],
        ],
    ]);

    expect($document->components->parameters)->toHaveCount(4);

    expect($document->components->parameters['QueryParam']->in)->toBe('query');
    expect($document->components->parameters['HeaderParam']->in)->toBe('header');
    expect($document->components->parameters['HeaderParam']->required)->toBeTrue();
    expect($document->components->parameters['PathParam']->in)->toBe('path');
    expect($document->components->parameters['PathParam']->required)->toBeTrue();
    expect($document->components->parameters['CookieParam']->in)->toBe('cookie');
});

it('can parse parameter references', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Parameter Reference Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'parameters' => [
                'BaseParam' => [
                    'name' => 'base',
                    'in' => 'query',
                    'description' => 'Base parameter',
                    'schema' => ['type' => 'string'],
                ],
                'RefParam' => [
                    '$ref' => '#/components/parameters/BaseParam',
                ],
            ],
        ],
    ]);

    expect($document->components->parameters)->toHaveCount(2);

    $baseParam = $document->components->parameters['BaseParam'];
    $refParam = $document->components->parameters['RefParam'];

    expect($baseParam->name)->toBe('base');
    expect($baseParam->description)->toBe('Base parameter');

    // The referenced parameter should be resolved
    expect($refParam->name)->toBe('base');
    expect($refParam->description)->toBe('Base parameter');
    expect($refParam->in)->toBe('query');
});

it('can parse parameter with advanced properties', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Advanced Parameter Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'parameters' => [
                'AdvancedParam' => [
                    'name' => 'tags',
                    'in' => 'query',
                    'description' => 'Tags to filter by',
                    'required' => false,
                    'deprecated' => false,
                    'allowEmptyValue' => true,
                    'style' => 'form',
                    'explode' => true,
                    'allowReserved' => false,
                    'schema' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                    ],
                    'example' => ['tag1', 'tag2'],
                ],
            ],
        ],
    ]);

    $param = $document->components->parameters['AdvancedParam'];

    expect($param->name)->toBe('tags');
    expect($param->in)->toBe('query');
    expect($param->description)->toBe('Tags to filter by');
    expect($param->required)->toBeFalse();
    expect($param->deprecated)->toBeFalse();
    expect($param->allowEmptyValue)->toBeTrue();
    expect($param->style)->toBe('form');
    expect($param->explode)->toBeTrue();
    expect($param->allowReserved)->toBeFalse();
    expect($param->schema->type)->toBe('array');
    expect($param->example)->toBe(['tag1', 'tag2']);
});
