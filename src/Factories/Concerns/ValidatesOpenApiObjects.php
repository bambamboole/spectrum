<?php declare(strict_types=1);
namespace Bambamboole\OpenApi\Factories\Concerns;

use Bambamboole\OpenApi\Context\ParsingContext;
use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\OpenApiObject;

trait ValidatesOpenApiObjects
{
    protected ParsingContext $context;

    protected function validate(array $data, string $objectName, string $keyPrefix = ''): void
    {
        if (! class_exists($objectName) || ! is_subclass_of($objectName, OpenApiObject::class)) {
            throw new \InvalidArgumentException("Class {$objectName} does not implement ".OpenApiObject::class);
        }
        $rules = $objectName::rules();
        $messages = $objectName::messages();

        $validator = $this->context->validator->make($data, $rules, $messages);

        if ($validator->fails()) {
            $prefix = empty($keyPrefix) ? '' : "{$keyPrefix}.";
            $messages = collect($validator->errors()->toArray())
                ->mapWithKeys(fn ($errors, $field) => ["{$prefix}{$field}" => $errors])
                ->toArray();

            throw ParseException::withMessages($messages);
        }
    }
}
