<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\Operation;

it('rejects operation missing required responses', function () {
    try {
        Operation::fromArray([
            'summary' => 'Test operation',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('responses');
        expect($e->getMessages()['responses'])->toContain('responses is required.');
    }
});

it('rejects operation with empty responses', function () {
    try {
        Operation::fromArray([
            'responses' => [],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('responses');
        expect($e->getMessages()['responses'])->toContain('responses is required.');
    }
});

it('rejects operation with non-array responses', function () {
    try {
        Operation::fromArray([
            'responses' => 'invalid',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('responses');
        expect($e->getMessages()['responses'])->toContain('responses must be an array.');
    }
});

it('rejects operation with empty summary', function () {
    try {
        Operation::fromArray([
            'summary' => '',
            'responses' => [
                '200' => ['description' => 'OK'],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('summary');
        expect($e->getMessages()['summary'])->toContain('summary must be filled in.');
    }
});

it('rejects operation with empty description', function () {
    try {
        Operation::fromArray([
            'description' => '',
            'responses' => [
                '200' => ['description' => 'OK'],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('description');
        expect($e->getMessages()['description'])->toContain('description must be filled in.');
    }
});

it('rejects operation with empty operationId', function () {
    try {
        Operation::fromArray([
            'operationId' => '',
            'responses' => [
                '200' => ['description' => 'OK'],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('operationId');
        expect($e->getMessages()['operationId'])->toContain('operation id must be filled in.');
    }
});

it('rejects operation with non-array tags', function () {
    try {
        Operation::fromArray([
            'tags' => 'not-an-array',
            'responses' => [
                '200' => ['description' => 'OK'],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('tags');
        expect($e->getMessages()['tags'])->toContain('tags must be an array.');
    }
});

it('rejects operation with empty tag in tags array', function () {
    try {
        Operation::fromArray([
            'tags' => ['users', ''],
            'responses' => [
                '200' => ['description' => 'OK'],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('tags.1');
        expect($e->getMessages()['tags.1'])->toContain('tags.1 must be filled in.');
    }
});

it('rejects operation with non-string tag in tags array', function () {
    try {
        Operation::fromArray([
            'tags' => ['users', 123],
            'responses' => [
                '200' => ['description' => 'OK'],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('tags.1');
        expect($e->getMessages()['tags.1'])->toContain('tags.1 must be a string.');
    }
});

it('rejects operation with non-boolean deprecated', function () {
    try {
        Operation::fromArray([
            'deprecated' => 'not-boolean',
            'responses' => [
                '200' => ['description' => 'OK'],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('deprecated');
        expect($e->getMessages()['deprecated'])->toContain("deprecated must be either 'true' or 'false'.");
    }
});

it('rejects operation with non-array parameters', function () {
    try {
        Operation::fromArray([
            'parameters' => 'not-an-array',
            'responses' => [
                '200' => ['description' => 'OK'],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('parameters');
        expect($e->getMessages()['parameters'])->toContain('parameters must be an array.');
    }
});

it('rejects operation with non-array security', function () {
    try {
        Operation::fromArray([
            'security' => 'not-an-array',
            'responses' => [
                '200' => ['description' => 'OK'],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('security');
        expect($e->getMessages()['security'])->toContain('security must be an array.');
    }
});

it('accepts operation with valid minimal properties', function () {
    $operation = Operation::fromArray([
        'responses' => [
            '200' => [
                'description' => 'Successful response',
            ],
        ],
    ]);

    expect($operation)->toBeInstanceOf(Operation::class);
    expect($operation->responses)->toHaveCount(1);
});

it('accepts operation with all valid optional properties', function () {
    $operation = Operation::fromArray([
        'tags' => ['users', 'admin'],
        'summary' => 'Get user profile',
        'description' => 'Retrieve user profile information',
        'operationId' => 'getUserProfile',
        'deprecated' => true,
        'parameters' => [
            [
                'name' => 'userId',
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'integer'],
            ],
        ],
        'requestBody' => [
            'content' => [
                'application/json' => [
                    'schema' => ['type' => 'object'],
                ],
            ],
        ],
        'responses' => [
            '200' => [
                'description' => 'Success',
            ],
            '404' => [
                'description' => 'Not found',
            ],
        ],
        'security' => [
            ['ApiKeyAuth' => []],
        ],
        'servers' => [
            [
                'url' => 'https://api.example.com',
            ],
        ],
    ]);

    expect($operation)->toBeInstanceOf(Operation::class);
    expect($operation->tags)->toBe(['users', 'admin']);
    expect($operation->summary)->toBe('Get user profile');
    expect($operation->description)->toBe('Retrieve user profile information');
    expect($operation->operationId)->toBe('getUserProfile');
    expect($operation->deprecated)->toBeTrue();
    expect($operation->parameters)->toHaveCount(1);
    expect($operation->requestBody)->not->toBeNull();
    expect($operation->responses)->toHaveCount(2);
    expect($operation->security)->toHaveCount(1);
    expect($operation->servers)->toHaveCount(1);
});
