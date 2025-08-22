<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas\Good;

class GoodSchemaFixture1 extends GoodSchemaFixture
{
    public function schema(): array
    {
        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Minimal API',
                'version' => '1.0.0',
            ],
            'paths' => [],
        ];
    }
}
