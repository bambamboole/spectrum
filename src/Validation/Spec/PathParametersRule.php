<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Validation\Spec;

use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Illuminate\Support\Str;

#[RuleAttribute(
    name: 'path-parameters',
    description: 'Validates that path parameters are correctly defined and consistent',
    defaultSeverity: ValidationSeverity::ERROR
)]
class PathParametersRule implements SpecRuleInterface
{
    public function validate(OpenApiDocument $document, \Closure $fail): void
    {
        foreach ($document->paths as $path => $pathItem) {
            $pathParameters = $this->extractPathParameters($path);
            $pathLevelParameters = $pathItem->parameters ?? [];

            $operations = $pathItem->getOperations();
            foreach ($operations as $method => $operation) {
                // Merge path-level and operation-level parameters
                $allParameters = array_merge($pathLevelParameters, $operation->parameters ?? []);
                $this->validateOperationPathParameters($pathParameters, $allParameters, $path, $method, $fail);
            }
        }
    }

    private function extractPathParameters(string $path): array
    {
        return Str::matchAll('/\{([^}]+)\}/', $path)->all();
    }

    private function validateOperationPathParameters(array $pathParameters, array $parameters, string $path, string $method, \Closure $fail): void
    {
        $definedPathParams = [];

        // Check all defined parameters
        foreach ($parameters as $parameter) {
            if ($parameter->in === 'path') {
                $definedPathParams[] = $parameter->name;

                // Path parameters must be required
                if (! $parameter->required) {
                    $fail("Path parameter '{$parameter->name}' must be required", "paths.{$path}.{$method}.parameters.{$parameter->name}.required");
                }
            }
        }

        // Check that all path template parameters are defined
        foreach ($pathParameters as $pathParam) {
            if (! in_array($pathParam, $definedPathParams, true)) {
                $fail("Path parameter '{$pathParam}' found in path template but not defined in parameters", "paths.{$path}.{$method}.parameters");
            }
        }

        // Check for unused path parameter definitions
        foreach ($definedPathParams as $definedParam) {
            if (! in_array($definedParam, $pathParameters, true)) {
                $fail("Path parameter '{$definedParam}' is defined but not used in path template", "paths.{$path}.{$method}.parameters.{$definedParam}");
            }
        }
    }
}
