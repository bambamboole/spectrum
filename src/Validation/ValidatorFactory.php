<?php declare(strict_types=1);
namespace Bambamboole\OpenApi\Validation;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

class ValidatorFactory extends Factory
{
    private static ?self $instance = null;

    public static function create(): self
    {
        if (! self::$instance) {
            $loader = new FileLoader(new Filesystem, dirname(__DIR__, 2).'/lang');
            $translator = new Translator($loader, 'en');
            self::$instance = new self($translator, new Container);
        }

        return self::$instance;
    }
}
