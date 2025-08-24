<?php declare(strict_types=1);

use App\Objects\Callback;

it('accepts callback with empty expressions', function () {

    $callback = Callback::fromArray([]);

    expect($callback)->toBeInstanceOf(Callback::class);
    expect($callback->expressions)->toBeEmpty();
});

it('accepts callback with valid expressions and path items', function () {

    $callback = Callback::fromArray([
        '{$request.body#/webhookUrl}' => [
            'post' => [
                'description' => 'Webhook callback',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'event' => ['type' => 'string'],
                                    'data' => ['type' => 'object'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Webhook processed successfully',
                    ],
                ],
            ],
        ],
    ]);

    expect($callback->expressions)->toHaveKey('{$request.body#/webhookUrl}');
    expect($callback->expressions['{$request.body#/webhookUrl}'])->toHaveKey('post');
    expect($callback->expressions['{$request.body#/webhookUrl}']['post'])->toHaveKey('description');
    expect($callback->expressions['{$request.body#/webhookUrl}']['post']['description'])->toBe('Webhook callback');
});

it('accepts callback with multiple expressions', function () {

    $callback = Callback::fromArray([
        '{$request.body#/successUrl}' => [
            'post' => [
                'description' => 'Success callback',
                'responses' => ['200' => ['description' => 'Success processed']],
            ],
        ],
        '{$request.body#/failureUrl}' => [
            'post' => [
                'description' => 'Failure callback',
                'responses' => ['200' => ['description' => 'Failure processed']],
            ],
        ],
    ]);

    expect($callback->expressions)->toHaveCount(2);
    expect($callback->expressions)->toHaveKey('{$request.body#/successUrl}');
    expect($callback->expressions)->toHaveKey('{$request.body#/failureUrl}');
});

it('accepts callback with complex runtime expressions', function () {

    $callback = Callback::fromArray([
        '{$request.body#/callbackUrl}?event={$request.body#/eventType}&user={$response.body#/userId}' => [
            'post' => [
                'description' => 'Complex callback with multiple runtime expressions',
                'parameters' => [
                    [
                        'name' => 'event',
                        'in' => 'query',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                    [
                        'name' => 'user',
                        'in' => 'query',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'responses' => [
                    '200' => ['description' => 'Complex callback processed'],
                ],
            ],
        ],
    ]);

    expect($callback->expressions)->toHaveKey('{$request.body#/callbackUrl}?event={$request.body#/eventType}&user={$response.body#/userId}');
    $expression = $callback->expressions['{$request.body#/callbackUrl}?event={$request.body#/eventType}&user={$response.body#/userId}'];
    expect($expression['post']['parameters'])->toHaveCount(2);
    expect($expression['post']['parameters'][0]['name'])->toBe('event');
    expect($expression['post']['parameters'][1]['name'])->toBe('user');
});

it('accepts callback with multiple HTTP methods', function () {

    $callback = Callback::fromArray([
        '{$request.body#/webhookUrl}' => [
            'post' => [
                'description' => 'Create webhook notification',
                'responses' => ['201' => ['description' => 'Notification created']],
            ],
            'put' => [
                'description' => 'Update webhook notification',
                'responses' => ['200' => ['description' => 'Notification updated']],
            ],
            'delete' => [
                'description' => 'Delete webhook notification',
                'responses' => ['204' => ['description' => 'Notification deleted']],
            ],
        ],
    ]);

    $expression = $callback->expressions['{$request.body#/webhookUrl}'];
    expect($expression)->toHaveKey('post');
    expect($expression)->toHaveKey('put');
    expect($expression)->toHaveKey('delete');
    expect($expression['post']['description'])->toBe('Create webhook notification');
    expect($expression['put']['description'])->toBe('Update webhook notification');
    expect($expression['delete']['description'])->toBe('Delete webhook notification');
});
