<?php declare(strict_types=1);

namespace App\Commands;

use App\OpenApiParser;
use App\Validation\Spec\RulesetLoader;
use App\Validation\Validator;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ValidateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'validate {path?} {--ruleset=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(OpenApiParser $parser, RulesetLoader $rulesetLoader)
    {
        if (! $path = $this->argument('path')) {
            $this->components->error('path argument is required');

            return self::FAILURE;
        }

        $document = $parser->parseFile($path);
        if ($this->option('ruleset')) {
            $ruleset = $rulesetLoader->loadFromFile($this->option('ruleset'));
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
                        'enabled' => false, // This rule should be skipped
                    ],
                ],
            ]);
        }
        $result = Validator::validateDocument($document, $ruleset);

        if ($result->hasInfo()) {
            $this->components->info('Severity: INFO');
            $this->table(['Path', 'message'], array_map(fn ($error) => [$error->path, $error->message], $result->getInfo()));
        }
        if ($result->hasWarnings()) {
            $this->components->info('Severity: WARNING');
            $this->table(['Path', 'message'], array_map(fn ($error) => [$error->path, $error->message], $result->getWarnings()));
        }
        if ($result->hasErrors()) {
            $this->components->info('Severity: ERROR');
            $this->table(['Path', 'message'], array_map(fn ($error) => [$error->path, $error->message], $result->getErrors()));
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
