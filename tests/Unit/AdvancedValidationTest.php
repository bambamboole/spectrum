<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\OpenApiParser;

it('validates OpenAPI version format', function () {
    try {
        OpenApiParser::make()->parseArray([
            'openapi' => '2.0.0', // invalid version format
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
            ],
            'paths' => [],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('openapi')
            ->and($e->getMessages()['openapi'])->toContain('The openapi must be at least version 3.0.0.');
    }
});

it('validates that required_with works for license name', function () {
    try {
        OpenApiParser::make()->parseArray([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
                'license' => [
                    'url' => 'https://example.com/license', // missing required name
                ],
            ],
            'paths' => [],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('info.license.name')
            ->and($e->getMessages()['info.license.name'])->toContain('name is required.');
    }
});

it('validates that filled fields cannot be empty strings', function () {
    try {
        OpenApiParser::make()->parseArray([
            'openapi' => '3.0.0',
            'info' => [
                'title' => '', // empty string not allowed for filled fields
                'version' => '1.0.0',
            ],
            'paths' => [],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('info.title')
            ->and($e->getMessages()['info.title'])->toContain('title is required.');
    }
});

it('allows valid OpenAPI versions', function () {
    $validVersions = ['3.0.0', '3.0.1', '3.0.2', '3.0.3', '3.1.0', '3.1.1'];

    foreach ($validVersions as $version) {
        $document = OpenApiParser::make()->parseArray([
            'openapi' => $version,
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
            ],
            'paths' => [],
        ]);

        expect($document->openapi)->toBe($version);
    }
});

it('demonstrates sophisticated validation working together', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Valid API',
            'version' => '1.0.0',
            'description' => 'A properly validated API',
            'termsOfService' => 'https://example.com/terms',
            'contact' => [
                'name' => 'Support Team',
                'email' => 'support@example.com',
                'url' => 'https://example.com/support',
            ],
            'license' => [
                'name' => 'MIT',
                'url' => 'https://opensource.org/licenses/MIT',
            ],
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'ValidSchema' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 100,
                    'pattern' => '^[a-zA-Z]+$',
                ],
                'ValidNumber' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'maximum' => 999.99,
                    'multipleOf' => 0.01,
                ],
            ],
        ],
        'servers' => [
            [
                'url' => 'https://api.example.com',
                'description' => 'Production server',
            ],
        ],
        'externalDocs' => [
            'description' => 'Find more info here',
            'url' => 'https://example.com/docs',
        ],
    ]);

    expect($document->info->title)->toBe('Valid API')
        ->and($document->info->contact->email)->toBe('support@example.com')
        ->and($document->components->schemas['ValidSchema']->maxLength)->toBe(100)
        ->and($document->components->schemas['ValidNumber']->multipleOf)->toBe(0.01);
});
