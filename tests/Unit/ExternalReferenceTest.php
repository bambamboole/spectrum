<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ReferenceResolutionException;
use Bambamboole\OpenApi\OpenApiParser;
use Bambamboole\OpenApi\ReferenceResolver;

beforeEach(function () {
    ReferenceResolver::clear();
});

it('can resolve relative external file references', function () {
    $parser = OpenApiParser::make();
    $document = $parser->parseFile(__DIR__.'/../Fixtures/external/main.yaml');

    expect($document->paths['/users']->get->responses['200']->description)->toBe('User response');
    expect($document->paths['/users/{id}']->get->responses['200']->content['application/json']->schema->type)->toBe('object');
    expect($document->paths['/users/{id}']->get->responses['200']->content['application/json']->schema->properties['email']->format)->toBe('email');
});

it('can resolve JSON external file references', function () {
    $document = [
        'components' => [
            'responses' => [
                'UserResponse' => [
                    '$ref' => __DIR__.'/../Fixtures/external/responses.json#/UserResponse',
                ],
            ],
        ],
    ];

    ReferenceResolver::initialize($document, __DIR__.'/../Fixtures/external/main.yaml');

    $resolved = ReferenceResolver::resolveRef(['$ref' => __DIR__.'/../Fixtures/external/responses.json#/UserResponse']);

    expect($resolved['description'])->toBe('User response');
    expect($resolved['content']['application/json']['schema']['$ref'])->toBe('./schemas.yaml#/User');

    ReferenceResolver::clear();
});

it('can resolve YAML external file references', function () {
    $document = [];
    ReferenceResolver::initialize($document, __DIR__.'/../Fixtures/external/main.yaml');

    $resolved = ReferenceResolver::resolveRef(['$ref' => __DIR__.'/../Fixtures/external/schemas.yaml#/User']);

    expect($resolved['type'])->toBe('object');
    expect($resolved['properties']['email']['format'])->toBe('email');

    ReferenceResolver::clear();
});

it('caches external file contents', function () {
    $document = [];
    ReferenceResolver::initialize($document, __DIR__.'/../Fixtures/external/main.yaml');

    $resolved1 = ReferenceResolver::resolveRef(['$ref' => __DIR__.'/../Fixtures/external/schemas.yaml#/User']);
    $resolved2 = ReferenceResolver::resolveRef(['$ref' => __DIR__.'/../Fixtures/external/schemas.yaml#/Error']);

    expect($resolved1['type'])->toBe('object');
    expect($resolved2['type'])->toBe('object');
    expect($resolved2['required'])->toBe(['code', 'message']);

    ReferenceResolver::clear();
});

it('throws exception for non-existent external file', function () {
    $document = [];
    ReferenceResolver::initialize($document, __DIR__.'/../Fixtures/external/main.yaml');

    expect(fn () => ReferenceResolver::resolveRef(['$ref' => './non-existent.yaml#/Schema']))
        ->toThrow(ReferenceResolutionException::class, 'File not found');

    ReferenceResolver::clear();
});

it('can resolve absolute file paths', function () {
    $document = [];
    ReferenceResolver::initialize($document);

    $absolutePath = __DIR__.'/../Fixtures/external/schemas.yaml';
    $resolved = ReferenceResolver::resolveRef(['$ref' => $absolutePath.'#/User']);

    expect($resolved['type'])->toBe('object');
    expect($resolved['properties']['name']['type'])->toBe('string');

    ReferenceResolver::clear();
});

it('can resolve references to root of external file', function () {
    $document = [];
    ReferenceResolver::initialize($document, __DIR__.'/../Fixtures/external/main.yaml');

    $resolved = ReferenceResolver::resolveRef(['$ref' => './responses.json']);

    expect($resolved)->toHaveKey('UserResponse');
    expect($resolved)->toHaveKey('ErrorResponse');
    expect($resolved['UserResponse']['description'])->toBe('User response');

    ReferenceResolver::clear();
});
