<?php declare(strict_types=1);

use App\Exceptions\ParseException;

it('rejects invalid OpenAPI version', function () {
    $this->expectSchema([
        'openapi' => '2.0.0', // Invalid version format
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toMatchArray([
            'openapi' => [
                'The openapi must be at least version 3.0.0.',
            ],
        ]);
    });
});
