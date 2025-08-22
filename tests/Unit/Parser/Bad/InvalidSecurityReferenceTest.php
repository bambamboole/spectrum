<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;

it('rejects schema with invalid security reference', function () {
    $this->expectSchema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'securitySchemes' => [
                'ApiKeyAuth' => [
                    'type' => 'apiKey',
                    'in' => 'header',
                    'name' => 'X-API-Key',
                ],
            ],
        ],
        'security' => [
            ['ApiKeyAuth' => []],
            ['NonExistentAuth' => []], // References non-existent security scheme
        ],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toMatchArray([
            'security.1.NonExistentAuth' => [
                "Security scheme 'NonExistentAuth' is not defined in components.securitySchemes.",
            ],
        ]);
    });
});
