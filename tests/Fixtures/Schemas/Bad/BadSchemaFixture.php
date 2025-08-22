<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas\Bad;

use Bambamboole\OpenApi\Tests\Fixtures\Schemas\SchemaFixtureInterface;

abstract class BadSchemaFixture implements SchemaFixtureInterface
{
    public function passes(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [];
    }

    abstract public function schema(): array;

    abstract public function violations(): array;

    public static function all(): array
    {
        $fixtures = [];
        foreach (glob(__DIR__.'/*.php') as $file) {
            if (str_ends_with($file, 'BadSchemaFixture.php')) {
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
