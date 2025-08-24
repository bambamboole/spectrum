<?php declare(strict_types=1);

use App\Exceptions\ParseException;

it('rejects parameter missing required name', function () {
    $this->expectSchema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Invalid Parameter API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'parameters' => [
                'InvalidParam' => [
                    // Missing required 'name' field
                    'in' => 'query',
                    'schema' => ['type' => 'string'],
                ],
            ],
        ],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toHaveKey('components.parameters.InvalidParam.name');
        expect($e->getMessages()['components.parameters.InvalidParam.name'])
            ->toContain('name is required.');
    });
});

it('rejects parameter missing required in field', function () {
    $this->expectSchema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Invalid Parameter API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'parameters' => [
                'InvalidParam' => [
                    'name' => 'test',
                    // Missing required 'in' field
                    'schema' => ['type' => 'string'],
                ],
            ],
        ],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toHaveKey('components.parameters.InvalidParam.in');
        expect($e->getMessages()['components.parameters.InvalidParam.in'])
            ->toContain('in is required.');
    });
});

it('rejects parameter with invalid in value', function () {
    $this->expectSchema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Invalid Parameter API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'parameters' => [
                'InvalidParam' => [
                    'name' => 'test',
                    'in' => 'invalid', // Invalid value
                    'schema' => ['type' => 'string'],
                ],
            ],
        ],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toHaveKey('components.parameters.InvalidParam.in');
        expect($e->getMessages()['components.parameters.InvalidParam.in'])
            ->toContain('The selected value for in is invalid.');
    });
});

it('rejects parameter with empty name', function () {
    $this->expectSchema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Invalid Parameter API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'parameters' => [
                'InvalidParam' => [
                    'name' => '', // Empty name not allowed
                    'in' => 'query',
                    'schema' => ['type' => 'string'],
                ],
            ],
        ],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toHaveKey('components.parameters.InvalidParam.name');
        expect($e->getMessages()['components.parameters.InvalidParam.name'])
            ->toContain('name is required.');
    });
});
