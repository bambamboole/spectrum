<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class Security extends OpenApiObject
{
    public function __construct(
        /** @var array<string, array<string>> */
        public array $requirements,
    ) {}

    public static function rules(): array
    {
        return [
            '*' => ['array'],
        ];
    }
}
