<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Context;

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\OpenApiObject;
use Bambamboole\OpenApi\Services\ReferenceResolver;
use Bambamboole\OpenApi\Validation\ValidatorFactory;

readonly class ParsingContext
{
    public function __construct(
        public ReferenceResolver $referenceResolver,
        public ValidatorFactory $validator,
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

    public function validate(array $data, string $objectName, string $keyPrefix = ''): void
    {
        if (! class_exists($objectName) || ! is_subclass_of($objectName, OpenApiObject::class)) {
            throw new \InvalidArgumentException("Class {$objectName} does not implement ".OpenApiObject::class);
        }
        $rules = $objectName::rules();
        $messages = $objectName::messages();

        $validator = $this->validator->make($data, $rules, $messages);

        if ($validator->fails()) {
            $prefix = empty($keyPrefix) ? '' : "{$keyPrefix}.";
            $messages = collect($validator->errors()->toArray())
                ->mapWithKeys(fn ($errors, $field) => [ltrim("{$prefix}{$field}", '.') => $errors])
                ->toArray();

            throw ParseException::withMessages($messages);
        }
    }

    public function key(string $prefix, string $key): string
    {
        return $prefix === '' ? $key : $prefix.'.'.$key;
    }
}
