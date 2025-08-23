<?php declare(strict_types=1);

use Bambamboole\OpenApi\Context\ParsingContext;
use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Factories\ComponentsFactory;

it('rejects example with empty summary', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createExample([
            'summary' => '',
            'value' => 'test',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('summary');
        expect($e->getMessages()['summary'])->toContain('summary must be filled in.');
    }
});

it('rejects example with empty description', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createExample([
            'description' => '',
            'value' => 'test',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('description');
        expect($e->getMessages()['description'])->toContain('description must be filled in.');
    }
});

it('rejects example with non-string summary', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createExample([
            'summary' => 123,
            'value' => 'test',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('summary');
        expect($e->getMessages()['summary'])->toContain('summary must be a string.');
    }
});

it('rejects example with non-string description', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createExample([
            'description' => ['not', 'a', 'string'],
            'value' => 'test',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('description');
        expect($e->getMessages()['description'])->toContain('description must be a string.');
    }
});

it('rejects example with invalid externalValue URL', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createExample([
            'externalValue' => 'not-a-valid-url',
            'summary' => 'External example',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('externalValue');
        expect($e->getMessages()['externalValue'])->toContain('external value must be a URL.');
    }
});

it('rejects example with non-string externalValue', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    try {
        $factory->createExample([
            'externalValue' => 12345,
            'summary' => 'External example',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('externalValue');
        expect($e->getMessages()['externalValue'])->toContain('external value must be a URL.');
    }
});

it('accepts example with valid externalValue URL', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $example = $factory->createExample([
        'externalValue' => 'https://example.com/user.json',
        'summary' => 'External user example',
        'description' => 'User data from external source',
    ]);

    expect($example->externalValue)->toBe('https://example.com/user.json');
    expect($example->summary)->toBe('External user example');
    expect($example->description)->toBe('User data from external source');
    expect($example->value)->toBeNull();
});

it('accepts example with different valid URL schemes', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $examples = [
        $factory->createExample(['externalValue' => 'https://example.com/data.json']),
        $factory->createExample(['externalValue' => 'http://example.com/data.json']),
        $factory->createExample(['externalValue' => 'ftp://example.com/data.json']),
    ];

    expect($examples[0]->externalValue)->toBe('https://example.com/data.json');
    expect($examples[1]->externalValue)->toBe('http://example.com/data.json');
    expect($examples[2]->externalValue)->toBe('ftp://example.com/data.json');
});

it('accepts example with all optional fields valid', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $example = $factory->createExample([
        'summary' => 'Complete example',
        'description' => 'A comprehensive example with all fields',
        'value' => [
            'id' => 1,
            'name' => 'Test User',
            'active' => true,
        ],
    ]);

    expect($example->summary)->toBe('Complete example');
    expect($example->description)->toBe('A comprehensive example with all fields');
    expect($example->value)->toBeArray();
    expect($example->value)->toHaveKey('id');
    expect($example->externalValue)->toBeNull();
});

it('accepts example with no fields (minimal valid example)', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $example = $factory->createExample([]);

    expect($example->summary)->toBeNull();
    expect($example->description)->toBeNull();
    expect($example->value)->toBeNull();
    expect($example->externalValue)->toBeNull();
});
