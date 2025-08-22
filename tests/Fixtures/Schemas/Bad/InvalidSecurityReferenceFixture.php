<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas\Bad;

class InvalidSecurityReferenceFixture extends BadSchemaFixture
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
        ];
    }

    public function violations(): array
    {
        return [
            'security.1.NonExistentAuth' => [
                "Security scheme 'NonExistentAuth' is not defined in components.securitySchemes.",
            ],
        ];
    }
}
