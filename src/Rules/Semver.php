<?php declare(strict_types=1);
namespace Bambamboole\OpenApi\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Semver implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // TODO: Implement validate() method.
    }
}
