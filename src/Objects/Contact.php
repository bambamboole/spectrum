<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class Contact extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'filled'],
            'email' => ['sometimes', 'email'],
            'url' => ['sometimes', 'url'],
        ];
    }

    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $url = null,
    ) {}
}
