<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas\Bad;

class MissingSecuritySchemesFixture extends BadSchemaFixture
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
            // No components.securitySchemes defined
            'security' => [
                ['ApiKeyAuth' => []], // References non-existent security scheme
            ],
        ];
    }

    public function violations(): array
    {
        return [
            'security.0.ApiKeyAuth' => [
                "Security scheme 'ApiKeyAuth' is not defined in components.securitySchemes.",
            ],
        ];
    }
}
