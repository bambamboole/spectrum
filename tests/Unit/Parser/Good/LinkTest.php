<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\Link;
use Bambamboole\OpenApi\ReferenceResolver;

it('can parse minimal link with operationRef only', function () {

    $link = Link::fromArray([
        'operationRef' => '#/paths/~1users~1{userId}/get',
    ]);

    expect($link)->toBeInstanceOf(Link::class);
    expect($link->operationRef)->toBe('#/paths/~1users~1{userId}/get');
    expect($link->operationId)->toBeNull();
    expect($link->parameters)->toBeNull();
    expect($link->requestBody)->toBeNull();
    expect($link->description)->toBeNull();
    expect($link->server)->toBeNull();
});

it('can parse link with operationId', function () {

    $link = Link::fromArray([
        'operationId' => 'getUserById',
        'parameters' => [
            'userId' => '$response.body#/id',
        ],
        'description' => 'Get user details by ID from response',
    ]);

    expect($link->operationRef)->toBeNull();
    expect($link->operationId)->toBe('getUserById');
    expect($link->parameters)->toHaveKey('userId');
    expect($link->parameters['userId'])->toBe('$response.body#/id');
    expect($link->description)->toBe('Get user details by ID from response');
});

it('can parse link with all properties', function () {

    $link = Link::fromArray([
        'operationRef' => '#/paths/~1orders~1{orderId}/get',
        'parameters' => [
            'orderId' => '$response.body#/orderId',
            'includeItems' => true,
        ],
        'requestBody' => [
            'filters' => '$request.query.filters',
        ],
        'description' => 'Get order details with items included',
        'server' => [
            'url' => 'https://api.orders.example.com',
            'description' => 'Orders API server',
            'variables' => [
                'version' => [
                    'default' => 'v1',
                    'enum' => ['v1', 'v2'],
                ],
            ],
        ],
    ]);

    expect($link->operationRef)->toBe('#/paths/~1orders~1{orderId}/get');
    expect($link->parameters)->toHaveKey('orderId');
    expect($link->parameters)->toHaveKey('includeItems');
    expect($link->parameters['orderId'])->toBe('$response.body#/orderId');
    expect($link->parameters['includeItems'])->toBeTrue();
    expect($link->requestBody)->toHaveKey('filters');
    expect($link->requestBody['filters'])->toBe('$request.query.filters');
    expect($link->description)->toBe('Get order details with items included');
    expect($link->server)->not->toBeNull();
    expect($link->server->url)->toBe('https://api.orders.example.com');
    expect($link->server->description)->toBe('Orders API server');
    expect($link->server->variables)->toHaveKey('version');
});

it('can parse link with various parameter expressions', function () {

    $link = Link::fromArray([
        'operationId' => 'searchResults',
        'parameters' => [
            'userId' => '$response.body#/user/id',
            'status' => '$response.header.X-Status',
            'page' => '$request.query.page',
            'limit' => 10,
            'includeDeleted' => false,
        ],
        'description' => 'Search with various parameter sources',
    ]);

    expect($link->operationId)->toBe('searchResults');
    expect($link->parameters)->toHaveCount(5);
    expect($link->parameters['userId'])->toBe('$response.body#/user/id');
    expect($link->parameters['status'])->toBe('$response.header.X-Status');
    expect($link->parameters['page'])->toBe('$request.query.page');
    expect($link->parameters['limit'])->toBe(10);
    expect($link->parameters['includeDeleted'])->toBeFalse();
});

it('can parse link with complex request body', function () {

    $link = Link::fromArray([
        'operationRef' => '#/paths/~1notifications/post',
        'requestBody' => [
            'type' => 'email',
            'recipient' => '$response.body#/user/email',
            'template' => 'order_confirmation',
            'data' => [
                'orderNumber' => '$response.body#/orderNumber',
                'totalAmount' => '$response.body#/total',
                'items' => '$response.body#/items',
            ],
        ],
        'description' => 'Send order confirmation email',
    ]);

    expect($link->operationRef)->toBe('#/paths/~1notifications/post');
    expect($link->requestBody)->toHaveKey('type');
    expect($link->requestBody)->toHaveKey('recipient');
    expect($link->requestBody)->toHaveKey('template');
    expect($link->requestBody)->toHaveKey('data');
    expect($link->requestBody['type'])->toBe('email');
    expect($link->requestBody['recipient'])->toBe('$response.body#/user/email');
    expect($link->requestBody['data'])->toHaveKey('orderNumber');
    expect($link->requestBody['data']['orderNumber'])->toBe('$response.body#/orderNumber');
});

it('can parse multiple links in components', function () {

    $links = Link::multiple([
        'GetUserById' => [
            'operationId' => 'getUserById',
            'parameters' => [
                'userId' => '$response.body#/id',
            ],
        ],
        'GetUserOrders' => [
            'operationRef' => '#/paths/~1users~1{userId}~1orders/get',
            'parameters' => [
                'userId' => '$response.body#/id',
                'status' => 'active',
            ],
        ],
        'UpdateUser' => [
            'operationId' => 'updateUser',
            'parameters' => [
                'userId' => '$response.body#/id',
            ],
            'requestBody' => '$request.body',
            'description' => 'Update user with the same data',
        ],
    ]);

    expect($links)->toHaveCount(3);
    expect($links)->toHaveKey('GetUserById');
    expect($links)->toHaveKey('GetUserOrders');
    expect($links)->toHaveKey('UpdateUser');
    expect($links['GetUserById'])->toBeInstanceOf(Link::class);
    expect($links['GetUserById']->operationId)->toBe('getUserById');
    expect($links['GetUserOrders']->operationRef)->toBe('#/paths/~1users~1{userId}~1orders/get');
    expect($links['UpdateUser']->requestBody)->toBe('$request.body');
    expect($links['UpdateUser']->description)->toBe('Update user with the same data');
});

it('can parse link with server variables', function () {

    $link = Link::fromArray([
        'operationId' => 'getResource',
        'server' => [
            'url' => 'https://{environment}.api.example.com/{version}',
            'description' => 'Dynamic API server',
            'variables' => [
                'environment' => [
                    'default' => 'prod',
                    'enum' => ['dev', 'staging', 'prod'],
                    'description' => 'API environment',
                ],
                'version' => [
                    'default' => 'v1',
                    'description' => 'API version',
                ],
            ],
        ],
    ]);

    expect($link->server)->not->toBeNull();
    expect($link->server->url)->toBe('https://{environment}.api.example.com/{version}');
    expect($link->server->variables)->toHaveKey('environment');
    expect($link->server->variables)->toHaveKey('version');
    expect($link->server->variables['environment'])->toHaveKey('default');
    expect($link->server->variables['environment'])->toHaveKey('enum');
    expect($link->server->variables['environment']['default'])->toBe('prod');
    expect($link->server->variables['environment']['enum'])->toBe(['dev', 'staging', 'prod']);
});

it('can parse link reference', function () {
    ReferenceResolver::initialize([
        'openapi' => '3.0.0',
        'info' => [],
        'paths' => [],
        'components' => [
            'links' => [
                'GetUserById' => [
                    'operationId' => 'getUserById',
                    'parameters' => [
                        'userId' => '$response.body#/id',
                    ],
                    'description' => 'Get user by ID from response',
                ],
            ],
        ],
    ]);

    $link = Link::fromArray([
        '$ref' => '#/components/links/GetUserById',
    ]);

    expect($link->operationId)->toBe('getUserById');
    expect($link->parameters)->toHaveKey('userId');
    expect($link->parameters['userId'])->toBe('$response.body#/id');
    expect($link->description)->toBe('Get user by ID from response');

    ReferenceResolver::clear();
});
