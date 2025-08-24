<?php declare(strict_types=1);
namespace App\Validation\Spec;

readonly class RuleConfig
{
    public function __construct(
        /** @var class-string<SpecRuleInterface> */
        public string $ruleClass,
        public ValidationSeverity $severity = ValidationSeverity::ERROR,
    ) {}
}
