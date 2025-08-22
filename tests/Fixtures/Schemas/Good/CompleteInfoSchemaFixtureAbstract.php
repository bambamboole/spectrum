<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas\Good;

class CompleteInfoSchemaFixtureAbstract extends AbstractGoodSchemaFixture
{
    public function schema(): array
    {
        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Complete API',
                'version' => '2.0.0',
                'description' => 'A comprehensive API example',
                'termsOfService' => 'https://example.com/terms',
                'contact' => [
                    'name' => 'API Support',
                    'email' => 'support@example.com',
                    'url' => 'https://example.com/support',
                ],
                'license' => [
                    'name' => 'MIT',
                    'url' => 'https://opensource.org/licenses/MIT',
                ],
            ],
            'paths' => [],
            'externalDocs' => [
                'description' => 'Find more info here',
                'url' => 'https://example.com/docs',
            ],
        ];
    }
}
