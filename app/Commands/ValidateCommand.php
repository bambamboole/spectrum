<?php declare(strict_types=1);

namespace App\Commands;

use App\OpenApiParser;
use App\Validation\Spec\RulesetLoader;
use App\Validation\Validator;
use LaravelZero\Framework\Commands\Command;

class ValidateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'validate
                            {path? : Path to the OpenAPI specification file}
                            {--ruleset= : Path to custom validation ruleset file}
                            {--format=table : Output format (table, json, compact)}
                            {--output= : Output file path (default: stdout)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate an OpenAPI specification file';

    /**
     * Execute the console command.
     */
    public function handle(OpenApiParser $parser, RulesetLoader $rulesetLoader)
    {
        $path = $this->argument('path');
        $isQuiet = $this->option('quiet');
        $isVerbose = $this->option('verbose');
        $format = $this->option('format');
        $outputFile = $this->option('output');

        // Validate arguments
        if (! $path) {
            $this->components->error('Path argument is required.');
            $this->line('');
            $this->components->info('Usage: spectrum validate <path> [options]');
            $this->components->info('Example: spectrum validate openapi.yaml --format=json');

            return self::FAILURE;
        }

        if (! file_exists($path)) {
            $this->components->error("File not found: {$path}");

            return self::FAILURE;
        }

        if (! in_array($format, ['table', 'json', 'compact'])) {
            $this->components->error("Invalid format '{$format}'. Valid formats: table, json, compact");

            return self::FAILURE;
        }

        try {
            // Show progress for large files if not quiet
            if (! $isQuiet) {
                $this->components->info("Validating OpenAPI specification: {$path}");
                if ($isVerbose) {
                    $this->components->info('File size: '.number_format(filesize($path)).' bytes');
                }
            }

            // Parse the document
            if (! $isQuiet) {
                $this->components->info('Parsing document...');
            }
            $document = $parser->parseFile($path);

            // Load ruleset
            if (! $isQuiet && $isVerbose) {
                $this->components->info('Loading validation rules...');
            }

            if ($this->option('ruleset')) {
                $rulesetPath = $this->option('ruleset');
                if (! file_exists($rulesetPath)) {
                    $this->components->error("Ruleset file not found: {$rulesetPath}");

                    return self::FAILURE;
                }
                $ruleset = $rulesetLoader->loadFromFile($rulesetPath);
                if (! $isQuiet && $isVerbose) {
                    $this->components->info("Using custom ruleset: {$rulesetPath}");
                }
            } else {
                $ruleset = $rulesetLoader->loadFromArray([
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
                            'enabled' => false,
                        ],
                    ],
                ]);
                if (! $isQuiet && $isVerbose) {
                    $this->components->info('Using default validation rules');
                }
            }

            // Validate document
            if (! $isQuiet) {
                $this->components->info('Running validation...');
            }
            $result = Validator::validateDocument($document, $ruleset);

            // Output results
            $output = $this->formatOutput($result, $format, $isVerbose);

            if ($outputFile) {
                file_put_contents($outputFile, $output);
                if (! $isQuiet) {
                    $this->components->info("Results written to: {$outputFile}");
                }
            } else {
                if ($format === 'json') {
                    $this->line($output);
                } elseif ($format === 'compact') {
                    $this->line($output);
                } else {
                    $this->displayTableOutput($result, $isQuiet, $isVerbose);
                }
            }

            // Summary
            if (! $isQuiet) {
                $this->displaySummary($result, $isVerbose);
            }

            // Return appropriate exit code
            return $result->hasErrors() ? self::FAILURE : self::SUCCESS;

        } catch (\Exception $e) {
            $this->components->error("Validation failed: {$e->getMessage()}");
            if ($isVerbose) {
                $this->line($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    /**
     * Format output based on the specified format.
     */
    private function formatOutput($result, string $format, bool $isVerbose): string
    {
        switch ($format) {
            case 'json':
                return $this->formatJsonOutput($result, $isVerbose);
            case 'compact':
                return $this->formatCompactOutput($result);
            default:
                return ''; // Table format is handled separately
        }
    }

    /**
     * Format output as JSON.
     */
    private function formatJsonOutput($result, bool $isVerbose): string
    {
        $output = [
            'valid' => ! $result->hasErrors(),
            'summary' => [
                'errors' => count($result->getErrors()),
                'warnings' => count($result->getWarnings()),
                'info' => count($result->getInfo()),
                'total' => count($result->getErrors()) + count($result->getWarnings()) + count($result->getInfo()),
            ],
        ];

        if ($result->hasErrors()) {
            $output['errors'] = array_map(fn ($error) => [
                'path' => $error->path,
                'message' => $error->message,
                'severity' => 'error',
            ], $result->getErrors());
        }

        if ($result->hasWarnings()) {
            $output['warnings'] = array_map(fn ($error) => [
                'path' => $error->path,
                'message' => $error->message,
                'severity' => 'warning',
            ], $result->getWarnings());
        }

        if ($result->hasInfo()) {
            $output['info'] = array_map(fn ($error) => [
                'path' => $error->path,
                'message' => $error->message,
                'severity' => 'info',
            ], $result->getInfo());
        }

        return json_encode($output, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }

    /**
     * Format output in compact format.
     */
    private function formatCompactOutput($result): string
    {
        $lines = [];

        foreach ($result->getErrors() as $error) {
            $lines[] = "ERROR: {$error->path} - {$error->message}";
        }

        foreach ($result->getWarnings() as $error) {
            $lines[] = "WARNING: {$error->path} - {$error->message}";
        }

        foreach ($result->getInfo() as $error) {
            $lines[] = "INFO: {$error->path} - {$error->message}";
        }

        return implode("\n", $lines);
    }

    /**
     * Display table format output.
     */
    private function displayTableOutput($result, bool $isQuiet, bool $isVerbose): void
    {
        if ($result->hasErrors()) {
            if (! $isQuiet) {
                $this->components->error('Validation Errors');
            }
            $this->table(['Path', 'Message'], array_map(fn ($error) => [$error->path, $error->message], $result->getErrors()));
        }

        if ($result->hasWarnings()) {
            if (! $isQuiet) {
                $this->components->warn('Validation Warnings');
            }
            $this->table(['Path', 'Message'], array_map(fn ($error) => [$error->path, $error->message], $result->getWarnings()));
        }

        if ($result->hasInfo() && ($isVerbose || ! $result->hasErrors() && ! $result->hasWarnings())) {
            if (! $isQuiet) {
                $this->components->info('Validation Information');
            }
            $this->table(['Path', 'Message'], array_map(fn ($error) => [$error->path, $error->message], $result->getInfo()));
        }
    }

    /**
     * Display validation summary.
     */
    private function displaySummary($result, bool $isVerbose): void
    {
        $errors = count($result->getErrors());
        $warnings = count($result->getWarnings());
        $info = count($result->getInfo());
        $total = $errors + $warnings + $info;

        $this->line('');
        $this->components->info('Validation Summary');

        if ($total === 0) {
            $this->components->info('✅ No validation issues found - specification is valid!');
        } else {
            if ($errors > 0) {
                $this->components->error("❌ {$errors} error(s) found");
            }
            if ($warnings > 0) {
                $this->components->warn("⚠️  {$warnings} warning(s) found");
            }
            if ($info > 0 && $isVerbose) {
                $this->components->info("ℹ️  {$info} info message(s)");
            }

            if ($errors === 0) {
                $this->components->info('✅ Specification is valid (warnings/info only)');
            } else {
                $this->components->error('❌ Specification has validation errors');
            }
        }
    }
}
