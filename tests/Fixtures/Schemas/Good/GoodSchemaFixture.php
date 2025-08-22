<?php declare(strict_types=1);
namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas\Good;

use Bambamboole\OpenApi\Tests\Fixtures\Schemas\SchemaFixtureInterface;

abstract class GoodSchemaFixture implements SchemaFixtureInterface
{
    public function passes(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    abstract public function schema(): array;

    public function violations(): array
    {
        return [];
    }

    public static function all(): array
    {
        $fixtures = [];
        foreach (glob(__DIR__.'/*.php') as $file) {
            if (str_ends_with($file, 'GoodSchemaFixture.php')) {
                continue;
            }
            $className = basename($file, '.php');
            $fullClassName = __NAMESPACE__.'\\'.$className;
            if (class_exists($fullClassName)) {
                $fixtures[$className] = [new $fullClassName];
            }
        }

        return $fixtures;
    }
}
