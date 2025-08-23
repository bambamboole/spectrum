<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Validation\Spec;

class ValidationResult
{
    /** @param array<ValidationError> $errors */
    public function __construct(private array $errors = []) {}

    public function add(ValidationError $error): void
    {
        $this->errors[] = $error;
    }

    public function all(): array
    {
        return $this->errors;
    }

    public function getErrors(?string $path = null): array
    {
        $errors = array_filter($this->errors, fn (ValidationError $error) => $error->severity === ValidationSeverity::ERROR);

        if ($path === null) {
            return $errors;
        }

        return array_filter($errors, fn (ValidationError $error) => $error->path === $path);
    }

    public function getWarnings(): array
    {
        return array_filter($this->errors, fn (ValidationError $error) => $error->severity === ValidationSeverity::WARNING);
    }

    public function getInfo(): array
    {
        return array_filter($this->errors, fn (ValidationError $error) => $error->severity === ValidationSeverity::INFO);
    }
}
