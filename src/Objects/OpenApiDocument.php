<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\ReferenceResolver;
use Bambamboole\OpenApi\Validation\Rules\Semver;
use Bambamboole\OpenApi\Validation\Validator;

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
        /** @var array<string, PathItem> */
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

    public static function fromArray(array $data): self
    {
        // Initialize the ReferenceResolver with the full document before parsing
        ReferenceResolver::initialize($data);

        try {
            $data = ReferenceResolver::resolveRef($data);
            Validator::validate($data, self::rules());

            $info = Info::fromArray($data['info'], 'info');
            $components = Components::fromArray($data['components'] ?? [], 'components');
            $security = Security::multiple($data['security'] ?? [], 'security');
            $servers = Server::multiple($data['servers'] ?? [], 'servers');
            $tags = Tag::multiple($data['tags'] ?? [], 'tags');
            $externalDocs = isset($data['externalDocs']) ? ExternalDocs::fromArray($data['externalDocs']) : null;

            return new OpenApiDocument(
                openapi: $data['openapi'],
                info: $info,
                paths: PathItem::multiple($data['paths'], 'paths'),
                components: $components,
                security: $security,
                tags: $tags,
                servers: $servers,
                externalDocs: $externalDocs,
            );
        } finally {
            // Clear the ReferenceResolver instance after parsing
            ReferenceResolver::clear();
        }
    }
}
