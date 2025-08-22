<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Exceptions\ParseException;

readonly class License
{
    public function __construct(
        public string $name,
        public ?string $url = null,
    ) {}

    public static function fromArray(array $data): self
    {
        self::validateFields($data);

        return new self(
            name: $data['name'],
            url: $data['url'] ?? null,
        );
    }

    private static function validateFields(array $data): void
    {
        if (! isset($data['name'])) {
            throw new ParseException('Missing required field: name');
        }

        if (isset($data['url']) && ! filter_var($data['url'], FILTER_VALIDATE_URL)) {
            throw new ParseException('url must be a valid URL');
        }
    }
}
