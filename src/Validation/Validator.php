<?php declare(strict_types=1);
namespace Bambamboole\OpenApi\Validation;

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Validation\Spec\RuleConfig;
use Bambamboole\OpenApi\Validation\Spec\ValidationError;
use Bambamboole\OpenApi\Validation\Spec\ValidationSeverity;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

class Validator
{
    private static ?Factory $factory = null;

    public static function make(array $data, array $rules, array $messages = []): \Illuminate\Contracts\Validation\Validator
    {
        if (! self::$factory) {
            $loader = new FileLoader(new Filesystem, dirname(__DIR__, 2).'/lang');
            $translator = new Translator($loader, 'en');
            self::$factory = new Factory($translator, new Container);
        }

        return self::$factory->make($data, $rules, $messages);
    }

    public static function validate(array $data, array $rules, string $keyPrefix = ''): void
    {
        $validator = self::make($data, $rules);

        if ($validator->fails()) {
            $prefix = empty($keyPrefix) ? '' : "{$keyPrefix}.";
            $messages = collect($validator->errors()->toArray())
                ->mapWithKeys(fn ($errors, $field) => [ltrim("{$prefix}{$field}", '.') => $errors])
                ->toArray();

            throw ParseException::withMessages($messages);
        }
    }

    public static function validateDocument(OpenApiDocument $document, string|array $rules): array
    {
        $errors = [];
        foreach (Arr::wrap($rules) as $config) {
            /** @var RuleConfig $config */
            $rule = new $config->ruleClass;

            $rule->validate($document, function ($message, $key, ?ValidationSeverity $severity = null) use (&$errors, $config) {
                return $errors[] = new ValidationError($message, $key, $severity ?? $config->severity);
            });
        }

        return $errors;
    }
}
