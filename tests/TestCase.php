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
}
