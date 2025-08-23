<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Validation\Validator;

readonly class Security extends OpenApiObject
{
    public function __construct(
        /** @var array<string, array<string>> */
        public array $requirements,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function rules(): array
    {
        return [
            '*' => ['array'],
        ];
    }

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        return new Security(
            requirements: $data,
            x: self::extractX($data),
        );
    }
}
