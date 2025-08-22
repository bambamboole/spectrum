<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\Contact;
use Bambamboole\OpenApi\Objects\Info;
use Bambamboole\OpenApi\Objects\License;

it('can create Info with required fields only', function () {
    $info = Info::fromArray([
        'title' => 'Test API',
        'version' => '1.0.0',
    ]);

    expect($info->title)->toBe('Test API')
        ->and($info->version)->toBe('1.0.0')
        ->and($info->description)->toBeNull()
        ->and($info->termsOfService)->toBeNull()
        ->and($info->contact)->toBeNull()
        ->and($info->license)->toBeNull();
});

it('can create Info with all fields', function () {
    $info = Info::fromArray([
        'title' => 'Test API',
        'version' => '1.0.0',
        'description' => 'A test API',
        'termsOfService' => 'https://example.com/terms',
        'contact' => [
            'name' => 'API Support',
            'email' => 'support@example.com',
            'url' => 'https://example.com/support',
        ],
        'license' => [
            'name' => 'MIT',
            'url' => 'https://opensource.org/licenses/MIT',
        ],
    ]);

    expect($info->title)->toBe('Test API')
        ->and($info->version)->toBe('1.0.0')
        ->and($info->description)->toBe('A test API')
        ->and($info->termsOfService)->toBe('https://example.com/terms')
        ->and($info->contact)->toBeInstanceOf(Contact::class)
        ->and($info->license)->toBeInstanceOf(License::class);
});

it('throws exception when title is missing', function () {
    expect(fn () => Info::fromArray([
        'version' => '1.0.0',
    ]))
        ->toThrow(ParseException::class, 'Missing required field: title');
});

it('throws exception when version is missing', function () {
    expect(fn () => Info::fromArray([
        'title' => 'Test API',
    ]))
        ->toThrow(ParseException::class, 'Missing required field: version');
});

it('validates termsOfService is a URL', function () {
    expect(fn () => Info::fromArray([
        'title' => 'Test API',
        'version' => '1.0.0',
        'termsOfService' => 'not-a-url',
    ]))
        ->toThrow(ParseException::class, 'termsOfService must be a valid URL');
});
