<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Validation;

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Validation\Spec\RuleConfig;
use Bambamboole\OpenApi\Validation\Spec\RulesetLoader;
use Bambamboole\OpenApi\Validation\Spec\ValidationError;
use Bambamboole\OpenApi\Validation\Spec\ValidationResult;
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

    private static bool $skipValidation = false;

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
        if (self::$skipValidation) {
            return;
        }

        $validator = self::make($data, $rules);

        if ($validator->fails()) {
            $prefix = empty($keyPrefix) ? '' : "{$keyPrefix}.";
            $messages = collect($validator->errors()->toArray())
                ->mapWithKeys(fn ($errors, $field) => [ltrim("{$prefix}{$field}", '.') => $errors])
                ->toArray();

            throw ParseException::withMessages($messages);
        }
    }

    public static function enablePerformanceMode(): void
    {
        self::$skipValidation = true;
    }

    public static function disablePerformanceMode(): void
    {
        self::$skipValidation = false;
    }

    public static function isPerformanceModeEnabled(): bool
    {
        return self::$skipValidation;
    }

    public static function validateDocument(OpenApiDocument $document, string|array $rules): ValidationResult
    {
        $result = new ValidationResult;
        foreach (Arr::wrap($rules) as $config) {
            if ($config instanceof RuleConfig) {
                $rule = new $config->ruleClass;
                $defaultSeverity = $config->severity;
            } else {
                $rule = new $config;
                $defaultSeverity = ValidationSeverity::ERROR;
            }

            $failCallback = function (string $message, string $path, ?ValidationSeverity $severity = null) use ($result, $defaultSeverity) {
                $result->add(new ValidationError($message, $path, $severity ?? $defaultSeverity));
            };

            $rule->validate($document, $failCallback);
        }

        return $result;
    }

    public static function validateWithRuleset(OpenApiDocument $document, string $rulesetPath): ValidationResult
    {
        $loader = new RulesetLoader;
        $ruleConfigs = $loader->loadFromFile($rulesetPath);

        return self::validateDocument($document, $ruleConfigs);
    }

    public static function validateWithRulesetArray(OpenApiDocument $document, array $rulesetData): ValidationResult
    {
        $loader = new RulesetLoader;
        $ruleConfigs = $loader->loadFromArray($rulesetData);

        return self::validateDocument($document, $ruleConfigs);
    }

    public static function getAvailableRules(): array
    {
        $loader = new RulesetLoader;

        return $loader->getRegisteredRules();
    }
}
