<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Factories;

use Bambamboole\OpenApi\Context\ParsingContext;
use Bambamboole\OpenApi\Factories\Concerns\ValidatesOpenApiObjects;
use Bambamboole\OpenApi\Objects\Contact;
use Bambamboole\OpenApi\Objects\ExternalDocs;
use Bambamboole\OpenApi\Objects\Info;
use Bambamboole\OpenApi\Objects\License;
use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Objects\Server;
use Bambamboole\OpenApi\Objects\Tag;

use function collect;

class OpenApiDocumentFactory
{
    use ValidatesOpenApiObjects;

    public function __construct(ParsingContext $context)
    {
        $this->context = $context;
    }

    public static function create(array $data): self
    {
        return new self(ParsingContext::fromDocument($data));
    }

    public function createDocument(): OpenApiDocument
    {
        $data = $this->context->document;
        $this->validate($data, OpenApiDocument::class);

        $documentSecurity = OpenApiSecurityFactory::create($this->context)->validateAndCreateDocumentSecurity($data);
        $info = $this->createInfo($data['info'], 'info');
        $components = ComponentsFactory::create($this->context)->createComponents($data['components'] ?? [], $documentSecurity->securitySchemes);

        $servers = collect($data['servers'] ?? [])->map(fn ($server, $i) => $this->createServer($server, "servers.{$i}"))->all();
        $tags = collect($data['tags'] ?? [])->map(fn ($tag, $i) => $this->createTag($tag, "tags.{$i}"))->all();
        $externalDocs = isset($data['externalDocs']) ? $this->createExternalDocs($data['externalDocs']) : null;

        return new OpenApiDocument(
            openapi: $data['openapi'],
            info: $info,
            paths: $data['paths'],
            components: $components,
            security: $documentSecurity->security,
            tags: $tags,
            servers: $servers,
            externalDocs: $externalDocs,
        );
    }

    private function createInfo(array $data, string $keyPrefix = ''): Info
    {
        $this->validate($data, Info::class, $keyPrefix);

        $contact = isset($data['contact']) ? $this->createContact($data['contact'], $keyPrefix.'.contact') : null;
        $license = isset($data['license']) ? $this->createLicense($data['license'], $keyPrefix.'.license') : null;

        return new Info(
            title: $data['title'],
            version: $data['version'],
            description: $data['description'] ?? null,
            termsOfService: $data['termsOfService'] ?? null,
            contact: $contact,
            license: $license,
        );
    }

    private function createContact(array $data, string $keyPrefix = ''): Contact
    {
        $this->validate($data, Contact::class, $keyPrefix);

        return new Contact(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            url: $data['url'] ?? null,
        );
    }

    private function createLicense(array $data, string $keyPrefix = ''): License
    {
        $this->validate($data, License::class, $keyPrefix);

        return new License(
            name: $data['name'],
            url: $data['url'] ?? null,
        );
    }

    private function createServer(array $data, string $keyPrefix = ''): Server
    {
        $this->validate($data, Server::class, $keyPrefix);

        return new Server(
            url: $data['url'],
            description: $data['description'] ?? null,
            variables: $data['variables'] ?? null,
        );
    }

    private function createExternalDocs(array $data, string $keyPrefix = ''): ExternalDocs
    {
        $this->validate($data, ExternalDocs::class, $keyPrefix);

        return new ExternalDocs(
            url: $data['url'],
            description: $data['description'] ?? null,
        );
    }

    private function createTag(array $data, string $keyPrefix = ''): Tag
    {
        $this->validate($data, Tag::class, $keyPrefix);

        $externalDocs = isset($data['externalDocs'])
            ? $this->createExternalDocs($data['externalDocs'], $keyPrefix)
            : null;

        return new Tag(
            name: $data['name'],
            description: $data['description'] ?? null,
            externalDocs: $externalDocs,
        );
    }
}
