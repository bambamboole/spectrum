<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Objects\Schema;
use Bambamboole\OpenApi\OpenApiParser;

it('can resolve schema references in components', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Reference Test API',
            'version' => '1.0.0',
        ],
        'components' => [
            'schemas' => [
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                    ],
                    'required' => ['id', 'name'],
                ],
                'UserReference' => [
                    '$ref' => '#/components/schemas/User',
                ],
            ],
        ],
    ]));

    expect($document)->toBeInstanceOf(OpenApiDocument::class);

    // Check that the UserReference schema has been resolved to the actual User schema
    $userReferenceSchema = $document->components->schemas['UserReference'];
    $userSchema = $document->components->schemas['User'];

    expect($userReferenceSchema)->toBeInstanceOf(Schema::class);
    expect($userReferenceSchema->type)->toBe('object');
    expect($userReferenceSchema->properties)->toHaveKey('id');
    expect($userReferenceSchema->properties)->toHaveKey('name');
    expect($userReferenceSchema->required)->toBe(['id', 'name']);
});

it('can resolve nested schema references', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Nested Reference Test API',
            'version' => '1.0.0',
        ],
        'components' => [
            'schemas' => [
                'BasicUser' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                    ],
                ],
                'UserProfile' => [
                    'type' => 'object',
                    'properties' => [
                        'user' => ['$ref' => '#/components/schemas/BasicUser'],
                        'bio' => ['type' => 'string'],
                    ],
                ],
            ],
        ],
    ]));

    expect($document)->toBeInstanceOf(OpenApiDocument::class);

    $userProfileSchema = $document->components->schemas['UserProfile'];
    expect($userProfileSchema)->toBeInstanceOf(Schema::class);
    expect($userProfileSchema->type)->toBe('object');
    expect($userProfileSchema->properties)->toHaveKey('user');
    expect($userProfileSchema->properties)->toHaveKey('bio');

    // The 'user' property should be resolved to a Schema object
    $userProperty = $userProfileSchema->properties['user'];
    expect($userProperty)->toBeInstanceOf(Schema::class);
    expect($userProperty->type)->toBe('object');
    expect($userProperty->properties)->toHaveKey('id');
    expect($userProperty->properties)->toHaveKey('name');
});

it('can resolve references in array items', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Array Reference Test API',
            'version' => '1.0.0',
        ],
        'components' => [
            'schemas' => [
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                    ],
                ],
                'UserList' => [
                    'type' => 'array',
                    'items' => ['$ref' => '#/components/schemas/User'],
                ],
            ],
        ],
    ]));

    expect($document)->toBeInstanceOf(OpenApiDocument::class);

    $userListSchema = $document->components->schemas['UserList'];
    expect($userListSchema)->toBeInstanceOf(Schema::class);
    expect($userListSchema->type)->toBe('array');

    // The items should be resolved to a Schema object
    expect($userListSchema->items)->toBeInstanceOf(Schema::class);
    expect($userListSchema->items->type)->toBe('object');
    expect($userListSchema->items->properties)->toHaveKey('id');
    expect($userListSchema->items->properties)->toHaveKey('name');
});
