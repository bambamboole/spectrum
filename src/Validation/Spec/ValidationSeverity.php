<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Validation\Spec;

enum ValidationSeverity: string
{
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';

    public function getLabel(): string
    {
        return match ($this) {
            self::ERROR => 'Error',
            self::WARNING => 'Warning',
            self::INFO => 'Info',
        };
    }

    public function isBlocking(): bool
    {
        return $this === self::ERROR;
    }
}
