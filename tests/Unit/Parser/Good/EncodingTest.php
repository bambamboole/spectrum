<?php declare(strict_types=1);

use App\Objects\OpenApiDocument;
use App\OpenApiParser;

it('can parse media type with encoding objects', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Encoding',
            'version' => '1.0.0',
        ],
        'paths' => [
            '/upload' => [
                'post' => [
                    'requestBody' => [
                        'content' => [
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'file' => [
                                            'type' => 'string',
                                            'format' => 'binary',
                                        ],
                                        'metadata' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'name' => ['type' => 'string'],
                                                'description' => ['type' => 'string'],
                                            ],
                                        ],
                                        'tags' => [
                                            'type' => 'array',
                                            'items' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                                'encoding' => [
                                    'file' => [
                                        'contentType' => 'image/png, image/jpeg',
                                        'headers' => [
                                            'X-Custom-Header' => [
                                                'description' => 'Custom header for file upload',
                                                'schema' => [
                                                    'type' => 'string',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'metadata' => [
                                        'contentType' => 'application/json',
                                        'style' => 'form',
                                        'explode' => false,
                                    ],
                                    'tags' => [
                                        'style' => 'form',
                                        'explode' => true,
                                        'allowReserved' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Success',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    expect($document)->toBeInstanceOf(OpenApiDocument::class);

    $mediaType = $document->paths['/upload']->post->requestBody->content['multipart/form-data'];
    expect($mediaType->encoding)->toHaveCount(3);
    expect($mediaType->encoding)->toHaveKey('file');
    expect($mediaType->encoding)->toHaveKey('metadata');
    expect($mediaType->encoding)->toHaveKey('tags');

    // Test file encoding
    $fileEncoding = $mediaType->encoding['file'];
    expect($fileEncoding->contentType)->toBe('image/png, image/jpeg');
    expect($fileEncoding->headers)->toHaveKey('X-Custom-Header');
    expect($fileEncoding->getHeader('X-Custom-Header'))->not->toBeNull();
    expect($fileEncoding->getHeader('X-Custom-Header')->description)->toBe('Custom header for file upload');
    expect($fileEncoding->hasHeader('X-Custom-Header'))->toBeTrue();
    expect($fileEncoding->hasHeader('nonexistent'))->toBeFalse();
    expect($fileEncoding->style)->toBeNull();
    expect($fileEncoding->explode)->toBeNull();
    expect($fileEncoding->getEffectiveExplode())->toBeFalse(); // Default for non-form style
    expect($fileEncoding->allowsReservedCharacters())->toBeFalse();

    // Test metadata encoding
    $metadataEncoding = $mediaType->encoding['metadata'];
    expect($metadataEncoding->contentType)->toBe('application/json');
    expect($metadataEncoding->style)->toBe('form');
    expect($metadataEncoding->explode)->toBeFalse();
    expect($metadataEncoding->getEffectiveExplode())->toBeFalse(); // Explicitly set to false
    expect($metadataEncoding->allowReserved)->toBeNull(); // Not set in test data

    // Test tags encoding
    $tagsEncoding = $mediaType->encoding['tags'];
    expect($tagsEncoding->style)->toBe('form');
    expect($tagsEncoding->explode)->toBeTrue();
    expect($tagsEncoding->getEffectiveExplode())->toBeTrue(); // Explicitly set to true
    expect($tagsEncoding->allowReserved)->toBeTrue();
    expect($tagsEncoding->allowsReservedCharacters())->toBeTrue();
});

it('can parse encoding with form style default explode behavior', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Form Encoding',
            'version' => '1.0.0',
        ],
        'paths' => [
            '/form' => [
                'post' => [
                    'requestBody' => [
                        'content' => [
                            'application/x-www-form-urlencoded' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'formField' => ['type' => 'string'],
                                    ],
                                ],
                                'encoding' => [
                                    'formField' => [
                                        'style' => 'form',
                                        // explode not specified, should default to true for form style
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Success',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $mediaType = $document->paths['/form']->post->requestBody->content['application/x-www-form-urlencoded'];
    $formEncoding = $mediaType->encoding['formField'];

    expect($formEncoding->style)->toBe('form');
    expect($formEncoding->explode)->toBeNull(); // Not explicitly set
    expect($formEncoding->getEffectiveExplode())->toBeTrue(); // Default to true for form style
});

it('can parse encoding with non-form style default explode behavior', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Non-Form Encoding',
            'version' => '1.0.0',
        ],
        'paths' => [
            '/pipe' => [
                'post' => [
                    'requestBody' => [
                        'content' => [
                            'application/x-www-form-urlencoded' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'pipeField' => ['type' => 'array', 'items' => ['type' => 'string']],
                                    ],
                                ],
                                'encoding' => [
                                    'pipeField' => [
                                        'style' => 'pipeDelimited',
                                        // explode not specified, should default to false for non-form styles
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Success',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $mediaType = $document->paths['/pipe']->post->requestBody->content['application/x-www-form-urlencoded'];
    $pipeEncoding = $mediaType->encoding['pipeField'];

    expect($pipeEncoding->style)->toBe('pipeDelimited');
    expect($pipeEncoding->explode)->toBeNull(); // Not explicitly set
    expect($pipeEncoding->getEffectiveExplode())->toBeFalse(); // Default to false for non-form styles
});

it('can parse minimal encoding object', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Minimal Encoding',
            'version' => '1.0.0',
        ],
        'paths' => [
            '/minimal' => [
                'post' => [
                    'requestBody' => [
                        'content' => [
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'field' => ['type' => 'string'],
                                    ],
                                ],
                                'encoding' => [
                                    'field' => [], // Empty encoding object
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Success',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $mediaType = $document->paths['/minimal']->post->requestBody->content['multipart/form-data'];
    $fieldEncoding = $mediaType->encoding['field'];

    expect($fieldEncoding->contentType)->toBeNull();
    expect($fieldEncoding->headers)->toBeNull();
    expect($fieldEncoding->style)->toBeNull();
    expect($fieldEncoding->explode)->toBeNull();
    expect($fieldEncoding->allowReserved)->toBeNull();
    expect($fieldEncoding->getEffectiveExplode())->toBeFalse(); // Default when style is null
});

it('can parse encoding with extension properties', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Extended Encoding',
            'version' => '1.0.0',
        ],
        'paths' => [
            '/extended' => [
                'post' => [
                    'requestBody' => [
                        'content' => [
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'extendedField' => ['type' => 'string'],
                                    ],
                                ],
                                'encoding' => [
                                    'extendedField' => [
                                        'contentType' => 'text/plain',
                                        'x-custom-encoding' => 'custom-value',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Success',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $mediaType = $document->paths['/extended']->post->requestBody->content['multipart/form-data'];
    $extendedEncoding = $mediaType->encoding['extendedField'];

    expect($extendedEncoding->x)->toHaveKey('x-custom-encoding');
    expect($extendedEncoding->x['x-custom-encoding'])->toBe('custom-value');
});
