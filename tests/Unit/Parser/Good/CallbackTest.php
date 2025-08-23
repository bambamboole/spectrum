<?php declare(strict_types=1);

use Bambamboole\OpenApi\Context\ParsingContext;
use Bambamboole\OpenApi\Factories\ComponentsFactory;
use Bambamboole\OpenApi\Objects\Callback;

it('can parse minimal callback with single expression', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $callback = $factory->createCallback([
        '{$request.body#/webhookUrl}' => [
            'post' => [
                'requestBody' => [
                    'description' => 'Webhook payload',
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
                        'description' => 'Webhook received successfully',
                    ],
                ],
            ],
        ],
    ]);

    expect($callback)->toBeInstanceOf(Callback::class);
    expect($callback->expressions)->toHaveKey('{$request.body#/webhookUrl}');
    expect($callback->expressions['{$request.body#/webhookUrl}'])->toHaveKey('post');
    expect($callback->expressions['{$request.body#/webhookUrl}']['post'])->toHaveKey('requestBody');
    expect($callback->expressions['{$request.body#/webhookUrl}']['post'])->toHaveKey('responses');
});

it('can parse callback with multiple expressions', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $callback = $factory->createCallback([
        '{$request.body#/successUrl}' => [
            'post' => [
                'description' => 'Success webhook',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'status' => ['type' => 'string', 'enum' => ['success']],
                                    'transactionId' => ['type' => 'string'],
                                    'amount' => ['type' => 'number'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => ['description' => 'Success acknowledged'],
                    '400' => ['description' => 'Invalid payload'],
                ],
            ],
        ],
        '{$request.body#/failureUrl}' => [
            'post' => [
                'description' => 'Failure webhook',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'status' => ['type' => 'string', 'enum' => ['failed']],
                                    'error' => ['type' => 'string'],
                                    'errorCode' => ['type' => 'string'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => ['description' => 'Failure acknowledged'],
                ],
            ],
        ],
    ]);

    expect($callback->expressions)->toHaveCount(2);
    expect($callback->expressions)->toHaveKey('{$request.body#/successUrl}');
    expect($callback->expressions)->toHaveKey('{$request.body#/failureUrl}');
    expect($callback->expressions['{$request.body#/successUrl}']['post']['description'])->toBe('Success webhook');
    expect($callback->expressions['{$request.body#/failureUrl}']['post']['description'])->toBe('Failure webhook');
});

it('can parse callback with complex expressions and multiple HTTP methods', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $callback = $factory->createCallback([
        '{$request.body#/notificationEndpoint}?event={$request.body#/eventType}' => [
            'post' => [
                'description' => 'Send notification via POST',
                'parameters' => [
                    [
                        'name' => 'event',
                        'in' => 'query',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'timestamp' => ['type' => 'string', 'format' => 'date-time'],
                                    'payload' => ['type' => 'object'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => ['description' => 'Notification delivered'],
                    '404' => ['description' => 'Endpoint not found'],
                    '500' => ['description' => 'Delivery failed'],
                ],
            ],
            'put' => [
                'description' => 'Update notification status via PUT',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'status' => ['type' => 'string', 'enum' => ['delivered', 'failed']],
                                    'attempts' => ['type' => 'integer'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => ['description' => 'Status updated'],
                ],
            ],
        ],
    ]);

    expect($callback->expressions)->toHaveKey('{$request.body#/notificationEndpoint}?event={$request.body#/eventType}');
    $expression = $callback->expressions['{$request.body#/notificationEndpoint}?event={$request.body#/eventType}'];
    expect($expression)->toHaveKey('post');
    expect($expression)->toHaveKey('put');
    expect($expression['post']['description'])->toBe('Send notification via POST');
    expect($expression['put']['description'])->toBe('Update notification status via PUT');
    expect($expression['post'])->toHaveKey('parameters');
    expect($expression['post']['parameters'][0]['name'])->toBe('event');
});

it('can parse multiple callbacks in components', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $callbacks = $factory->createCallbacks([
        'PaymentWebhook' => [
            '{$request.body#/webhookUrl}' => [
                'post' => [
                    'description' => 'Payment status webhook',
                    'requestBody' => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'paymentId' => ['type' => 'string'],
                                        'status' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Webhook processed'],
                    ],
                ],
            ],
        ],
        'OrderWebhook' => [
            '{$request.body#/orderCallbackUrl}' => [
                'post' => [
                    'description' => 'Order status webhook',
                    'requestBody' => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'orderId' => ['type' => 'string'],
                                        'status' => ['type' => 'string'],
                                        'updatedAt' => ['type' => 'string', 'format' => 'date-time'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Order webhook processed'],
                        '400' => ['description' => 'Invalid order data'],
                    ],
                ],
            ],
        ],
    ]);

    expect($callbacks)->toHaveCount(2);
    expect($callbacks)->toHaveKey('PaymentWebhook');
    expect($callbacks)->toHaveKey('OrderWebhook');
    expect($callbacks['PaymentWebhook'])->toBeInstanceOf(Callback::class);
    expect($callbacks['OrderWebhook'])->toBeInstanceOf(Callback::class);
    expect($callbacks['PaymentWebhook']->expressions)->toHaveKey('{$request.body#/webhookUrl}');
    expect($callbacks['OrderWebhook']->expressions)->toHaveKey('{$request.body#/orderCallbackUrl}');
});

it('can parse callback with runtime expressions for headers and query parameters', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = ComponentsFactory::create($context);

    $callback = $factory->createCallback([
        '{$request.body#/callbackUrl}?source={$method}&id={$request.body#/transactionId}' => [
            'post' => [
                'description' => 'Transaction callback with dynamic parameters',
                'parameters' => [
                    [
                        'name' => 'source',
                        'in' => 'query',
                        'schema' => ['type' => 'string'],
                        'description' => 'The HTTP method that triggered this callback',
                    ],
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'schema' => ['type' => 'string'],
                        'description' => 'Transaction identifier from request',
                    ],
                ],
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'status' => ['type' => 'string'],
                                    'message' => ['type' => 'string'],
                                    'details' => ['type' => 'object'],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => ['description' => 'Callback acknowledged'],
                ],
            ],
        ],
    ]);

    expect($callback->expressions)->toHaveKey('{$request.body#/callbackUrl}?source={$method}&id={$request.body#/transactionId}');
    $expression = $callback->expressions['{$request.body#/callbackUrl}?source={$method}&id={$request.body#/transactionId}'];
    expect($expression['post']['parameters'])->toHaveCount(2);
    expect($expression['post']['parameters'][0]['name'])->toBe('source');
    expect($expression['post']['parameters'][1]['name'])->toBe('id');
    expect($expression['post']['parameters'][0]['description'])->toBe('The HTTP method that triggered this callback');
    expect($expression['post']['parameters'][1]['description'])->toBe('Transaction identifier from request');
});

it('can parse callback reference', function () {
    $context = ParsingContext::fromDocument([
        'openapi' => '3.0.0',
        'info' => [],
        'paths' => [],
        'components' => [
            'callbacks' => [
                'WebhookCallback' => [
                    '{$request.body#/webhookUrl}' => [
                        'post' => [
                            'description' => 'Referenced webhook callback',
                            'responses' => [
                                '200' => ['description' => 'Success'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);
    $factory = ComponentsFactory::create($context);

    $callback = $factory->createCallback([
        '$ref' => '#/components/callbacks/WebhookCallback',
    ]);

    expect($callback->expressions)->toHaveKey('{$request.body#/webhookUrl}');
    expect($callback->expressions['{$request.body#/webhookUrl}']['post']['description'])->toBe('Referenced webhook callback');
});
