<?php declare(strict_types=1);

use App\Objects\OpenApiDocument;
use App\Validation\Spec\ValidationSeverity;
use App\Validation\Validator;

it('can validate with ruleset array', function () {
    $document = OpenApiDocument::fromArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [], // Empty paths should trigger a warning
    ]);

    $ruleset = [
        'rules' => [
            'required-fields' => [
                'enabled' => true,
                'severity' => 'warning',
            ],
            'path-parameters' => [
                'enabled' => true,
                'severity' => 'error',
            ],
        ],
    ];

    $result = Validator::validateWithRulesetArray($document, $ruleset);

    expect($result->hasWarnings())->toBeTrue();
    expect($result->getWarnings())->toHaveCount(1);
    expect($result->getWarnings()[0]->message)->toContain('Paths object should not be empty');
    expect($result->getWarnings()[0]->severity)->toBe(ValidationSeverity::WARNING);
});

it('can validate with YAML ruleset file', function () {
    $yamlContent = <<<'YAML'
rules:
  required-fields:
    description: "Validates required fields"
    severity: info
    enabled: true
  valid-security-references:
    severity: error
    enabled: false
YAML;

    $tempFile = tempnam(sys_get_temp_dir(), 'ruleset');
    file_put_contents($tempFile, $yamlContent);

    try {
        $document = OpenApiDocument::fromArray([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
                // Missing description - should trigger info level
            ],
            'paths' => [], // Empty paths - should trigger info level
        ]);

        $result = Validator::validateWithRuleset($document, $tempFile);

        // The required-fields rule generates messages with different severities
        // - Empty paths: WARNING (built into the rule)
        // - Missing description: INFO (built into the rule)
        // The ruleset severity 'info' doesn't override individual rule severities currently
        expect($result->hasWarnings())->toBeTrue(); // Empty paths warning
        expect($result->hasInfo())->toBeTrue(); // Missing description info
        expect($result->hasErrors())->toBeFalse(); // security rule is disabled

        expect($result->getWarnings())->toHaveCount(1);
        expect($result->getInfo())->toHaveCount(1);

        $warnings = array_values($result->getWarnings());
        $info = array_values($result->getInfo());

        expect($warnings[0]->message)->toContain('Paths object should not be empty');
        expect($info[0]->message)->toContain('Info description is recommended');
    } finally {
        unlink($tempFile);
    }
});

it('can get available rules', function () {
    $rules = Validator::getAvailableRules();

    expect($rules)->toBeArray();
    expect($rules)->toHaveKey('required-fields');
    expect($rules)->toHaveKey('path-parameters');
    expect($rules)->toHaveKey('response-codes');
    expect($rules)->toHaveKey('valid-security-references');

    // Check rule metadata
    expect($rules['required-fields'])->toHaveKey('class');
    expect($rules['required-fields'])->toHaveKey('description');
    expect($rules['required-fields'])->toHaveKey('defaultSeverity');
    expect($rules['required-fields']['description'])->toBe('Validates that required fields are present and follow best practices');
});
