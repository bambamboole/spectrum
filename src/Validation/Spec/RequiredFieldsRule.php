<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Validation\Spec;

use Bambamboole\OpenApi\Objects\OpenApiDocument;

class RequiredFieldsRule implements SpecRuleInterface
{
    public function validate(OpenApiDocument $document, \Closure $fail): void
    {
        $this->validatePathsNotEmpty($document, $fail);
        $this->validateOperationsHaveSuccessResponse($document, $fail);
        $this->validateInfoDescription($document, $fail);
    }

    private function validatePathsNotEmpty(OpenApiDocument $document, \Closure $fail): void
    {
        if (empty($document->paths)) {
            $fail('Paths object should not be empty - API must define at least one path', 'paths', ValidationSeverity::WARNING);
        }
    }

    private function validateOperationsHaveSuccessResponse(OpenApiDocument $document, \Closure $fail): void
    {
        foreach ($document->paths as $path => $pathItem) {
            $operations = $pathItem->getOperations();
            foreach ($operations as $method => $operation) {
                if ($operation->responses === null) {
                    continue;
                }

                $hasSuccessResponse = false;
                foreach (array_keys($operation->responses) as $statusCode) {
                    if (is_string($statusCode) && str_starts_with($statusCode, '2')) {
                        $hasSuccessResponse = true;
                        break;
                    }
                    if (is_int($statusCode) && $statusCode >= 200 && $statusCode < 300) {
                        $hasSuccessResponse = true;
                        break;
                    }
                }

                if (! $hasSuccessResponse) {
                    $fail('Operation should define at least one success response (2xx)', "paths.{$path}.{$method}.responses", ValidationSeverity::WARNING);
                }
            }
        }
    }

    private function validateInfoDescription(OpenApiDocument $document, \Closure $fail): void
    {
        if (empty($document->info->description)) {
            $fail('Info description is recommended for better API documentation', 'info.description', ValidationSeverity::INFO);
        }
    }
}
