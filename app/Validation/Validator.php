<?php declare(strict_types=1);

namespace App\Validation;

use App\Exceptions\ParseException;
use App\Objects\OpenApiDocument;
use App\Validation\Spec\RuleConfig;
use App\Validation\Spec\RulesetLoader;
use App\Validation\Spec\ValidationError;
use App\Validation\Spec\ValidationResult;
use App\Validation\Spec\ValidationSeverity;
use Illuminate\Support\Arr;

class Validator
{
    private static bool $skipValidation = false;

    public static function validate(array $data, array $rules, string $keyPrefix = ''): void
    {
        if (self::$skipValidation) {
            return;
        }

        $validator = app('validator')->make($data, $rules);

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

    public static function validateWithRuleset(OpenApiDocument $document, string|array $rulesetPath): ValidationResult
    {
        $loader = new RulesetLoader;

        $ruleConfig = is_string($rulesetPath)
            ? $loader->loadFromFile($rulesetPath)
            : $loader->loadFromArray($rulesetPath);

        return self::validateDocument($document, $ruleConfig);
    }

    public static function getAvailableRules(): array
    {
        $loader = new RulesetLoader;

        return $loader->getRegisteredRules();
    }
}
