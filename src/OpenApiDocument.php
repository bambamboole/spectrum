<?php declare(strict_types=1);

namespace Bambamboole\OpenApi;

use Bambamboole\OpenApi\Objects\Components;
use Bambamboole\OpenApi\Objects\Info;

readonly class OpenApiDocument
{
    public function __construct(
        public string $openapi,
        public Info $info,
        public array $paths,
        public Components $components,
        public array $security = [],
        public array $tags = [],
        public array $servers = [],
        public ?array $externalDocs = null,
    ) {}
}
