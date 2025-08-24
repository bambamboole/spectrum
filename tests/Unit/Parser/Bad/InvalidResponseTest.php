<?php declare(strict_types=1);
use App\Exceptions\ParseException;
use App\Objects\Response;

it('rejects response missing required description', function () {

    try {
        Response::fromArray([
            'headers' => [
                'X-Rate-Limit' => [
                    'schema' => ['type' => 'integer'],
                ],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('description');
        expect($e->getMessages()['description'])->toContain('description is required.');
    }
});

it('rejects response with empty description', function () {

    try {
        Response::fromArray([
            'description' => '',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('description');
        expect($e->getMessages()['description'])->toContain('description is required.');
    }
});

it('rejects response with non-string description', function () {

    try {
        Response::fromArray([
            'description' => 123,
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('description');
        expect($e->getMessages()['description'])->toContain('description must be a string.');
    }
});

it('rejects response with invalid headers structure', function () {

    try {
        Response::fromArray([
            'description' => 'Valid description',
            'headers' => 'invalid-headers',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('headers');
        expect($e->getMessages()['headers'])->toContain('headers must be an array.');
    }
});

it('rejects response with invalid content structure', function () {

    try {
        Response::fromArray([
            'description' => 'Valid description',
            'content' => 'invalid-content',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('content');
        expect($e->getMessages()['content'])->toContain('content must be an array.');
    }
});

it('rejects response with invalid links structure', function () {

    try {
        Response::fromArray([
            'description' => 'Valid description',
            'links' => 'invalid-links',
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('links');
        expect($e->getMessages()['links'])->toContain('links must be an array.');
    }
});

it('rejects response with invalid schema in content', function () {

    try {
        Response::fromArray([
            'description' => 'Response with invalid content schema',
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'string',
                        'minLength' => -1, // Invalid constraint
                    ],
                ],
            ],
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('content.application/json.schema.minLength');
        expect($e->getMessages()['content.application/json.schema.minLength'])->toContain('The min length must be at least 0.');
    }
});

it('accepts response with valid optional fields', function () {

    $response = Response::fromArray([
        'description' => 'Valid response with optional fields',
        'headers' => [
            'X-Custom-Header' => [
                'description' => 'Custom header',
                'schema' => ['type' => 'string'],
            ],
        ],
        'content' => [
            'application/json' => [
                'schema' => ['type' => 'object'],
            ],
        ],
        'links' => [
            'NextPage' => [
                'operationRef' => '#/paths/~1items/get',
            ],
        ],
    ]);

    expect($response->description)->toBe('Valid response with optional fields');
    expect($response->headers)->toHaveCount(1);
    expect($response->content)->toHaveCount(1);
    expect($response->links)->toHaveKey('NextPage');
});

it('accepts response with empty optional arrays', function () {

    $response = Response::fromArray([
        'description' => 'Valid response with empty arrays',
        'headers' => [],
        'content' => [],
        'links' => [],
    ]);

    expect($response->description)->toBe('Valid response with empty arrays');
    expect($response->headers)->toBeEmpty();
    expect($response->content)->toBeEmpty();
    expect($response->links)->toBeEmpty();
});
