<?php declare(strict_types=1);

namespace App\Validation\Spec;

enum ValidationSeverity: string
{
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';
}
