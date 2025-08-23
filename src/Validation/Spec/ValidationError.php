<?php declare(strict_types=1);
namespace Bambamboole\OpenApi\Validation\Spec;

readonly class ValidationError
{
    public function __construct(
        public string $message,
        public string $path,
        public ValidationSeverity $severity = ValidationSeverity::ERROR,
    ) {}

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'path' => $this->path,
            'severity' => $this->severity->value,
        ];
    }
}
