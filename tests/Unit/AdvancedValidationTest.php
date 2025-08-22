<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\OpenApiParser;

it('validates OpenAPI version format', function () {
    expect(fn () => OpenApiParser::fromArray([
        'openapi' => '2.0.0', // invalid version format
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
    ]))->toThrow(ParseException::class, 'format is invalid');
});

it('validates that maxLength is greater than or equal to minLength', function () {
    expect(fn () => OpenApiParser::fromArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'InvalidSchema' => [
                    'type' => 'string',
                    'minLength' => 10,
                    'maxLength' => 5, // invalid: max < min
                ],
            ],
        ],
    ]))->toThrow(ParseException::class, 'must be greater than or equal');
});

it('validates that maximum is greater than or equal to minimum', function () {
    expect(fn () => OpenApiParser::fromArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'InvalidSchema' => [
                    'type' => 'number',
                    'minimum' => 100,
                    'maximum' => 50, // invalid: max < min
                ],
            ],
        ],
    ]))->toThrow(ParseException::class, 'must be greater than or equal');
});

it('validates that required_with works for license name', function () {
    expect(fn () => OpenApiParser::fromArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
            'license' => [
                'url' => 'https://example.com/license', // missing required name
            ],
        ],
        'paths' => [],
    ]))->toThrow(ParseException::class, 'must be filled in if');
});

it('validates that empty arrays are rejected for composition keywords', function () {
    expect(fn () => OpenApiParser::fromArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'InvalidSchema' => [
                    'allOf' => [], // empty array not allowed
                ],
            ],
        ],
    ]))->toThrow(ParseException::class, 'must have at least 1 items');
});

it('validates that filled fields cannot be empty strings', function () {
    expect(fn () => OpenApiParser::fromArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => '', // empty string not allowed for filled fields
            'version' => '1.0.0',
        ],
        'paths' => [],
    ]))->toThrow(ParseException::class, 'is required');
});

it('validates external docs url is required when external docs object exists', function () {
    expect(fn () => OpenApiParser::fromArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'externalDocs' => [
            'description' => 'More info', // missing required url
        ],
    ]))->toThrow(ParseException::class, 'must be filled in if');
});

it('allows valid OpenAPI versions', function () {
    $validVersions = ['3.0.0', '3.0.1', '3.0.2', '3.0.3', '3.1.0', '3.1.1'];

    foreach ($validVersions as $version) {
        $document = OpenApiParser::fromArray([
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

it('validates multipleOf is greater than 0', function () {
    expect(fn () => OpenApiParser::fromArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'InvalidSchema' => [
                    'type' => 'number',
                    'multipleOf' => 0, // must be > 0
                ],
            ],
        ],
    ]))->toThrow(ParseException::class, 'must be greater than 0');
});

it('demonstrates sophisticated validation working together', function () {
    $document = OpenApiParser::fromArray([
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
