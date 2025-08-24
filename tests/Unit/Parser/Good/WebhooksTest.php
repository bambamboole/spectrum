<?php declare(strict_types=1);

use App\Objects\OpenApiDocument;
use App\OpenApiParser;

it('can parse OpenAPI document with webhooks', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Webhooks',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'webhooks' => [
            'newPet' => [
                'summary' => 'New pet webhook',
                'description' => 'Information about a new pet in the store',
                'post' => [
                    'requestBody' => [
                        'description' => 'Information about a new pet in the store',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => [
                                            'type' => 'integer',
                                            'format' => 'int64',
                                        ],
                                        'name' => [
                                            'type' => 'string',
                                        ],
                                        'status' => [
                                            'type' => 'string',
                                            'enum' => ['available', 'pending', 'sold'],
                                        ],
                                    ],
                                    'required' => ['name'],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'OK',
                        ],
                    ],
                ],
            ],
            'petUpdated' => [
                'description' => 'Pet information updated',
                'put' => [
                    'responses' => [
                        '200' => [
                            'description' => 'Pet updated successfully',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    expect($document)->toBeInstanceOf(OpenApiDocument::class);
    expect($document->webhooks)->not->toBeNull();
    expect($document->webhooks->webhooks)->toHaveCount(2);
    expect($document->webhooks->hasWebhook('newPet'))->toBeTrue();
    expect($document->webhooks->hasWebhook('petUpdated'))->toBeTrue();
    expect($document->webhooks->hasWebhook('nonexistent'))->toBeFalse();

    $newPetWebhook = $document->webhooks->getWebhook('newPet');
    expect($newPetWebhook)->not->toBeNull();
    expect($newPetWebhook->summary)->toBe('New pet webhook');
    expect($newPetWebhook->description)->toBe('Information about a new pet in the store');
    expect($newPetWebhook->post)->not->toBeNull();
    expect($newPetWebhook->post->requestBody)->not->toBeNull();

    $petUpdatedWebhook = $document->webhooks->getWebhook('petUpdated');
    expect($petUpdatedWebhook)->not->toBeNull();
    expect($petUpdatedWebhook->description)->toBe('Pet information updated');
    expect($petUpdatedWebhook->put)->not->toBeNull();

    expect($document->webhooks->getWebhookNames())->toContain('newPet');
    expect($document->webhooks->getWebhookNames())->toContain('petUpdated');
});

it('can parse OpenAPI document without webhooks', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.0.3',
        'info' => [
            'title' => 'API without Webhooks',
            'version' => '1.0.0',
        ],
        'paths' => [],
    ]));

    expect($document)->toBeInstanceOf(OpenApiDocument::class);
    expect($document->webhooks)->toBeNull();
});

it('can parse webhooks with extension properties', function () {
    $document = OpenApiParser::make()->parseArray($this->schema([
        'openapi' => '3.1.0',
        'info' => [
            'title' => 'API with Extended Webhooks',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'webhooks' => [
            'customWebhook' => [
                'post' => [
                    'responses' => [
                        '200' => [
                            'description' => 'OK',
                        ],
                    ],
                ],
                'x-custom-property' => 'custom-value',
            ],
            'x-webhook-extension' => 'webhook-level-extension',
        ],
    ]));

    expect($document->webhooks->webhooks)->toHaveKey('customWebhook');
    expect($document->webhooks->getWebhook('customWebhook')->x)->toHaveKey('x-custom-property');
    expect($document->webhooks->getWebhook('customWebhook')->x['x-custom-property'])->toBe('custom-value');
    expect($document->webhooks->x)->toHaveKey('x-webhook-extension');
    expect($document->webhooks->x['x-webhook-extension'])->toBe('webhook-level-extension');
});
