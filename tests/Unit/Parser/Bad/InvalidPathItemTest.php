<?php declare(strict_types=1);

use App\Exceptions\ParseException;
use App\Objects\PathItem;

it('rejects path item with empty summary', function () {
    try {
        PathItem::fromArray([
            'summary' => '',
            'get' => [
                'responses' => [
                    '200' => ['description' => 'OK'],
                ],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('summary');
        expect($e->getMessages()['summary'])->toContain('summary must be filled in.');
    }
});

it('rejects path item with empty description', function () {
    try {
        PathItem::fromArray([
            'description' => '',
            'get' => [
                'responses' => [
                    '200' => ['description' => 'OK'],
                ],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('description');
        expect($e->getMessages()['description'])->toContain('description must be filled in.');
    }
});

it('rejects path item with non-array servers', function () {
    try {
        PathItem::fromArray([
            'servers' => 'not-an-array',
            'get' => [
                'responses' => [
                    '200' => ['description' => 'OK'],
                ],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('servers');
        expect($e->getMessages()['servers'])->toContain('servers must be an array.');
    }
});

it('rejects path item with non-array parameters', function () {
    try {
        PathItem::fromArray([
            'parameters' => 'not-an-array',
            'get' => [
                'responses' => [
                    '200' => ['description' => 'OK'],
                ],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('parameters');
        expect($e->getMessages()['parameters'])->toContain('parameters must be an array.');
    }
});

it('rejects path item with non-array HTTP method', function () {
    try {
        PathItem::fromArray([
            'get' => 'not-an-array',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('get');
        expect($e->getMessages()['get'])->toContain('get must be an array.');
    }
});

it('rejects path item with invalid operation in HTTP method', function () {
    try {
        PathItem::fromArray([
            'post' => [
                'summary' => 'Create something',
                // Missing required responses
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('post.responses');
        expect($e->getMessages()['post.responses'])->toContain('responses is required.');
    }
});

it('accepts path item with no operations defined', function () {
    $pathItem = PathItem::fromArray([
        'summary' => 'Empty path item',
        'description' => 'A path item with no operations',
    ]);

    expect($pathItem)->toBeInstanceOf(PathItem::class);
    expect($pathItem->summary)->toBe('Empty path item');
    expect($pathItem->description)->toBe('A path item with no operations');
    expect($pathItem->getOperations())->toHaveCount(0);
});

it('accepts path item with valid optional properties', function () {
    $pathItem = PathItem::fromArray([
        'summary' => 'User endpoint',
        'description' => 'Operations for user management',
        'parameters' => [
            [
                'name' => 'userId',
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'integer'],
            ],
        ],
        'servers' => [
            [
                'url' => 'https://api.example.com',
                'description' => 'Production server',
            ],
        ],
        'get' => [
            'summary' => 'Get user',
            'responses' => [
                '200' => ['description' => 'User found'],
                '404' => ['description' => 'User not found'],
            ],
        ],
        'put' => [
            'summary' => 'Update user',
            'requestBody' => [
                'content' => [
                    'application/json' => [
                        'schema' => ['type' => 'object'],
                    ],
                ],
            ],
            'responses' => [
                '200' => ['description' => 'User updated'],
                '400' => ['description' => 'Invalid input'],
                '404' => ['description' => 'User not found'],
            ],
        ],
    ]);

    expect($pathItem)->toBeInstanceOf(PathItem::class);
    expect($pathItem->summary)->toBe('User endpoint');
    expect($pathItem->description)->toBe('Operations for user management');
    expect($pathItem->parameters)->toHaveCount(1);
    expect($pathItem->servers)->toHaveCount(1);
    expect($pathItem->get)->not->toBeNull();
    expect($pathItem->put)->not->toBeNull();
    expect($pathItem->post)->toBeNull();
    expect($pathItem->getOperations())->toHaveCount(2);
});

it('accepts path item with all HTTP methods', function () {
    $pathItem = PathItem::fromArray([
        'get' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
        'put' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
        'post' => [
            'responses' => ['201' => ['description' => 'Created']],
        ],
        'delete' => [
            'responses' => ['204' => ['description' => 'No Content']],
        ],
        'options' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
        'head' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
        'patch' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
        'trace' => [
            'responses' => ['200' => ['description' => 'OK']],
        ],
    ]);

    expect($pathItem)->toBeInstanceOf(PathItem::class);
    expect($pathItem->getOperations())->toHaveCount(8);
    expect($pathItem->get)->not->toBeNull();
    expect($pathItem->put)->not->toBeNull();
    expect($pathItem->post)->not->toBeNull();
    expect($pathItem->delete)->not->toBeNull();
    expect($pathItem->options)->not->toBeNull();
    expect($pathItem->head)->not->toBeNull();
    expect($pathItem->patch)->not->toBeNull();
    expect($pathItem->trace)->not->toBeNull();
});
