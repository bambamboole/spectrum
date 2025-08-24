<?php declare(strict_types=1);

namespace App\Commands;

use App\Validation\Spec\RulesetLoader;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Yaml\Yaml;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CreateRulesetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:ruleset 
                            {name? : Name for the ruleset file (without extension)}
                            {--template=default : Ruleset template to use (default, strict, permissive)}
                            {--output-dir=. : Directory to save the ruleset file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a custom validation ruleset for OpenAPI specifications';

    /**
     * Available ruleset templates.
     */
    private array $templates = [
        'default' => [
            'description' => 'Balanced validation with common best practices',
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
                    'enabled' => true,
                    'severity' => 'info',
                ],
            ],
        ],
        'strict' => [
            'description' => 'Comprehensive validation with strict requirements',
            'rules' => [
                'required-fields' => [
                    'enabled' => true,
                    'severity' => 'error',
                ],
                'path-parameters' => [
                    'enabled' => true,
                    'severity' => 'error',
                ],
                'response-codes' => [
                    'enabled' => true,
                    'severity' => 'error',
                ],
                'valid-security-references' => [
                    'enabled' => true,
                    'severity' => 'error',
                ],
            ],
        ],
        'permissive' => [
            'description' => 'Minimal validation focusing only on critical issues',
            'rules' => [
                'required-fields' => [
                    'enabled' => true,
                    'severity' => 'error',
                ],
                'path-parameters' => [
                    'enabled' => false,
                ],
                'response-codes' => [
                    'enabled' => false,
                ],
            ],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(RulesetLoader $rulesetLoader)
    {
        $this->components->info('Creating OpenAPI Validation Ruleset');
        $this->line('');

        // Get or prompt for ruleset name
        $name = $this->argument('name') ?: text(
            label: 'What should we name this ruleset?',
            default: 'my-ruleset',
            validate: fn (string $value) => $this->isValidFilename($value)
                ? null
                : 'Invalid filename. Please use only letters, numbers, hyphens, and underscores.'
        );

        // Get template choice
        $template = $this->option('template');
        if (! array_key_exists($template, $this->templates)) {
            $template = select(
                label: 'Which template would you like to use?',
                options: array_map(fn ($key, $tmpl) => "{$key} - {$tmpl['description']}", array_keys($this->templates), $this->templates),
                default: 'default - '.$this->templates['default']['description']
            );
            $template = explode(' - ', $template)[0];
        }

        // Get output directory
        $outputDir = $this->option('output-dir');
        if (! is_dir($outputDir)) {
            if (! confirm("Directory '{$outputDir}' doesn't exist. Create it?", true)) {
                return self::FAILURE;
            }
            mkdir($outputDir, 0755, true);
        }

        // Build ruleset configuration
        $config = $this->buildRulesetConfig($template);

        // Allow interactive customization
        if (confirm('Would you like to customize the rules interactively?', false)) {
            $config = $this->customizeRules($config, $rulesetLoader);
        }

        // Generate filename and save
        $filename = $outputDir.'/'.$name.'.yaml';
        $this->saveRuleset($config, $filename);

        $this->line('');
        $this->components->info("✅ Ruleset created successfully: {$filename}");
        $this->components->info("Use it with: spectrum validate <spec> --ruleset={$filename}");

        return self::SUCCESS;
    }

    /**
     * Build initial ruleset configuration from template.
     */
    private function buildRulesetConfig(string $template): array
    {
        $config = [
            'name' => ucfirst($template).' Validation Ruleset',
            'description' => $this->templates[$template]['description'],
            'created' => date('Y-m-d H:i:s'),
            'rules' => $this->templates[$template]['rules'],
        ];

        return $config;
    }

    /**
     * Allow interactive customization of rules.
     */
    private function customizeRules(array $config, RulesetLoader $rulesetLoader): array
    {
        $this->line('');
        $this->components->info('Customizing Rules');
        $this->line('Available severities: error, warning, info, disabled');
        $this->line('');

        $availableRules = $rulesetLoader->getRegisteredRules();

        foreach ($availableRules as $ruleKey => $ruleClass) {
            $currentConfig = $config['rules'][$ruleKey] ?? ['enabled' => false];
            $currentStatus = $currentConfig['enabled'] ?
                ($currentConfig['severity'] ?? 'warning') : 'disabled';

            $this->line("Rule: <comment>{$ruleKey}</comment>");
            $this->line("Current: {$currentStatus}");

            $action = select(
                label: 'What would you like to do?',
                options: ['keep', 'enable as error', 'enable as warning', 'enable as info', 'disable'],
                default: 'keep'
            );

            switch ($action) {
                case 'enable as error':
                    $config['rules'][$ruleKey] = ['enabled' => true, 'severity' => 'error'];
                    break;
                case 'enable as warning':
                    $config['rules'][$ruleKey] = ['enabled' => true, 'severity' => 'warning'];
                    break;
                case 'enable as info':
                    $config['rules'][$ruleKey] = ['enabled' => true, 'severity' => 'info'];
                    break;
                case 'disable':
                    $config['rules'][$ruleKey] = ['enabled' => false];
                    break;
                    // 'keep' - no changes needed
            }

            $this->line('');
        }

        return $config;
    }

    /**
     * Save the ruleset to a YAML file.
     */
    private function saveRuleset(array $config, string $filename): void
    {
        $yaml = "# OpenAPI Validation Ruleset\n";
        $yaml .= "# Generated by Spectrum on {$config['created']}\n";
        $yaml .= "# {$config['description']}\n\n";

        // Remove metadata from config before converting to YAML
        unset($config['created']);

        $yaml .= Yaml::dump($config, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        file_put_contents($filename, $yaml);
    }

    /**
     * Check if filename is valid.
     */
    private function isValidFilename(string $name): bool
    {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $name) === 1;
    }
}
