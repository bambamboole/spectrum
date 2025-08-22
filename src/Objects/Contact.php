<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Exceptions\ParseException;

readonly class Contact
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        self::validateFields($data);

        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            url: $data['url'] ?? null,
        );
    }

    private static function validateFields(array $data): void
    {
        if (isset($data['email']) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new ParseException('email must be a valid email address');
        }

        if (isset($data['url']) && ! filter_var($data['url'], FILTER_VALIDATE_URL)) {
            throw new ParseException('url must be a valid URL');
        }
    }
}
