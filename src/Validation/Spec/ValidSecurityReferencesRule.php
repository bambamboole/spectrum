<?php declare(strict_types=1);
namespace Bambamboole\OpenApi\Validation\Spec;

use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Objects\Security;

class ValidSecurityReferencesRule implements SpecRuleInterface
{
    public function validate(OpenApiDocument $document, \Closure $fail): void
    {
        $availableSchemes = array_keys($document->components->securitySchemes);

        // Validate global security requirements
        $this->validateSecurityRequirements($document->security, $availableSchemes, 'security', $fail);

        // Validate operation-level security requirements
        foreach ($document->paths as $path => $pathItem) {
            $operations = $pathItem->getOperations();
            foreach ($operations as $method => $operation) {
                if ($operation->security !== null) {
                    $this->validateSecurityRequirements(
                        $operation->security,
                        $availableSchemes,
                        "paths.{$path}.{$method}.security",
                        $fail
                    );
                }
            }
        }
    }

    /**
     * @param  array<Security>  $securityRequirements
     * @param  array<string>  $availableSchemes
     */
    private function validateSecurityRequirements(array $securityRequirements, array $availableSchemes, string $path, \Closure $fail): void
    {
        foreach ($securityRequirements as $index => $securityRequirement) {
            foreach (array_keys($securityRequirement->requirements) as $schemeName) {
                if (! in_array($schemeName, $availableSchemes, true)) {
                    $fail('Security requirement references undefined security scheme. Available schemes: '.implode(', ', $availableSchemes), "{$path}.{$index}.{$schemeName}");
                }
            }
        }
    }
}
