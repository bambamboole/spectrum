<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Validation\Spec;

use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\Yaml\Yaml;

class RulesetLoader
{
    private array $registeredRules = [];

    public function __construct()
    {
        $this->discoverRules();
    }

    public function loadFromFile(string $filePath): array
    {
        if (! file_exists($filePath)) {
            throw new InvalidArgumentException("Ruleset file not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        $data = Yaml::parse($content);

        return $this->loadFromArray($data);
    }

    public function loadFromArray(array $data): array
    {
        if (! isset($data['rules']) || ! is_array($data['rules'])) {
            throw new InvalidArgumentException('Ruleset must contain a "rules" section');
        }

        $ruleConfigs = [];

        foreach ($data['rules'] as $ruleName => $config) {
            if (! isset($this->registeredRules[$ruleName])) {
                throw new InvalidArgumentException("Unknown rule: {$ruleName}");
            }

            // Skip disabled rules
            if (isset($config['enabled']) && ! $config['enabled']) {
                continue;
            }

            $ruleClass = $this->registeredRules[$ruleName]['class'];
            $defaultSeverity = $this->registeredRules[$ruleName]['defaultSeverity'];

            // Parse severity from config
            $severity = $defaultSeverity;
            if (isset($config['severity'])) {
                $severity = $this->parseSeverity($config['severity']);
            }

            $ruleConfigs[] = new RuleConfig($ruleClass, $severity);
        }

        return $ruleConfigs;
    }

    public function getRegisteredRules(): array
    {
        return $this->registeredRules;
    }

    private function discoverRules(): void
    {
        // Get all classes in the Spec namespace
        $specDir = __DIR__;
        $files = glob($specDir.'/*Rule.php');

        foreach ($files as $file) {
            $className = 'Bambamboole\\OpenApi\\Validation\\Spec\\'.basename($file, '.php');

            if (! class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);

            // Skip abstract classes and interfaces
            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            // Check if it implements SpecRuleInterface
            if (! $reflection->implementsInterface(SpecRuleInterface::class)) {
                continue;
            }

            // Look for RuleAttribute
            $attributes = $reflection->getAttributes(RuleAttribute::class);
            if (empty($attributes)) {
                continue;
            }

            $attribute = $attributes[0]->newInstance();
            $this->registeredRules[$attribute->name] = [
                'class' => $className,
                'description' => $attribute->description,
                'defaultSeverity' => $attribute->defaultSeverity,
            ];
        }
    }

    private function parseSeverity(string $severityString): ValidationSeverity
    {
        return match (strtolower($severityString)) {
            'error' => ValidationSeverity::ERROR,
            'warning', 'warn' => ValidationSeverity::WARNING,
            'info', 'information' => ValidationSeverity::INFO,
            default => throw new InvalidArgumentException("Invalid severity: {$severityString}")
        };
    }
}
