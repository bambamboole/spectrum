<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas\Bad;

class InvalidSchemaConstraintsFixture extends BadSchemaFixture
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
                'schemas' => [
                    'InvalidSchema' => [
                        'type' => 'string',
                        'minLength' => 10,
                        'maxLength' => 5, // maxLength < minLength (invalid)
                    ],
                ],
            ],
        ];
    }

    public function violations(): array
    {
        return [
            'maxLength' => [
                'The max length must be greater than or equal 10.',
            ],
        ];
    }
}
