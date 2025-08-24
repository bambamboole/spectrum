<?php declare(strict_types=1);

use Bambamboole\OpenApi\OpenApiParser;
use Illuminate\Http\Client\Factory;

it('can parse OpenAPI specification from URL', function () {
    $yamlContent = '
openapi: 3.1.0
info:
  title: Test API
  version: 1.0.0
paths:
  /users:
    get:
      responses:
        "200":
          description: Success
';

    $http = new Factory;
    $http->fake(['*' => $http->response($yamlContent, 200, ['Content-Type' => 'application/yaml'])]);

    $parser = new OpenApiParser(new \Illuminate\Filesystem\Filesystem, $http);
    $document = $parser->parseUrl('https://example.com/api.yaml');

    expect($document->info->title)->toBe('Test API');
    expect($document->paths['/users']->get->responses['200']->description)->toBe('Success');
});

it('handles JSON content from URLs', function () {
    $jsonContent = '{"openapi": "3.1.0", "info": {"title": "Test", "version": "1.0.0"}, "paths": {}}';

    $http = new Factory;
    $http->fake(['*' => $http->response($jsonContent, 200, ['Content-Type' => 'application/json'])]);

    $parser = new OpenApiParser(new \Illuminate\Filesystem\Filesystem, $http);
    $document = $parser->parseUrl('https://example.com/api.json');

    expect($document->info->title)->toBe('Test');
});

it('can parse the local digital ocean spec', function () {
    $parser = OpenApiParser::make();
    $document = $parser->parseFile(dirname(__DIR__, 2).'/digitalocean-openapi/specification/DigitalOcean-public.v2.yaml');

    expect($document->info->title)->toBe('DigitalOcean API');
    expect($document->info->version)->toBe('2.0');
    expect($document->paths)->not->toBeEmpty();

    // Test that we can access a specific endpoint
    expect($document->paths['/v2/account']->get->responses['200']->description)->toBe('A JSON object keyed on account with an excerpt of the current user account data.');
});
