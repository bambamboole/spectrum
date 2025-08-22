<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas\Bad;

class InvalidSecuritySchemeFixture extends BadSchemaFixture
{
    public function schema(): array
    {
        return [
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
        ];
    }

    public function violations(): array
    {
        return [
            'components.securitySchemes.InvalidApiKey.name' => [
                'name must be filled in if type has the value apiKey.',
            ],
            'components.securitySchemes.InvalidApiKey.in' => [
                'in must be filled in if type has the value apiKey.',
            ],
        ];
    }
}
