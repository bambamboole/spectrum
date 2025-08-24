<?php declare(strict_types=1);

namespace App\Validation\Spec;

use Attribute;

/**
 * Attribute to register validation rules for use in rulesets.
 *
 * Usage:
 * #[Rule('rule-name', 'Description of the rule')]
 * class MyRule implements SpecRuleInterface
 */
#[Attribute(Attribute::TARGET_CLASS)]
readonly class RuleAttribute
{
    public function __construct(
        public string $name,
        public string $description,
        public ValidationSeverity $defaultSeverity = ValidationSeverity::ERROR
    ) {}
}
