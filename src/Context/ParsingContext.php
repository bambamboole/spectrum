<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Context;

use Bambamboole\OpenApi\Services\ReferenceResolver;
use Bambamboole\OpenApi\Validation\ValidatorFactory;
use Illuminate\Validation\Factory as LaravelValidatorFactory;

readonly class ParsingContext
{
    public function __construct(
        public ReferenceResolver $referenceResolver,
        public LaravelValidatorFactory $validator,
        public array $document,
    ) {}

    public static function fromDocument(array $document): self
    {
        return new self(
            referenceResolver: new ReferenceResolver($document),
            validator: ValidatorFactory::create(),
            document: $document,
        );
    }
}
