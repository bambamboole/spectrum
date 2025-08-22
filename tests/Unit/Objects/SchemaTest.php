<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\Schema;

it('can create string schema', function () {
    $schema = Schema::fromArray([
        'type' => 'string',
    ]);

    expect($schema->type)->toBe('string')
        ->and($schema->format)->toBeNull()
        ->and($schema->minLength)->toBeNull()
        ->and($schema->maxLength)->toBeNull();
});

it('can create string schema with constraints', function () {
    $schema = Schema::fromArray([
        'type' => 'string',
        'format' => 'email',
        'minLength' => 5,
        'maxLength' => 100,
        'pattern' => '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$',
    ]);

    expect($schema->type)->toBe('string')
        ->and($schema->format)->toBe('email')
        ->and($schema->minLength)->toBe(5)
        ->and($schema->maxLength)->toBe(100)
        ->and($schema->pattern)->toBe('^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$');
});

it('can create integer schema', function () {
    $schema = Schema::fromArray([
        'type' => 'integer',
    ]);

    expect($schema->type)->toBe('integer')
        ->and($schema->format)->toBeNull()
        ->and($schema->minimum)->toBeNull()
        ->and($schema->maximum)->toBeNull();
});

it('can create integer schema with constraints', function () {
    $schema = Schema::fromArray([
        'type' => 'integer',
        'format' => 'int64',
        'minimum' => 1,
        'maximum' => 1000,
        'exclusiveMinimum' => true,
        'exclusiveMaximum' => false,
    ]);

    expect($schema->type)->toBe('integer')
        ->and($schema->format)->toBe('int64')
        ->and($schema->minimum)->toBe(1)
        ->and($schema->maximum)->toBe(1000)
        ->and($schema->exclusiveMinimum)->toBe(true)
        ->and($schema->exclusiveMaximum)->toBe(false);
});

it('can create number schema', function () {
    $schema = Schema::fromArray([
        'type' => 'number',
        'minimum' => 0.5,
        'maximum' => 99.9,
    ]);

    expect($schema->type)->toBe('number')
        ->and($schema->minimum)->toBe(0.5)
        ->and($schema->maximum)->toBe(99.9);
});

it('can create boolean schema', function () {
    $schema = Schema::fromArray([
        'type' => 'boolean',
    ]);

    expect($schema->type)->toBe('boolean');
});

it('can create array schema', function () {
    $schema = Schema::fromArray([
        'type' => 'array',
        'items' => [
            'type' => 'string',
        ],
        'minItems' => 1,
        'maxItems' => 10,
        'uniqueItems' => true,
    ]);

    expect($schema->type)->toBe('array')
        ->and($schema->items)->toBeInstanceOf(Schema::class)
        ->and($schema->minItems)->toBe(1)
        ->and($schema->maxItems)->toBe(10)
        ->and($schema->uniqueItems)->toBe(true);
});

it('can create object schema', function () {
    $schema = Schema::fromArray([
        'type' => 'object',
        'properties' => [
            'name' => [
                'type' => 'string',
            ],
            'age' => [
                'type' => 'integer',
                'minimum' => 0,
            ],
        ],
        'required' => ['name'],
        'additionalProperties' => false,
    ]);

    expect($schema->type)->toBe('object')
        ->and($schema->properties)->toHaveCount(2)
        ->and($schema->properties['name'])->toBeInstanceOf(Schema::class)
        ->and($schema->properties['age'])->toBeInstanceOf(Schema::class)
        ->and($schema->required)->toBe(['name'])
        ->and($schema->additionalProperties)->toBe(false);
});

it('validates invalid type', function () {
    expect(fn () => Schema::fromArray([
        'type' => 'invalid-type',
    ]))
        ->toThrow(ParseException::class, 'Invalid schema type: invalid-type');
});

it('validates minLength constraint', function () {
    expect(fn () => Schema::fromArray([
        'type' => 'string',
        'minLength' => -1,
    ]))
        ->toThrow(ParseException::class, 'minLength must be >= 0');
});

it('validates maxLength constraint', function () {
    expect(fn () => Schema::fromArray([
        'type' => 'string',
        'maxLength' => -1,
    ]))
        ->toThrow(ParseException::class, 'maxLength must be >= 0');
});
