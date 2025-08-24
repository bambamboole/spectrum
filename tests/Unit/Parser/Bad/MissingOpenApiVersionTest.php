<?php declare(strict_types=1);

use App\Exceptions\ParseException;

it('rejects schema missing OpenAPI version', function () {
    $this->expectSchema([
        // Missing 'openapi' field
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toMatchArray([
            'openapi' => [
                'openapi is required.',
            ],
        ]);
    });
});
