<?php declare(strict_types=1);
namespace Bambamboole\OpenApi\Tests;

use Bambamboole\OpenApi\OpenApiParser;
use Pest\Expectation;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function providesGoodTestSchemas() {}

    public function expectSchema(array $schema): Expectation
    {
        return expect(fn () => OpenApiParser::make()->parseArray($schema));
    }

    public function schema(array $data = []): array
    {
        return array_merge([
            'openapi' => '3.1.1',
            'info' => [
                'title' => 'Minimal API',
                'version' => '1.0.0',
            ],
            'paths' => [],
        ], $data);
    }
}
