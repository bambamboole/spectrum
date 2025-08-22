<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;

it('rejects schema referencing security scheme without defining it', function () {
    $this->expectSchema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        // No components.securitySchemes defined
        'security' => [
            ['ApiKeyAuth' => []], // References non-existent security scheme
        ],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toMatchArray([
            'security.0.ApiKeyAuth' => [
                "Security scheme 'ApiKeyAuth' is not defined in components.securitySchemes.",
            ],
        ]);
    });
});
