<?php declare(strict_types=1);

namespace Bambamboole\OpenApi;

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\Components;
use Bambamboole\OpenApi\Objects\Contact;
use Bambamboole\OpenApi\Objects\Info;
use Bambamboole\OpenApi\Objects\License;
use Bambamboole\OpenApi\Objects\Schema;
use Illuminate\Contracts\Validation\Factory as ValidatorFactory;

class OpenApiObjectFactory
{
    public function __construct(
        protected readonly ValidatorFactory $validator,
    ) {}

    public function createDocument(array $data): OpenApiDocument
    {
        $this->validateDocument($data);

        return new OpenApiDocument(
            openapi: $data['openapi'],
            info: $this->createInfo($data['info']),
            paths: $data['paths'],
            components: $this->createComponents($data['components'] ?? []),
            security: $data['security'] ?? [],
            tags: $data['tags'] ?? [],
            servers: $data['servers'] ?? [],
            externalDocs: $data['externalDocs'] ?? null,
        );
    }

    public function createInfo(array $data): Info
    {
        // Validation for direct usage with sophisticated rules
        $validator = $this->validator->make($data, [
            'title' => ['required', 'string', 'filled'],
            'version' => ['required', 'string', 'filled'],
            'description' => ['sometimes', 'string', 'filled'],
            'termsOfService' => ['sometimes', 'url'],
            'contact' => ['sometimes', 'array'],
            'license' => ['sometimes', 'array'],

            // Nested validation for contact
            'contact.name' => ['sometimes', 'string', 'filled'],
            'contact.email' => ['sometimes', 'email'],
            'contact.url' => ['sometimes', 'url'],

            // Nested validation for license
            'license.name' => ['required_with:license', 'string', 'filled'],
            'license.url' => ['sometimes', 'url'],
        ]);

        if ($validator->fails()) {
            throw new ParseException('Info validation failed: '.$validator->errors()->first());
        }

        return new Info(
            title: $data['title'],
            version: $data['version'],
            description: $data['description'] ?? null,
            termsOfService: $data['termsOfService'] ?? null,
            contact: isset($data['contact']) ? $this->createContact($data['contact']) : null,
            license: isset($data['license']) ? $this->createLicense($data['license']) : null,
        );
    }

    public function createContact(array $data): Contact
    {
        // Validation for direct usage
        $validator = $this->validator->make($data, [
            'name' => ['sometimes', 'string', 'filled'],
            'email' => ['sometimes', 'email'],
            'url' => ['sometimes', 'url'],
        ]);

        if ($validator->fails()) {
            throw new ParseException('Contact validation failed: '.$validator->errors()->first());
        }

        return new Contact(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            url: $data['url'] ?? null,
        );
    }

    public function createLicense(array $data): License
    {
        // License requires name if present
        $validator = $this->validator->make($data, [
            'name' => ['required', 'string', 'filled'],
            'url' => ['sometimes', 'url'],
        ]);

        if ($validator->fails()) {
            throw new ParseException('License validation failed: '.$validator->errors()->first());
        }

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
            throw new ParseException('Schema validation failed: '.$validator->errors()->first());
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
        return new Components(
            schemas: $this->createSchemas($data['schemas'] ?? []),
            responses: $data['responses'] ?? [],
            parameters: $data['parameters'] ?? [],
            examples: $data['examples'] ?? [],
            requestBodies: $data['requestBodies'] ?? [],
            headers: $data['headers'] ?? [],
            securitySchemes: $data['securitySchemes'] ?? [],
            links: $data['links'] ?? [],
            callbacks: $data['callbacks'] ?? [],
        );
    }

    private function validateDocument(array $data): void
    {
        $validator = $this->validator->make($data, [
            // Root level - OpenAPI spec requirements
            'openapi' => ['required', 'string', 'regex:/^3\.[01]\.\d+$/'],
            'info' => ['required', 'array', 'bail'],
            'paths' => ['present', 'array'],
            'components' => ['sometimes', 'array'],
            'security' => ['sometimes', 'array'],
            'tags' => ['sometimes', 'array'],
            'servers' => ['sometimes', 'array'],
            'externalDocs' => ['sometimes', 'array'],

            // Info object validation
            'info.title' => ['required', 'string', 'filled'],
            'info.version' => ['required', 'string', 'filled'],
            'info.description' => ['sometimes', 'string', 'filled'],
            'info.termsOfService' => ['sometimes', 'url'],
            'info.contact' => ['sometimes', 'array'],
            'info.license' => ['sometimes', 'array'],

            // Contact object (conditional validation)
            'info.contact.name' => ['sometimes', 'string', 'filled'],
            'info.contact.email' => ['sometimes', 'email'],
            'info.contact.url' => ['sometimes', 'url'],

            // License object (name required if license present)
            'info.license.name' => ['required_with:info.license', 'string', 'filled'],
            'info.license.url' => ['sometimes', 'url'],

            // Components validation (only if components section exists)
            'components.schemas' => ['sometimes', 'array'],
            'components.responses' => ['sometimes', 'array'],
            'components.parameters' => ['sometimes', 'array'],
            'components.examples' => ['sometimes', 'array'],
            'components.requestBodies' => ['sometimes', 'array'],
            'components.headers' => ['sometimes', 'array'],
            'components.securitySchemes' => ['sometimes', 'array'],
            'components.links' => ['sometimes', 'array'],
            'components.callbacks' => ['sometimes', 'array'],

            // External docs validation
            'externalDocs.description' => ['sometimes', 'string', 'filled'],
            'externalDocs.url' => ['required_with:externalDocs', 'url'],

            // Servers validation
            'servers.*.url' => ['sometimes', 'string', 'filled'],
            'servers.*.description' => ['sometimes', 'string'],

            // Security validation
            'security.*' => ['sometimes', 'array'],

            // Tags validation
            'tags.*.name' => ['sometimes', 'string', 'filled'],
            'tags.*.description' => ['sometimes', 'string'],
            'tags.*.externalDocs' => ['sometimes', 'array'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            throw new ParseException('Document validation failed: '.implode('; ', $errors));
        }
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
