<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\Example;
use Bambamboole\OpenApi\ReferenceResolver;

it('can parse minimal example with value only', function () {

    $example = Example::fromArray([
        'value' => 'Hello World',
    ]);

    expect($example)->toBeInstanceOf(Example::class);
    expect($example->value)->toBe('Hello World');
    expect($example->summary)->toBeNull();
    expect($example->description)->toBeNull();
    expect($example->externalValue)->toBeNull();
});

it('can parse example with external value', function () {

    $example = Example::fromArray([
        'externalValue' => 'https://example.com/examples/user.json',
        'summary' => 'User example',
        'description' => 'A complete user object example',
    ]);

    expect($example->externalValue)->toBe('https://example.com/examples/user.json');
    expect($example->summary)->toBe('User example');
    expect($example->description)->toBe('A complete user object example');
    expect($example->value)->toBeNull();
});

it('can parse example with all properties', function () {

    $example = Example::fromArray([
        'summary' => 'Complete user example',
        'description' => 'A comprehensive example showing all user properties with realistic data',
        'value' => [
            'id' => 12345,
            'username' => 'john_doe',
            'email' => 'john.doe@example.com',
            'profile' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'age' => 30,
                'address' => [
                    'street' => '123 Main St',
                    'city' => 'Anytown',
                    'state' => 'CA',
                    'zipCode' => '12345',
                ],
            ],
            'preferences' => [
                'newsletter' => true,
                'theme' => 'dark',
                'language' => 'en',
            ],
            'createdAt' => '2023-01-15T10:30:00Z',
            'lastLoginAt' => '2023-12-20T14:22:33Z',
        ],
    ]);

    expect($example->summary)->toBe('Complete user example');
    expect($example->description)->toBe('A comprehensive example showing all user properties with realistic data');
    expect($example->value)->toBeArray();
    expect($example->value)->toHaveKey('id');
    expect($example->value)->toHaveKey('username');
    expect($example->value)->toHaveKey('profile');
    expect($example->value['id'])->toBe(12345);
    expect($example->value['username'])->toBe('john_doe');
    expect($example->value['profile'])->toHaveKey('firstName');
    expect($example->value['profile'])->toHaveKey('address');
    expect($example->value['profile']['address'])->toHaveKey('street');
    expect($example->externalValue)->toBeNull();
});

it('can parse example with different value types', function () {

    $examples = [
        // String example
        Example::fromArray([
            'summary' => 'String example',
            'value' => 'Simple string value',
        ]),
        // Number example
        Example::fromArray([
            'summary' => 'Number example',
            'value' => 42.5,
        ]),
        // Boolean example
        Example::fromArray([
            'summary' => 'Boolean example',
            'value' => true,
        ]),
        // Array example
        Example::fromArray([
            'summary' => 'Array example',
            'value' => ['apple', 'banana', 'cherry'],
        ]),
        // Null example
        Example::fromArray([
            'summary' => 'Null example',
            'value' => null,
        ]),
    ];

    expect($examples[0]->value)->toBe('Simple string value');
    expect($examples[1]->value)->toBe(42.5);
    expect($examples[2]->value)->toBeTrue();
    expect($examples[3]->value)->toBe(['apple', 'banana', 'cherry']);
    expect($examples[4]->value)->toBeNull();
});

it('can parse multiple examples in components', function () {

    $examples = Example::multiple([
        'UserExample' => [
            'summary' => 'Basic user',
            'description' => 'A simple user object',
            'value' => [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
        ],
        'AdminExample' => [
            'summary' => 'Admin user',
            'description' => 'An admin user with elevated permissions',
            'value' => [
                'id' => 2,
                'name' => 'Jane Admin',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'permissions' => ['read', 'write', 'delete'],
            ],
        ],
        'ExternalExample' => [
            'summary' => 'External reference',
            'description' => 'Example stored externally',
            'externalValue' => 'https://api.example.com/examples/complex-user.json',
        ],
    ]);

    expect($examples)->toHaveCount(3);
    expect($examples)->toHaveKey('UserExample');
    expect($examples)->toHaveKey('AdminExample');
    expect($examples)->toHaveKey('ExternalExample');
    expect($examples['UserExample'])->toBeInstanceOf(Example::class);
    expect($examples['UserExample']->summary)->toBe('Basic user');
    expect($examples['UserExample']->value)->toHaveKey('id');
    expect($examples['AdminExample']->value)->toHaveKey('role');
    expect($examples['AdminExample']->value)->toHaveKey('permissions');
    expect($examples['ExternalExample']->externalValue)->toBe('https://api.example.com/examples/complex-user.json');
});

it('can parse example with complex nested objects and arrays', function () {

    $example = Example::fromArray([
        'summary' => 'E-commerce order',
        'description' => 'Complete order with customer, items, and payment information',
        'value' => [
            'orderId' => 'ORD-2023-001234',
            'status' => 'confirmed',
            'customer' => [
                'customerId' => 'CUST-789',
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'shippingAddress' => [
                    'street' => '456 Oak Avenue',
                    'city' => 'Springfield',
                    'state' => 'IL',
                    'zipCode' => '62701',
                    'country' => 'USA',
                ],
                'billingAddress' => [
                    'street' => '456 Oak Avenue',
                    'city' => 'Springfield',
                    'state' => 'IL',
                    'zipCode' => '62701',
                    'country' => 'USA',
                ],
            ],
            'items' => [
                [
                    'productId' => 'PROD-001',
                    'name' => 'Wireless Headphones',
                    'quantity' => 1,
                    'unitPrice' => 99.99,
                    'totalPrice' => 99.99,
                    'category' => 'Electronics',
                ],
                [
                    'productId' => 'PROD-002',
                    'name' => 'Phone Case',
                    'quantity' => 2,
                    'unitPrice' => 19.99,
                    'totalPrice' => 39.98,
                    'category' => 'Accessories',
                ],
            ],
            'payment' => [
                'method' => 'credit_card',
                'last4' => '4242',
                'status' => 'paid',
                'amount' => 139.97,
                'currency' => 'USD',
                'transactionId' => 'TXN-789123',
            ],
            'shipping' => [
                'method' => 'standard',
                'carrier' => 'FedEx',
                'trackingNumber' => '1234567890',
                'estimatedDelivery' => '2023-12-25',
            ],
            'timestamps' => [
                'createdAt' => '2023-12-20T10:30:00Z',
                'updatedAt' => '2023-12-20T14:45:00Z',
            ],
        ],
    ]);

    expect($example->summary)->toBe('E-commerce order');
    expect($example->value)->toHaveKey('orderId');
    expect($example->value)->toHaveKey('customer');
    expect($example->value)->toHaveKey('items');
    expect($example->value)->toHaveKey('payment');
    expect($example->value['customer'])->toHaveKey('shippingAddress');
    expect($example->value['customer'])->toHaveKey('billingAddress');
    expect($example->value['items'])->toHaveCount(2);
    expect($example->value['items'][0])->toHaveKey('productId');
    expect($example->value['items'][0])->toHaveKey('quantity');
    expect($example->value['payment'])->toHaveKey('transactionId');
    expect($example->value['shipping'])->toHaveKey('trackingNumber');
});

it('can parse example reference', function () {
    ReferenceResolver::initialize([
        'openapi' => '3.0.0',
        'info' => [],
        'paths' => [],
        'components' => [
            'examples' => [
                'UserExample' => [
                    'summary' => 'Sample user',
                    'description' => 'A typical user in our system',
                    'value' => [
                        'id' => 42,
                        'username' => 'sample_user',
                        'email' => 'user@example.com',
                    ],
                ],
            ],
        ],
    ]);

    $example = Example::fromArray([
        '$ref' => '#/components/examples/UserExample',
    ]);

    expect($example->summary)->toBe('Sample user');
    expect($example->description)->toBe('A typical user in our system');
    expect($example->value)->toHaveKey('id');
    expect($example->value)->toHaveKey('username');
    expect($example->value['id'])->toBe(42);
    expect($example->value['username'])->toBe('sample_user');

    ReferenceResolver::clear();
});
