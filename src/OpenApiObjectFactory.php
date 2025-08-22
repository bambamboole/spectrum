<?php declare(strict_types=1);

namespace Bambamboole\OpenApi;

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\Components;
use Bambamboole\OpenApi\Objects\Contact;
use Bambamboole\OpenApi\Objects\ExternalDocs;
use Bambamboole\OpenApi\Objects\Info;
use Bambamboole\OpenApi\Objects\License;
use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Objects\OpenApiObject;
use Bambamboole\OpenApi\Objects\Schema;
use Bambamboole\OpenApi\Objects\SecurityScheme;
use Bambamboole\OpenApi\Objects\Server;
use Bambamboole\OpenApi\Objects\Tag;
use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\Factory as ValidatorFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

use function collect;

class OpenApiObjectFactory
{
    public function __construct(
        protected readonly ValidatorFactory $validator,
    ) {}

    public static function make(): self
    {
        $loader = new FileLoader(new Filesystem, dirname(__DIR__).'/lang');
        $translator = new Translator($loader, 'en');
        $validatorFactory = new Factory($translator, new Container);

        return new self($validatorFactory);
    }

    public function createDocument(array $data): OpenApiDocument
    {
        $validator = $this->validator->make($data, OpenApiDocument::rules(), OpenApiDocument::messages());
        if ($validator->fails()) {
            throw ParseException::withMessages($validator->errors()->toArray());
        }

        $info = $this->createInfo($data['info'], 'info');
        $components = $this->createComponents($data['components'] ?? []);

        if (isset($data['security'])) {
            $this->validateSecurity($data['security'], $components->securitySchemes);
        }

        $servers = collect($data['servers'] ?? [])->map(fn ($server, $i) => $this->createServer($server, "servers.{$i}"))->all();
        $tags = collect($data['tags'] ?? [])->map(fn ($tag, $i) => $this->createTag($tag, "tags.{$i}"))->all();
        $externalDocs = isset($data['externalDocs']) ? $this->createExternalDocs($data['externalDocs']) : null;

        return new OpenApiDocument(
            openapi: $data['openapi'],
            info: $info,
            paths: $data['paths'],
            components: $components,
            security: $data['security'] ?? [],
            tags: $tags,
            servers: $servers,
            externalDocs: $externalDocs,
        );
    }

    public function createInfo(array $data, string $keyPrefix = ''): Info
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

    public function createContact(array $data, string $keyPrefix = ''): Contact
    {
        $this->validate($data, Contact::class, $keyPrefix);

        return new Contact(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            url: $data['url'] ?? null,
        );
    }

    public function createLicense(array $data, string $keyPrefix = ''): License
    {
        $this->validate($data, License::class, $keyPrefix);

        return new License(
            name: $data['name'],
            url: $data['url'] ?? null,
        );
    }

    public function createSchema(array $data): Schema
    {
        // Advanced schema validation with conditional rules
        $validator = $this->validator->make($data, [
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

    public function createComponents(array $data): Components
    {
        $securitySchemes = collect($data['securitySchemes'] ?? [])
            ->map(fn ($securityScheme, $key) => $this->createSecurityScheme($securityScheme, "components.securitySchemes.{$key}"))
            ->all();

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

    public function createServer(array $data, string $keyPrefix = ''): Server
    {
        $this->validate($data, Server::class, $keyPrefix);

        return new Server(
            url: $data['url'],
            description: $data['description'] ?? null,
            variables: $data['variables'] ?? null,
        );
    }

    public function createExternalDocs(array $data, string $keyPrefix = ''): ExternalDocs
    {
        $this->validate($data, ExternalDocs::class, $keyPrefix);

        return new ExternalDocs(
            url: $data['url'],
            description: $data['description'] ?? null,
        );
    }

    public function createTag(array $data, string $keyPrefix = ''): Tag
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

    public function createSecurityScheme(array $data, string $keyPrefix = ''): SecurityScheme
    {
        $this->validate($data, SecurityScheme::class, $keyPrefix);

        return new SecurityScheme(
            type: $data['type'],
            description: $data['description'] ?? null,
            name: $data['name'] ?? null,
            in: $data['in'] ?? null,
            scheme: $data['scheme'] ?? null,
            bearerFormat: $data['bearerFormat'] ?? null,
            flows: $data['flows'] ?? null,
            openIdConnectUrl: $data['openIdConnectUrl'] ?? null,
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

    protected function validate(array $data, string|array $objectNameOrRules, string $keyPrefix = ''): void
    {
        $rules = [];
        $messages = [];
        if (is_string($objectNameOrRules)) {
            $objectName = $objectNameOrRules;
            if (! class_exists($objectName) || ! is_subclass_of($objectName, OpenApiObject::class)) {
                throw new \InvalidArgumentException("Class {$objectName} does not implement ".OpenApiObject::class);
            }
            $rules = $objectName::rules();
            $messages = $objectName::messages();
        }

        $validator = $this->validator->make($data, $rules, $messages);

        if ($validator->fails()) {
            $prefix = empty($keyPrefix) ? '' : "{$keyPrefix}.";
            $messages = collect($validator->errors()->toArray())
                ->mapWithKeys(fn ($errors, $field) => ["{$prefix}{$field}" => $errors])
                ->toArray();

            throw ParseException::withMessages($messages);
        }
    }

    private function validateSecurity(array $security, array $securitySchemes): void
    {
        $schemeNames = array_keys($securitySchemes);
        $errors = [];

        foreach ($security as $index => $securityRequirement) {
            if (! is_array($securityRequirement)) {
                $errors["security.{$index}"] = ['Security requirement must be an object.'];

                continue;
            }

            foreach (array_keys($securityRequirement) as $schemeName) {
                if (! in_array($schemeName, $schemeNames, true)) {
                    $errors["security.{$index}.{$schemeName}"] = [
                        "Security scheme '{$schemeName}' is not defined in components.securitySchemes.",
                    ];
                }
            }
        }

        if (! empty($errors)) {
            throw ParseException::withMessages($errors);
        }
    }
}
