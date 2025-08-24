<?php declare(strict_types=1);

use App\Objects\OpenApiDocument;
use App\OpenApiParser;

it('can parse schema with discriminator', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Discriminator',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'Pet' => [
                    'type' => 'object',
                    'discriminator' => [
                        'propertyName' => 'petType',
                        'mapping' => [
                            'dog' => '#/components/schemas/Dog',
                            'cat' => '#/components/schemas/Cat',
                            'bird' => 'Bird.yaml#/Bird',
                        ],
                    ],
                    'required' => ['name', 'petType'],
                    'properties' => [
                        'name' => [
                            'type' => 'string',
                        ],
                        'petType' => [
                            'type' => 'string',
                        ],
                    ],
                ],
                'Dog' => [
                    'allOf' => [
                        ['$ref' => '#/components/schemas/Pet'],
                        [
                            'type' => 'object',
                            'properties' => [
                                'breed' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
                'Cat' => [
                    'allOf' => [
                        ['$ref' => '#/components/schemas/Pet'],
                        [
                            'type' => 'object',
                            'properties' => [
                                'declawed' => [
                                    'type' => 'boolean',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]));

    expect($document)->toBeInstanceOf(OpenApiDocument::class);

    $petSchema = $document->components->schemas['Pet'];
    expect($petSchema->discriminator)->not->toBeNull();
    expect($petSchema->discriminator->propertyName)->toBe('petType');
    expect($petSchema->discriminator->mapping)->toHaveCount(3);
    expect($petSchema->discriminator->getSchemaForValue('dog'))->toBe('#/components/schemas/Dog');
    expect($petSchema->discriminator->getSchemaForValue('cat'))->toBe('#/components/schemas/Cat');
    expect($petSchema->discriminator->getSchemaForValue('bird'))->toBe('Bird.yaml#/Bird');
    expect($petSchema->discriminator->getSchemaForValue('nonexistent'))->toBeNull();

    expect($petSchema->discriminator->hasMappingForValue('dog'))->toBeTrue();
    expect($petSchema->discriminator->hasMappingForValue('cat'))->toBeTrue();
    expect($petSchema->discriminator->hasMappingForValue('bird'))->toBeTrue();
    expect($petSchema->discriminator->hasMappingForValue('nonexistent'))->toBeFalse();

    expect($petSchema->discriminator->getMappedValues())->toBe(['dog', 'cat', 'bird']);
});

it('can parse schema with discriminator without mapping', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Simple Discriminator',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'Shape' => [
                    'type' => 'object',
                    'discriminator' => [
                        'propertyName' => 'shapeType',
                    ],
                    'required' => ['shapeType'],
                    'properties' => [
                        'shapeType' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $shapeSchema = $document->components->schemas['Shape'];
    expect($shapeSchema->discriminator)->not->toBeNull();
    expect($shapeSchema->discriminator->propertyName)->toBe('shapeType');
    expect($shapeSchema->discriminator->mapping)->toBeNull();
    expect($shapeSchema->discriminator->getMappedValues())->toBe([]);
    expect($shapeSchema->discriminator->hasMappingForValue('circle'))->toBeFalse();
});

it('can parse schema with discriminator and extension properties', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Extended Discriminator',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'Vehicle' => [
                    'type' => 'object',
                    'discriminator' => [
                        'propertyName' => 'vehicleType',
                        'mapping' => [
                            'car' => '#/components/schemas/Car',
                        ],
                        'x-custom-discriminator' => 'custom-value',
                    ],
                    'properties' => [
                        'vehicleType' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $vehicleSchema = $document->components->schemas['Vehicle'];
    expect($vehicleSchema->discriminator->x)->toHaveKey('x-custom-discriminator');
    expect($vehicleSchema->discriminator->x['x-custom-discriminator'])->toBe('custom-value');
});
