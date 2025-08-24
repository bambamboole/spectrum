<?php declare(strict_types=1);

namespace App\Validation\Spec;

use App\Objects\OpenApiDocument;

#[RuleAttribute(
    name: 'response-codes',
    description: 'Validates HTTP response codes follow standards',
    defaultSeverity: ValidationSeverity::WARNING
)]
class ResponseCodeRule implements SpecRuleInterface
{
    private const VALID_STATUS_CODES = [
        // 1xx Informational
        100, 101, 102, 103,
        // 2xx Success
        200, 201, 202, 203, 204, 205, 206, 207, 208, 226,
        // 3xx Redirection
        300, 301, 302, 303, 304, 305, 307, 308,
        // 4xx Client Error
        400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417, 418, 421, 422, 423, 424, 425, 426, 428, 429, 431, 451,
        // 5xx Server Error
        500, 501, 502, 503, 504, 505, 506, 507, 508, 510, 511,
    ];

    public function validate(OpenApiDocument $document, \Closure $fail): void
    {
        foreach ($document->paths as $path => $pathItem) {
            $operations = $pathItem->getOperations();
            foreach ($operations as $method => $operation) {
                if ($operation->responses === null) {
                    continue;
                }

                $this->validateResponseCodes($operation->responses, $path, $method, $fail);
                $this->checkCommonResponseCodes($operation->responses, $path, $method, $fail);
            }
        }
    }

    private function validateResponseCodes(array $responses, string $path, string $method, \Closure $fail): void
    {
        foreach (array_keys($responses) as $statusCode) {
            if ($statusCode === 'default') {
                continue; // 'default' is a special valid response code
            }

            // Check if it's a numeric status code
            if (! is_numeric($statusCode)) {
                $fail("Response code '{$statusCode}' must be a valid HTTP status code or 'default'", "paths.{$path}.{$method}.responses.{$statusCode}");

                continue;
            }

            $code = (int) $statusCode;

            // Check if it's in valid range
            if ($code < 100 || $code > 599) {
                $fail("Response code '{$statusCode}' must be between 100-599", "paths.{$path}.{$method}.responses.{$statusCode}");

                continue;
            }

            // Check if it's a known HTTP status code
            if (! in_array($code, self::VALID_STATUS_CODES, true)) {
                $fail("Response code '{$statusCode}' is not a standard HTTP status code", "paths.{$path}.{$method}.responses.{$statusCode}", ValidationSeverity::WARNING);
            }
        }
    }

    private function checkCommonResponseCodes(array $responses, string $path, string $method, \Closure $fail): void
    {
        $statusCodes = array_keys($responses);
        $hasSuccessResponse = false;
        $hasClientErrorResponse = false;
        $hasServerErrorResponse = false;

        foreach ($statusCodes as $statusCode) {
            if ($statusCode === 'default') {
                continue;
            }

            $codeStr = (string) $statusCode;

            if (str_starts_with($codeStr, '2')) {
                $hasSuccessResponse = true;
            }

            if (str_starts_with($codeStr, '4')) {
                $hasClientErrorResponse = true;
            }

            if (str_starts_with($codeStr, '5')) {
                $hasServerErrorResponse = true;
            }
        }

        // Suggest common error responses for operations that might need them
        if ($hasSuccessResponse && ! $hasClientErrorResponse && ! in_array('default', $statusCodes, true)) {
            if (in_array($method, ['post', 'put', 'patch', 'delete'], true)) {
                $fail("Consider adding 4xx error responses for {$method} operations", "paths.{$path}.{$method}.responses", ValidationSeverity::INFO);
            }
        }

        if ($hasSuccessResponse && ! $hasServerErrorResponse && ! in_array('default', $statusCodes, true)) {
            $fail("Consider adding a 5xx server error response or 'default' response", "paths.{$path}.{$method}.responses", ValidationSeverity::INFO);
        }
    }
}
