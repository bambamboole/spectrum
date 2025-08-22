<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas\Bad;

class BadSchemaFixture1 extends BadSchemaFixture
{
    public function schema(): array
    {
        return [
            // Missing 'openapi' field
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
                'openapi is required.',
            ],
        ];
    }
}
