<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\Validation\Rules\Semver;

readonly class OpenApiDocument extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'openapi' => ['required', 'string', new Semver('3.0.0')],
            'paths' => ['present', 'array'],
        ];
    }

    public function __construct(
        public string $openapi,
        public Info $info,
        public array $paths,
        public Components $components,
        /** @var Security[] */
        public array $security = [],
        /** @var Tag[] */
        public array $tags = [],
        /** @var Server[] */
        public array $servers = [],
        public ?ExternalDocs $externalDocs = null,
    ) {}
}
