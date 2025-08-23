<?php declare(strict_types=1);
use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\Link;

it('rejects link with empty operationRef', function () {

    try {
        Link::fromArray([
            'operationRef' => '',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('operationRef');
        expect($e->getMessages()['operationRef'])->toContain('operation ref must be filled in.');
    }
});

it('rejects link with empty operationId', function () {

    try {
        Link::fromArray([
            'operationId' => '',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('operationId');
        expect($e->getMessages()['operationId'])->toContain('operation id must be filled in.');
    }
});

it('rejects link with non-string operationRef', function () {

    try {
        Link::fromArray([
            'operationRef' => 123,
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('operationRef');
        expect($e->getMessages()['operationRef'])->toContain('operation ref must be a string.');
    }
});

it('rejects link with non-string operationId', function () {

    try {
        Link::fromArray([
            'operationId' => ['invalid'],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('operationId');
        expect($e->getMessages()['operationId'])->toContain('operation id must be a string.');
    }
});

it('rejects link with non-array parameters', function () {

    try {
        Link::fromArray([
            'operationId' => 'getUserById',
            'parameters' => 'not-an-array',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('parameters');
        expect($e->getMessages()['parameters'])->toContain('parameters must be an array.');
    }
});

it('rejects link with empty description', function () {

    try {
        Link::fromArray([
            'operationId' => 'getUserById',
            'description' => '',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('description');
        expect($e->getMessages()['description'])->toContain('description must be filled in.');
    }
});

it('rejects link with non-string description', function () {

    try {
        Link::fromArray([
            'operationId' => 'getUserById',
            'description' => 123,
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('description');
        expect($e->getMessages()['description'])->toContain('description must be a string.');
    }
});

it('rejects link with non-array server', function () {

    try {
        Link::fromArray([
            'operationId' => 'getUserById',
            'server' => 'not-an-array',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('server');
        expect($e->getMessages()['server'])->toContain('server must be an array.');
    }
});

it('accepts link with valid optional fields', function () {

    $link = Link::fromArray([
        'operationRef' => '#/paths/~1users~1{userId}/get',
        'parameters' => [
            'userId' => '$response.body#/id',
        ],
        'description' => 'Get user by ID',
        'server' => [
            'url' => 'https://api.example.com',
        ],
    ]);

    expect($link->operationRef)->toBe('#/paths/~1users~1{userId}/get');
    expect($link->parameters)->toHaveKey('userId');
    expect($link->description)->toBe('Get user by ID');
    expect($link->server)->not->toBeNull();
});

it('accepts link without any fields (minimal valid link)', function () {

    $link = Link::fromArray([]);

    expect($link->operationRef)->toBeNull();
    expect($link->operationId)->toBeNull();
    expect($link->parameters)->toBeNull();
    expect($link->requestBody)->toBeNull();
    expect($link->description)->toBeNull();
    expect($link->server)->toBeNull();
});
