<?php declare(strict_types=1);

namespace App\Validation\Spec;

use App\Objects\OpenApiDocument;

interface SpecRuleInterface
{
    /**
     * @param  \Closure(string,string): ValidationError  $fail
     */
    public function validate(OpenApiDocument $document, \Closure $fail): void;
}
