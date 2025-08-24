<?php declare(strict_types=1);

use App\Exceptions\ParseException;

it('rejects schema with invalid security scheme', function () {
    $this->expectSchema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'securitySchemes' => [
                'InvalidApiKey' => [
                    'type' => 'apiKey',
                    // Missing required 'name' and 'in' fields for apiKey type
                    'description' => 'Invalid API key scheme',
                ],
            ],
        ],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toMatchArray([
            'components.securitySchemes.InvalidApiKey.name' => [
                'name must be filled in if type has the value apiKey.',
            ],
            'components.securitySchemes.InvalidApiKey.in' => [
                'in must be filled in if type has the value apiKey.',
            ],
        ]);
    });
});
