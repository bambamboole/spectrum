<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas\Bad;

class BadSchemaFixture2 extends BadSchemaFixture
{
    public function schema(): array
    {
        return [
            'openapi' => '2.0.0', // Invalid version format
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
            ],
            'paths' => [],
        ];
    }

    public function violations(): array
    {
        return [
            'openapi' => [
                'openapi format is invalid.',
            ],
        ];
    }
}
