<?php declare(strict_types=1);

use App\Objects\OpenApiDocument;
use App\OpenApiParser;

it('can parse schema with XML metadata', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with XML Schema',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'User' => [
                    'type' => 'object',
                    'xml' => [
                        'name' => 'user',
                        'namespace' => 'http://example.com/schema/user',
                        'prefix' => 'usr',
                    ],
                    'properties' => [
                        'id' => [
                            'type' => 'integer',
                            'xml' => [
                                'attribute' => true,
                            ],
                        ],
                        'name' => [
                            'type' => 'string',
                            'xml' => [
                                'name' => 'fullName',
                            ],
                        ],
                        'tags' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                                'xml' => [
                                    'name' => 'tag',
                                ],
                            ],
                            'xml' => [
                                'wrapped' => true,
                                'name' => 'tagList',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]));

    expect($document)->toBeInstanceOf(OpenApiDocument::class);

    $userSchema = $document->components->schemas['User'];
    expect($userSchema->xml)->not->toBeNull();
    expect($userSchema->xml->name)->toBe('user');
    expect($userSchema->xml->namespace)->toBe('http://example.com/schema/user');
    expect($userSchema->xml->prefix)->toBe('usr');
    expect($userSchema->xml->getQualifiedName())->toBe('usr:user');
    expect($userSchema->xml->isAttribute())->toBeFalse();
    expect($userSchema->xml->isWrapped())->toBeFalse();

    $idProperty = $userSchema->properties['id'];
    expect($idProperty->xml)->not->toBeNull();
    expect($idProperty->xml->isAttribute())->toBeTrue();
    expect($idProperty->xml->attribute)->toBeTrue();

    $nameProperty = $userSchema->properties['name'];
    expect($nameProperty->xml)->not->toBeNull();
    expect($nameProperty->xml->name)->toBe('fullName');
    expect($nameProperty->xml->getQualifiedName())->toBe('fullName');

    $tagsProperty = $userSchema->properties['tags'];
    expect($tagsProperty->xml)->not->toBeNull();
    expect($tagsProperty->xml->wrapped)->toBeTrue();
    expect($tagsProperty->xml->isWrapped())->toBeTrue();
    expect($tagsProperty->xml->name)->toBe('tagList');

    $tagItemSchema = $tagsProperty->items;
    expect($tagItemSchema->xml)->not->toBeNull();
    expect($tagItemSchema->xml->name)->toBe('tag');
});

it('can parse schema with minimal XML metadata', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Minimal XML Schema',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'SimpleObject' => [
                    'type' => 'object',
                    'xml' => [
                        'name' => 'simple',
                    ],
                    'properties' => [
                        'value' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $simpleSchema = $document->components->schemas['SimpleObject'];
    expect($simpleSchema->xml)->not->toBeNull();
    expect($simpleSchema->xml->name)->toBe('simple');
    expect($simpleSchema->xml->namespace)->toBeNull();
    expect($simpleSchema->xml->prefix)->toBeNull();
    expect($simpleSchema->xml->attribute)->toBeNull();
    expect($simpleSchema->xml->wrapped)->toBeNull();
    expect($simpleSchema->xml->getQualifiedName())->toBe('simple');
});

it('can parse schema with XML prefix but no namespace', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with XML Prefix',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'PrefixedObject' => [
                    'type' => 'object',
                    'xml' => [
                        'name' => 'item',
                        'prefix' => 'pre',
                    ],
                ],
            ],
        ],
    ]));

    $prefixedSchema = $document->components->schemas['PrefixedObject'];
    expect($prefixedSchema->xml->getQualifiedName())->toBe('pre:item');
});

it('can parse schema with XML extension properties', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Extended XML',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'ExtendedXML' => [
                    'type' => 'object',
                    'xml' => [
                        'name' => 'extended',
                        'x-custom-xml' => 'xml-custom-value',
                    ],
                ],
            ],
        ],
    ]));

    $extendedSchema = $document->components->schemas['ExtendedXML'];
    expect($extendedSchema->xml->x)->toHaveKey('x-custom-xml');
    expect($extendedSchema->xml->x['x-custom-xml'])->toBe('xml-custom-value');
});

it('returns null qualified name when name is not set', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with XML without name',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'NoNameXML' => [
                    'type' => 'object',
                    'xml' => [
                        'attribute' => true,
                    ],
                ],
            ],
        ],
    ]));

    $schema = $document->components->schemas['NoNameXML'];
    expect($schema->xml->getQualifiedName())->toBeNull();
});
