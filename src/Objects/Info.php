<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Exceptions\ParseException;

readonly class Info
{
    public function __construct(
        public string $title,
        public string $version,
        public ?string $description = null,
        public ?string $termsOfService = null,
        public ?Contact $contact = null,
        public ?License $license = null,
    ) {}

    public static function fromArray(array $data): self
    {
        self::validateFields($data);

        return new self(
            title: $data['title'],
            version: $data['version'],
            description: $data['description'] ?? null,
            termsOfService: $data['termsOfService'] ?? null,
            contact: isset($data['contact']) ? Contact::fromArray($data['contact']) : null,
            license: isset($data['license']) ? License::fromArray($data['license']) : null,
        );
    }

    private static function validateFields(array $data): void
    {
        $requiredFields = ['title', 'version'];

        foreach ($requiredFields as $field) {
            if (! isset($data[$field])) {
                throw new ParseException("Missing required field: {$field}");
            }
        }

        if (isset($data['termsOfService']) && ! filter_var($data['termsOfService'], FILTER_VALIDATE_URL)) {
            throw new ParseException('termsOfService must be a valid URL');
        }
    }
}
