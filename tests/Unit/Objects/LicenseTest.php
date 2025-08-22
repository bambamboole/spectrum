<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\License;

it('can create License with name only', function () {
    $license = License::fromArray([
        'name' => 'MIT',
    ]);

    expect($license->name)->toBe('MIT')
        ->and($license->url)->toBeNull();
});

it('can create License with name and url', function () {
    $license = License::fromArray([
        'name' => 'MIT',
        'url' => 'https://opensource.org/licenses/MIT',
    ]);

    expect($license->name)->toBe('MIT')
        ->and($license->url)->toBe('https://opensource.org/licenses/MIT');
});

it('throws exception when name is missing', function () {
    expect(fn () => License::fromArray([
        'url' => 'https://opensource.org/licenses/MIT',
    ]))
        ->toThrow(ParseException::class, 'Missing required field: name');
});

it('validates url format', function () {
    expect(fn () => License::fromArray([
        'name' => 'MIT',
        'url' => 'not-a-url',
    ]))
        ->toThrow(ParseException::class, 'url must be a valid URL');
});
