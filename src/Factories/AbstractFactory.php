<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Factories;

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\OpenApiObject;
use Bambamboole\OpenApi\Validation\ValidatorFactory;

abstract class AbstractFactory
{
    public function __construct(
        protected readonly ValidatorFactory $validator,
    ) {}

    public static function create(): static
    {
        $validatorFactory = ValidatorFactory::create();

        return new static($validatorFactory);
    }

    protected function validate(array $data, string $objectName, string $keyPrefix = ''): void
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
                ->mapWithKeys(fn ($errors, $field) => ["{$prefix}{$field}" => $errors])
                ->toArray();

            throw ParseException::withMessages($messages);
        }
    }
}
