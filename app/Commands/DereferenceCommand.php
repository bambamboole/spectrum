<?php declare(strict_types=1);

namespace App\Commands;

use App\OpenApiParser;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Yaml\Yaml;

class DereferenceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dereference
                            {path : Path to the OpenAPI specification file}
                            {--format= : Output format (json, yaml) - defaults to input format}
                            {--output= : Output file path (default: stdout)}';

    protected $aliases = ['deref'];
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dereference all $ref links in an OpenAPI specification';

    /**
     * Execute the console command.
     */
    public function handle(OpenApiParser $parser)
    {
        $path = $this->argument('path');
        $format = $this->option('format');
        $outputFile = $this->option('output');
        $isStdout = ! $outputFile;

        // Validate arguments
        if (! file_exists($path)) {
            $this->components->error("File not found: {$path}");

            return self::FAILURE;
        }

        // Determine format - default to input file format
        if (! $format) {
            $format = $this->detectInputFormat($path);
        }

        if (! in_array($format, ['json', 'yaml'])) {
            $this->components->error("Invalid format '{$format}'. Valid formats: json, yaml");

            return self::FAILURE;
        }

        try {
            // Show progress only if not outputting to stdout
            if (! $isStdout) {
                $this->components->info("Dereferencing OpenAPI specification: {$path}");
                $this->components->info('Parsing and resolving all references...');
            }

            // Parse the document (this automatically dereferences all $ref links)
            $document = $parser->parseFile($path);

            // Convert document back to array format for output
            $dereferencedData = $this->documentToArray($document);

            // Format output
            $output = $this->formatOutput($dereferencedData, $format);

            // Output results
            if ($outputFile) {
                file_put_contents($outputFile, $output);
                $this->components->info("Dereferenced specification written to: {$outputFile}");
            } else {
                // Output to stdout with no additional messages
                $this->line($output);
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            if (! $isStdout) {
                $this->components->error("Dereferencing failed: {$e->getMessage()}");
                if ($this->option('verbose')) {
                    $this->line($e->getTraceAsString());
                }
            } else {
                // For stdout, just exit with error code
                return self::FAILURE;
            }

            return self::FAILURE;
        }
    }

    /**
     * Detect input file format based on extension.
     */
    private function detectInputFormat(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'json' => 'json',
            'yaml', 'yml' => 'yaml',
            default => 'yaml' // Default to YAML if unknown
        };
    }

    /**
     * Convert OpenAPI document object back to array.
     */
    private function documentToArray($document): array
    {
        // Use reflection to convert the readonly object back to array format
        // This is a simplified approach - in a full implementation you'd want
        // a proper serialization method on the document classes
        return $this->objectToArray($document);
    }

    /**
     * Recursively convert objects to arrays.
     */
    private function objectToArray($obj): mixed
    {
        if (is_object($obj)) {
            $reflection = new \ReflectionObject($obj);
            $array = [];

            foreach ($reflection->getProperties() as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($obj);

                // Skip null values and empty arrays/collections
                if ($value === null || (is_array($value) && empty($value))) {
                    continue;
                }

                // Convert property name from camelCase to snake_case for certain OpenAPI fields
                $propertyName = $this->convertPropertyName($property->getName());

                $convertedValue = $this->objectToArray($value);

                // Skip empty converted values
                if (! $this->isEmpty($convertedValue)) {
                    $array[$propertyName] = $convertedValue;
                }
            }

            return $array;
        }

        if (is_array($obj)) {
            $result = [];
            foreach ($obj as $key => $value) {
                $convertedValue = $this->objectToArray($value);
                if (! $this->isEmpty($convertedValue)) {
                    $result[$key] = $convertedValue;
                }
            }

            return $result;
        }

        return $obj;
    }

    /**
     * Convert property names to OpenAPI format.
     */
    private function convertPropertyName(string $propertyName): string
    {
        // Handle special OpenAPI property mappings
        return match ($propertyName) {
            'x' => 'x-extensions', // This will be filtered out anyway as it's usually empty
            default => $propertyName
        };
    }

    /**
     * Check if a value is considered empty for OpenAPI output.
     */
    private function isEmpty($value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        return false;
    }

    /**
     * Format output as JSON or YAML.
     */
    private function formatOutput(array $data, string $format): string
    {
        return match ($format) {
            'json' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            'yaml' => Yaml::dump($data, 6, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }
}
