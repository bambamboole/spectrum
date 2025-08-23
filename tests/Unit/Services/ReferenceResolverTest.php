<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ReferenceResolutionException;
use Bambamboole\OpenApi\ReferenceResolver;

it('can resolve simple component reference', function () {
    $document = [
        'components' => [
            'schemas' => [
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'integer'],
                        'name' => ['type' => 'string'],
                    ],
                ],
            ],
        ],
    ];

    $resolver = new ReferenceResolver($document);
    $resolved = $resolver->resolve('#/components/schemas/User');

    expect($resolved)->toBe([
        'type' => 'object',
        'properties' => [
            'id' => ['type' => 'integer'],
            'name' => ['type' => 'string'],
        ],
    ]);
});

it('can resolve nested reference paths', function () {
    $document = [
        'components' => [
            'schemas' => [
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'profile' => [
                            'type' => 'object',
                            'properties' => [
                                'email' => ['type' => 'string', 'format' => 'email'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $resolver = new ReferenceResolver($document);
    $resolved = $resolver->resolve('#/components/schemas/User/properties/profile');

    expect($resolved)->toBe([
        'type' => 'object',
        'properties' => [
            'email' => ['type' => 'string', 'format' => 'email'],
        ],
    ]);
});

it('throws exception for non-existent reference', function () {
    $document = [
        'components' => [
            'schemas' => [
                'User' => ['type' => 'object'],
            ],
        ],
    ];

    $resolver = new ReferenceResolver($document);

    expect(fn () => $resolver->resolve('#/components/schemas/NonExistent'))
        ->toThrow(ReferenceResolutionException::class, 'Reference path not found: #/components/schemas/NonExistent');
});

it('throws exception for external references', function () {
    $document = ['components' => ['schemas' => []]];
    $resolver = new ReferenceResolver($document);

    expect(fn () => $resolver->resolve('external.yaml#/components/schemas/User'))
        ->toThrow(ReferenceResolutionException::class, 'External references not supported');
});

it('caches resolved references for performance', function () {
    $document = [
        'components' => [
            'schemas' => [
                'User' => ['type' => 'object', 'properties' => ['id' => ['type' => 'integer']]],
            ],
        ],
    ];

    $resolver = new ReferenceResolver($document);

    // First resolution
    $resolved1 = $resolver->resolve('#/components/schemas/User');

    // Second resolution should return cached result
    $resolved2 = $resolver->resolve('#/components/schemas/User');

    expect($resolved1)->toBe($resolved2);
});

it('has circular reference detection mechanism', function () {
    $document = ['components' => ['schemas' => ['A' => ['type' => 'object']]]];
    $resolver = new ReferenceResolver($document);

    // Test that circular reference detection works by using reflection to simulate the stack
    $reflection = new ReflectionClass($resolver);
    $stackProperty = $reflection->getProperty('resolutionStack');
    $stackProperty->setAccessible(true);

    // Initially no circular reference
    expect($resolver->isCircularReference('#/components/schemas/A'))->toBeFalse();

    // Add to stack
    $stackProperty->setValue($resolver, ['#/components/schemas/A']);

    // Now it should detect circular reference
    expect($resolver->isCircularReference('#/components/schemas/A'))->toBeTrue();
    expect($resolver->isCircularReference('#/components/schemas/B'))->toBeFalse();
});

it('handles JSON Pointer escaping correctly', function () {
    $document = [
        'components' => [
            'schemas' => [
                'User/Name' => ['type' => 'string'], // Contains forward slash
                'User~Name' => ['type' => 'string'], // Contains tilde
            ],
        ],
    ];

    $resolver = new ReferenceResolver($document);

    // Forward slash should be escaped as ~1
    $resolved1 = $resolver->resolve('#/components/schemas/User~1Name');
    expect($resolved1)->toBe(['type' => 'string']);

    // Tilde should be escaped as ~0
    $resolved2 = $resolver->resolve('#/components/schemas/User~0Name');
    expect($resolved2)->toBe(['type' => 'string']);
});

it('can resolve root document reference', function () {
    $document = [
        'openapi' => '3.0.0',
        'info' => ['title' => 'Test API', 'version' => '1.0.0'],
    ];

    $resolver = new ReferenceResolver($document);
    $resolved = $resolver->resolve('#/info/title');

    expect($resolved)->toBe('Test API');
});
