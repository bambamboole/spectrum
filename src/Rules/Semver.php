<?php declare(strict_types=1);
namespace Bambamboole\OpenApi\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Semver implements ValidationRule
{
    public function __construct(
        private readonly string $minVersion,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! $this->isValidSemver($value)) {
            $fail('The :attribute must be a valid semantic version (e.g., 3.0.0, 3.1.1).');

            return;
        }

        if (! $this->meetsMinimumVersion($value, $this->minVersion)) {
            $fail("The :attribute must be at least version {$this->minVersion}.");
        }
    }

    private function isValidSemver(string $version): bool
    {
        return (bool) preg_match('/^\d+\.\d+\.\d+(?:-[0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*)?(?:\+[0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*)?$/', $version);
    }

    private function meetsMinimumVersion(string $version, string $minVersion): bool
    {
        return version_compare($version, $minVersion, '>=');
    }
}
