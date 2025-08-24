<?php declare(strict_types=1);

use Bambamboole\OpenApi\Validation\Spec\RuleConfig;
use Bambamboole\OpenApi\Validation\Spec\RulesetLoader;
use Bambamboole\OpenApi\Validation\Spec\ValidationSeverity;

it('can discover rules with attributes', function () {
    $loader = new RulesetLoader;
    $rules = $loader->getRegisteredRules();

    expect($rules)->toHaveKey('required-fields');
    expect($rules)->toHaveKey('path-parameters');
    expect($rules)->toHaveKey('response-codes');
    expect($rules)->toHaveKey('valid-security-references');

    expect($rules['required-fields']['description'])->toBe('Validates that required fields are present and follow best practices');
    expect($rules['path-parameters']['defaultSeverity'])->toBe(ValidationSeverity::ERROR);
});

it('can load ruleset from array', function () {
    $rulesetData = [
        'rules' => [
            'required-fields' => [
                'enabled' => true,
                'severity' => 'error',
            ],
            'path-parameters' => [
                'enabled' => true,
                'severity' => 'warning',
            ],
            'response-codes' => [
                'enabled' => false, // This rule should be skipped
            ],
        ],
    ];

    $loader = new RulesetLoader;
    $configs = $loader->loadFromArray($rulesetData);

    expect($configs)->toHaveCount(2); // Only 2 enabled rules
    expect($configs[0])->toBeInstanceOf(RuleConfig::class);
    expect($configs[0]->severity)->toBe(ValidationSeverity::ERROR);
    expect($configs[1]->severity)->toBe(ValidationSeverity::WARNING);
});

it('throws exception for unknown rule', function () {
    $rulesetData = [
        'rules' => [
            'unknown-rule' => [
                'enabled' => true,
            ],
        ],
    ];

    $loader = new RulesetLoader;

    expect(fn () => $loader->loadFromArray($rulesetData))
        ->toThrow(InvalidArgumentException::class, 'Unknown rule: unknown-rule');
});

it('throws exception for invalid severity', function () {
    $rulesetData = [
        'rules' => [
            'required-fields' => [
                'enabled' => true,
                'severity' => 'invalid-severity',
            ],
        ],
    ];

    $loader = new RulesetLoader;

    expect(fn () => $loader->loadFromArray($rulesetData))
        ->toThrow(InvalidArgumentException::class, 'Invalid severity: invalid-severity');
});

it('can load ruleset from YAML file', function () {
    $yamlContent = <<<'YAML'
rules:
  required-fields:
    description: "Custom description"
    severity: error
    enabled: true
  path-parameters:
    severity: warning
    enabled: true
YAML;

    $tempFile = tempnam(sys_get_temp_dir(), 'ruleset');
    file_put_contents($tempFile, $yamlContent);

    try {
        $loader = new RulesetLoader;
        $configs = $loader->loadFromFile($tempFile);

        expect($configs)->toHaveCount(2);
        expect($configs[0]->severity)->toBe(ValidationSeverity::ERROR);
        expect($configs[1]->severity)->toBe(ValidationSeverity::WARNING);
    } finally {
        unlink($tempFile);
    }
});

it('throws exception for non-existent file', function () {
    $loader = new RulesetLoader;

    expect(fn () => $loader->loadFromFile('/non/existent/file.yaml'))
        ->toThrow(InvalidArgumentException::class, 'Ruleset file not found');
});
