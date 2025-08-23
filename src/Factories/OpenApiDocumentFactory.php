<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Factories;

use Bambamboole\OpenApi\Context\ParsingContext;
use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Factories\Concerns\ValidatesOpenApiObjects;
use Bambamboole\OpenApi\Objects\Components;
use Bambamboole\OpenApi\Objects\Contact;
use Bambamboole\OpenApi\Objects\ExternalDocs;
use Bambamboole\OpenApi\Objects\Info;
use Bambamboole\OpenApi\Objects\License;
use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Objects\Schema;
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
        $components = $this->createComponents($data['components'] ?? [], $documentSecurity->securitySchemes);

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

    private function createSchema(array $data): Schema
    {
        // Handle $ref resolution first
        if (isset($data['$ref'])) {
            $resolvedData = $this->context->referenceResolver->resolve($data['$ref']);
            // If the resolved data also has a $ref, that's a problem with the schema design
            // The resolver should have already resolved it completely
            if (is_array($resolvedData)) {
                return $this->createSchema($resolvedData);
            }
            // If it's not an array, something went wrong
            throw new \InvalidArgumentException('Resolved reference must be an array');
        }

        // Advanced schema validation with conditional rules
        $validator = $this->context->validator->make($data, [
            'type' => ['sometimes', 'string', 'in:string,number,integer,boolean,array,object,null'],
            'format' => ['sometimes', 'string', 'filled'],
            'title' => ['sometimes', 'string', 'filled'],
            'description' => ['sometimes', 'string', 'filled'],
            '$ref' => ['sometimes', 'string', 'filled'],

            // String constraints
            'minLength' => ['sometimes', 'integer', 'min:0'],
            'maxLength' => ['sometimes', 'integer', 'min:0', 'gte:minLength'],
            'pattern' => ['sometimes', 'string', 'filled'],

            // Numeric constraints
            'minimum' => ['sometimes', 'numeric'],
            'maximum' => ['sometimes', 'numeric', 'gte:minimum'],
            'exclusiveMinimum' => ['sometimes', 'boolean'],
            'exclusiveMaximum' => ['sometimes', 'boolean'],
            'multipleOf' => ['sometimes', 'numeric', 'gt:0'],

            // Array constraints
            'minItems' => ['sometimes', 'integer', 'min:0'],
            'maxItems' => ['sometimes', 'integer', 'min:0', 'gte:minItems'],
            'uniqueItems' => ['sometimes', 'boolean'],
            'items' => ['sometimes', 'array'],

            // Object constraints
            'minProperties' => ['sometimes', 'integer', 'min:0'],
            'maxProperties' => ['sometimes', 'integer', 'min:0', 'gte:minProperties'],
            'required' => ['sometimes', 'array'],
            'properties' => ['sometimes', 'array'],
            'additionalProperties' => ['sometimes'],

            // Enumeration
            'enum' => ['sometimes', 'array', 'min:1'],

            // Composition keywords
            'allOf' => ['sometimes', 'array', 'min:1'],
            'anyOf' => ['sometimes', 'array', 'min:1'],
            'oneOf' => ['sometimes', 'array', 'min:1'],
            'not' => ['sometimes', 'array'],
        ]);

        if ($validator->fails()) {
            throw ParseException::withMessages($validator->errors()->toArray());
        }

        return new Schema(
            type: $data['type'] ?? null,
            format: $data['format'] ?? null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            default: $data['default'] ?? null,
            example: $data['example'] ?? null,
            minLength: $data['minLength'] ?? null,
            maxLength: $data['maxLength'] ?? null,
            pattern: $data['pattern'] ?? null,
            minimum: $data['minimum'] ?? null,
            maximum: $data['maximum'] ?? null,
            exclusiveMinimum: $data['exclusiveMinimum'] ?? null,
            exclusiveMaximum: $data['exclusiveMaximum'] ?? null,
            multipleOf: $data['multipleOf'] ?? null,
            minItems: $data['minItems'] ?? null,
            maxItems: $data['maxItems'] ?? null,
            uniqueItems: $data['uniqueItems'] ?? null,
            items: isset($data['items']) ? $this->createSchema($data['items']) : null,
            properties: $this->createSchemaProperties($data['properties'] ?? null),
            required: $data['required'] ?? null,
            additionalProperties: $this->createAdditionalProperties($data['additionalProperties'] ?? null),
            minProperties: $data['minProperties'] ?? null,
            maxProperties: $data['maxProperties'] ?? null,
            enum: $data['enum'] ?? null,
            allOf: $this->createSchemaArray($data['allOf'] ?? null),
            anyOf: $this->createSchemaArray($data['anyOf'] ?? null),
            oneOf: $this->createSchemaArray($data['oneOf'] ?? null),
            not: isset($data['not']) ? $this->createSchema($data['not']) : null,
            ref: $data['$ref'] ?? null,
        );
    }

    protected function createComponents(array $data, array $securitySchemes): Components
    {
        return new Components(
            schemas: $this->createSchemas($data['schemas'] ?? []),
            responses: $data['responses'] ?? [],
            parameters: $data['parameters'] ?? [],
            examples: $data['examples'] ?? [],
            requestBodies: $data['requestBodies'] ?? [],
            headers: $data['headers'] ?? [],
            securitySchemes: $securitySchemes,
            links: $data['links'] ?? [],
            callbacks: $data['callbacks'] ?? [],
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

    private function createSchemas(array $schemas): array
    {
        $parsed = [];
        foreach ($schemas as $key => $schema) {
            $parsed[$key] = $this->createSchema($schema);
        }

        return $parsed;
    }

    private function createSchemaProperties(?array $properties): ?array
    {
        if ($properties === null) {
            return null;
        }

        $parsed = [];
        foreach ($properties as $key => $property) {
            $parsed[$key] = $this->createSchema($property);
        }

        return $parsed;
    }

    private function createAdditionalProperties(mixed $additionalProperties): bool|Schema|null
    {
        if ($additionalProperties === null) {
            return null;
        }

        if (is_bool($additionalProperties)) {
            return $additionalProperties;
        }

        if (is_array($additionalProperties)) {
            return $this->createSchema($additionalProperties);
        }

        return null;
    }

    private function createSchemaArray(?array $schemas): ?array
    {
        if ($schemas === null) {
            return null;
        }

        return array_map(fn ($schema) => $this->createSchema($schema), $schemas);
    }
}
