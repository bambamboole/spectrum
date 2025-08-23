<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Factories;

use Bambamboole\OpenApi\Objects\Callback;
use Bambamboole\OpenApi\Objects\Example;
use Bambamboole\OpenApi\Objects\Link;
use Bambamboole\OpenApi\Objects\MediaType;
use Bambamboole\OpenApi\Objects\RequestBody;
use Bambamboole\OpenApi\Objects\Response;

class ComponentsFactory
{
    public static function create(): self
    {
        return new self;
    }

    public function createMediaType(array $data, string $keyPrefix = ''): MediaType
    {
        return MediaType::fromArray($data, $keyPrefix);
    }

    public function createResponse(array $data, string $keyPrefix = ''): Response
    {
        return Response::fromArray($data, $keyPrefix);
    }

    public function createRequestBody(array $data, string $keyPrefix = ''): RequestBody
    {
        return RequestBody::fromArray($data, $keyPrefix);
    }

    // Public methods for backward compatibility with tests
    public function createLink(array $data, string $keyPrefix = ''): Link
    {
        return Link::fromArray($data, $keyPrefix);
    }

    public function createLinks(array $links): array
    {
        return Link::multiple($links, 'links');
    }

    public function createResponses(array $responses): array
    {
        return Response::multiple($responses, 'responses');
    }

    public function createRequestBodies(array $requestBodies): array
    {
        return RequestBody::multiple($requestBodies, 'requestBodies');
    }

    public function createMediaTypes(array $content, string $keyPrefix = ''): array
    {
        return MediaType::multiple($content, $keyPrefix);
    }

    public function createExample(array $data, string $keyPrefix = ''): Example
    {
        return Example::fromArray($data, $keyPrefix);
    }

    public function createExamples(array $examples): array
    {
        return Example::multiple($examples, 'examples');
    }

    public function createCallback(array $data, string $keyPrefix = ''): Callback
    {
        return Callback::fromArray($data, $keyPrefix);
    }

    public function createCallbacks(array $callbacks): array
    {
        return Callback::multiple($callbacks, 'callbacks');
    }
}
