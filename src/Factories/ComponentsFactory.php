<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Factories;

use Bambamboole\OpenApi\Context\ParsingContext;
use Bambamboole\OpenApi\Factories\Concerns\ValidatesOpenApiObjects;
use Bambamboole\OpenApi\Objects\Callback;
use Bambamboole\OpenApi\Objects\Components;
use Bambamboole\OpenApi\Objects\Example;
use Bambamboole\OpenApi\Objects\Header;
use Bambamboole\OpenApi\Objects\Link;
use Bambamboole\OpenApi\Objects\MediaType;
use Bambamboole\OpenApi\Objects\Parameter;
use Bambamboole\OpenApi\Objects\RequestBody;
use Bambamboole\OpenApi\Objects\Response;
use Bambamboole\OpenApi\Objects\Schema;
use Bambamboole\OpenApi\Objects\Server;

use function collect;

class ComponentsFactory
{
    use ValidatesOpenApiObjects;

    public function __construct(ParsingContext $context)
    {
        $this->context = $context;
    }

    public static function create(ParsingContext $context): self
    {
        return new self($context);
    }

    public function createComponents(array $data, array $securitySchemes): Components
    {
        return new Components(
            schemas: $this->createSchemaArray($data['schemas'] ?? [], 'components.schemas'),
            responses: $this->createResponses($data['responses'] ?? []),
            parameters: $this->createParameters($data['parameters'] ?? []),
            examples: $this->createExamples($data['examples'] ?? []),
            requestBodies: $this->createRequestBodies($data['requestBodies'] ?? []),
            headers: $this->createHeaders($data['headers'] ?? []),
            securitySchemes: $securitySchemes,
            links: $this->createLinks($data['links'] ?? []),
            callbacks: $this->createCallbacks($data['callbacks'] ?? []),
        );
    }

    public function createSchema(array $data, string $keyPrefix = ''): Schema
    {
        // Handle $ref resolution first
        if (isset($data['$ref'])) {
            $resolvedData = $this->context->referenceResolver->resolve($data['$ref']);
            // If the resolved data also has a $ref, that's a problem with the schema design
            // The resolver should have already resolved it completely
            if (is_array($resolvedData)) {
                return $this->createSchema($resolvedData, $keyPrefix);
            }
            // If it's not an array, something went wrong
            throw new \InvalidArgumentException('Resolved reference must be an array');
        }

        // Advanced schema validation with conditional rules
        $this->validate($data, Schema::class, $keyPrefix);

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

    public function createParameter(array $data, string $keyPrefix = ''): Parameter
    {
        // Handle $ref resolution first
        if (isset($data['$ref'])) {
            $resolvedData = $this->context->referenceResolver->resolve($data['$ref']);
            if (is_array($resolvedData)) {
                return $this->createParameter($resolvedData, $keyPrefix);
            }
            throw new \InvalidArgumentException('Resolved reference must be an array');
        }

        $this->validate($data, Parameter::class, $keyPrefix);

        return new Parameter(
            name: $data['name'],
            in: $data['in'],
            description: $data['description'] ?? null,
            required: $data['required'] ?? false,
            deprecated: $data['deprecated'] ?? false,
            allowEmptyValue: $data['allowEmptyValue'] ?? null,
            style: $data['style'] ?? null,
            explode: $data['explode'] ?? null,
            allowReserved: $data['allowReserved'] ?? null,
            schema: isset($data['schema']) ? $this->createSchema($data['schema'], $this->buildKeyPrefix($keyPrefix, 'schema')) : null,
            example: $data['example'] ?? null,
            examples: $data['examples'] ?? null,
            content: $data['content'] ?? null,
        );
    }

    private function createParameters(array $parameters): array
    {
        $parsed = [];
        foreach ($parameters as $key => $parameter) {
            $parsed[$key] = $this->createParameter($parameter, "components.parameters.{$key}");
        }

        return $parsed;
    }

    public function createHeader(array $data, string $keyPrefix = ''): Header
    {
        // Handle $ref resolution first
        if (isset($data['$ref'])) {
            $resolvedData = $this->context->referenceResolver->resolve($data['$ref']);
            if (is_array($resolvedData)) {
                return $this->createHeader($resolvedData, $keyPrefix);
            }
            throw new \InvalidArgumentException('Resolved reference must be an array');
        }

        $this->validate($data, Header::class, $keyPrefix);

        return new Header(
            description: $data['description'] ?? null,
            required: $data['required'] ?? false,
            deprecated: $data['deprecated'] ?? false,
            allowEmptyValue: $data['allowEmptyValue'] ?? null,
            style: $data['style'] ?? null,
            explode: $data['explode'] ?? null,
            schema: isset($data['schema']) ? $this->createSchema($data['schema'], $this->buildKeyPrefix($keyPrefix, 'schema')) : null,
            example: $data['example'] ?? null,
            examples: $data['examples'] ?? null,
            content: $data['content'] ?? null,
        );
    }

    private function createHeaders(array $headers): array
    {
        $parsed = [];
        foreach ($headers as $key => $header) {
            $parsed[$key] = $this->createHeader($header, "components.headers.{$key}");
        }

        return $parsed;
    }

    public function createMediaType(array $data, string $keyPrefix = ''): MediaType
    {
        // MediaType objects are not typically referenced, but handle it for completeness
        if (isset($data['$ref'])) {
            $resolvedData = $this->context->referenceResolver->resolve($data['$ref']);
            if (is_array($resolvedData)) {
                return $this->createMediaType($resolvedData, $keyPrefix);
            }
            throw new \InvalidArgumentException('Resolved reference must be an array');
        }

        $this->validate($data, MediaType::class, $keyPrefix);

        return new MediaType(
            schema: isset($data['schema']) ? $this->createSchema($data['schema'], $this->buildKeyPrefix($keyPrefix, 'schema')) : null,
            example: $data['example'] ?? null,
            examples: $data['examples'] ?? null,
            encoding: $data['encoding'] ?? null,
        );
    }

    public function createMediaTypes(array $content, string $keyPrefix = ''): array
    {
        $parsed = [];
        foreach ($content as $mediaType => $mediaTypeData) {
            $parsed[$mediaType] = $this->createMediaType($mediaTypeData, "{$keyPrefix}.{$mediaType}");
        }

        return $parsed;
    }

    public function createResponse(array $data, string $keyPrefix = ''): Response
    {
        // Handle $ref resolution first
        if (isset($data['$ref'])) {
            $resolvedData = $this->context->referenceResolver->resolve($data['$ref']);
            if (is_array($resolvedData)) {
                return $this->createResponse($resolvedData, $keyPrefix);
            }
            throw new \InvalidArgumentException('Resolved reference must be an array');
        }

        $this->validate($data, Response::class, $keyPrefix);

        return new Response(
            description: $data['description'],
            headers: isset($data['headers']) ? $this->createHeaders($data['headers']) : null,
            content: isset($data['content']) ? $this->createMediaTypes($data['content'], $this->buildKeyPrefix($keyPrefix, 'content')) : null,
            links: isset($data['links']) ? $this->createResponseLinks($data['links'], $this->buildKeyPrefix($keyPrefix, 'links')) : null,
        );
    }

    public function createResponses(array $responses): array
    {
        $parsed = [];
        foreach ($responses as $key => $response) {
            $parsed[$key] = $this->createResponse($response, "components.responses.{$key}");
        }

        return $parsed;
    }

    public function createRequestBody(array $data, string $keyPrefix = ''): RequestBody
    {
        // Handle $ref resolution first
        if (isset($data['$ref'])) {
            $resolvedData = $this->context->referenceResolver->resolve($data['$ref']);
            if (is_array($resolvedData)) {
                return $this->createRequestBody($resolvedData, $keyPrefix);
            }
            throw new \InvalidArgumentException('Resolved reference must be an array');
        }

        $this->validate($data, RequestBody::class, $keyPrefix);

        return new RequestBody(
            content: $this->createMediaTypes($data['content'], $this->buildKeyPrefix($keyPrefix, 'content')),
            description: $data['description'] ?? null,
            required: $data['required'] ?? false,
        );
    }

    public function createRequestBodies(array $requestBodies): array
    {
        $parsed = [];
        foreach ($requestBodies as $key => $requestBody) {
            $parsed[$key] = $this->createRequestBody($requestBody, "components.requestBodies.{$key}");
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

    private function createSchemaArray(?array $schemas, string $keyPrefix = ''): ?array
    {
        if ($schemas === null) {
            return null;
        }

        return collect($schemas)
            ->map(fn ($schema, $key) => $this->createSchema($schema, $this->buildKeyPrefix($keyPrefix, (string) $key)))
            ->all();
    }

    public function createLink(array $data, string $keyPrefix = ''): Link
    {
        // Handle $ref resolution first
        if (isset($data['$ref'])) {
            $resolvedData = $this->context->referenceResolver->resolve($data['$ref']);
            if (is_array($resolvedData)) {
                return $this->createLink($resolvedData, $keyPrefix);
            }
            throw new \InvalidArgumentException('Resolved reference must be an array');
        }

        $this->validate($data, Link::class, $keyPrefix);

        // Handle server object creation if present
        $server = null;
        if (isset($data['server'])) {
            $server = new Server(
                url: $data['server']['url'] ?? '',
                description: $data['server']['description'] ?? null,
                variables: $data['server']['variables'] ?? [],
            );
        }

        return new Link(
            operationRef: $data['operationRef'] ?? null,
            operationId: $data['operationId'] ?? null,
            parameters: $data['parameters'] ?? null,
            requestBody: $data['requestBody'] ?? null,
            description: $data['description'] ?? null,
            server: $server,
        );
    }

    public function createLinks(array $links): array
    {
        $parsed = [];
        foreach ($links as $key => $link) {
            $parsed[$key] = $this->createLink($link, "components.links.{$key}");
        }

        return $parsed;
    }

    private function createResponseLinks(array $links, string $keyPrefix = ''): array
    {
        $parsed = [];
        foreach ($links as $key => $link) {
            $parsed[$key] = $this->createLink($link, $this->buildKeyPrefix($keyPrefix, $key));
        }

        return $parsed;
    }

    public function createCallback(array $data, string $keyPrefix = ''): Callback
    {
        // Handle $ref resolution first
        if (isset($data['$ref'])) {
            $resolvedData = $this->context->referenceResolver->resolve($data['$ref']);
            if (is_array($resolvedData)) {
                return $this->createCallback($resolvedData, $keyPrefix);
            }
            throw new \InvalidArgumentException('Resolved reference must be an array');
        }

        $this->validate($data, Callback::class, $keyPrefix);

        return new Callback(
            expressions: $data,
        );
    }

    public function createCallbacks(array $callbacks): array
    {
        $parsed = [];
        foreach ($callbacks as $key => $callback) {
            $parsed[$key] = $this->createCallback($callback, "components.callbacks.{$key}");
        }

        return $parsed;
    }

    public function createExample(array $data, string $keyPrefix = ''): Example
    {
        // Handle $ref resolution first
        if (isset($data['$ref'])) {
            $resolvedData = $this->context->referenceResolver->resolve($data['$ref']);
            if (is_array($resolvedData)) {
                return $this->createExample($resolvedData, $keyPrefix);
            }
            throw new \InvalidArgumentException('Resolved reference must be an array');
        }

        $this->validate($data, Example::class, $keyPrefix);

        return new Example(
            summary: $data['summary'] ?? null,
            description: $data['description'] ?? null,
            value: $data['value'] ?? null,
            externalValue: $data['externalValue'] ?? null,
        );
    }

    public function createExamples(array $examples): array
    {
        $parsed = [];
        foreach ($examples as $key => $example) {
            $parsed[$key] = $this->createExample($example, "components.examples.{$key}");
        }

        return $parsed;
    }

    private function buildKeyPrefix(string $keyPrefix, string $key): string
    {
        return $keyPrefix === '' ? $key : $keyPrefix.'.'.$key;
    }
}
