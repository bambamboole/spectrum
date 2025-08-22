<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

readonly class Info extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'title' => ['required', 'string', 'filled'],
            'version' => ['required', 'string', 'filled'],
            'description' => ['sometimes', 'string', 'filled'],
            'termsOfService' => ['sometimes', 'url'],
        ];
    }

    public function __construct(
        public string $title,
        public string $version,
        public ?string $description = null,
        public ?string $termsOfService = null,
        public ?Contact $contact = null,
        public ?License $license = null,
    ) {}
}
