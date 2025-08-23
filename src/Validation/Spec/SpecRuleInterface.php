<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Validation\Spec;

use Bambamboole\OpenApi\Objects\OpenApiDocument;

interface SpecRuleInterface
{
    /**
     * @param  \Closure(string,string,?ValidationSeverity):bool  $fail
     */
    public function validate(OpenApiDocument $document, \Closure $fail): void;
}
