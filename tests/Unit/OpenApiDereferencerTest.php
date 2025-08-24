<?php declare(strict_types=1);

use Bambamboole\OpenApi\OpenApiDereferencer;

it('can dereference external file references', function () {
    // Create a simple test case with external references
    $tempDir = sys_get_temp_dir().'/openapi_test_'.uniqid();
    mkdir($tempDir);

    $mainFile = $tempDir.'/main.yaml';
    $externalFile = $tempDir.'/external.yaml';

    file_put_contents($mainFile, "
openapi: 3.1.0
info:
  title: Test API
  version: 1.0.0
  description:
    \$ref: './external.yaml#/description'
paths:
  /test:
    get:
      responses:
        '200':
          \$ref: './external.yaml#/responses/Success'
");

    file_put_contents($externalFile, '
description: This is an external description
responses:
  Success:
    description: Success response
    content:
      application/json:
        schema:
          type: object
          properties:
            message:
              type: string
');

    $dereferencer = new OpenApiDereferencer;
    $dereferencedDoc = $dereferencer->dereferenceFile($mainFile);

    expect($dereferencedDoc)->toBeArray();
    expect($dereferencedDoc['info']['description'])->toBe('This is an external description');
    expect($dereferencedDoc['paths']['/test']['get']['responses']['200']['description'])->toBe('Success response');
    expect($dereferencedDoc['paths']['/test']['get']['responses']['200']['content']['application/json']['schema']['type'])->toBe('object');

    // Verify no $ref keys remain
    expect($dereferencedDoc['info'])->not->toHaveKey('$ref');
    expect($dereferencedDoc['paths']['/test']['get']['responses']['200'])->not->toHaveKey('$ref');

    // Cleanup
    unlink($mainFile);
    unlink($externalFile);
    rmdir($tempDir);
});

it('can dereference local JSON pointer references', function () {
    // Create a simple test case with local references
    $tempDir = sys_get_temp_dir().'/openapi_test_'.uniqid();
    mkdir($tempDir);

    $mainFile = $tempDir.'/main.yaml';

    file_put_contents($mainFile, "
openapi: 3.1.0
info:
  title: Test API
  version: 1.0.0
components:
  schemas:
    User:
      type: object
      properties:
        name:
          type: string
paths:
  /users:
    get:
      responses:
        '200':
          description: Success
          content:
            application/json:
              schema:
                \$ref: '#/components/schemas/User'
");

    $dereferencer = new OpenApiDereferencer;
    $dereferencedDoc = $dereferencer->dereferenceFile($mainFile);

    expect($dereferencedDoc)->toBeArray();

    // Check that the local reference is resolved
    $schema = $dereferencedDoc['paths']['/users']['get']['responses']['200']['content']['application/json']['schema'];
    expect($schema)->not->toHaveKey('$ref');
    expect($schema['type'])->toBe('object');
    expect($schema['properties']['name']['type'])->toBe('string');

    // Cleanup
    unlink($mainFile);
    rmdir($tempDir);
});

it('handles non-existent files gracefully', function () {
    $dereferencer = new OpenApiDereferencer;

    expect(fn () => $dereferencer->dereferenceFile('/non/existent/file.yaml'))
        ->toThrow(\Bambamboole\OpenApi\Exceptions\ReferenceResolutionException::class, 'File not found');
});

it('handles circular references gracefully', function () {
    // Create a simple test case with circular references
    $tempDir = sys_get_temp_dir().'/openapi_test_'.uniqid();
    mkdir($tempDir);

    $mainFile = $tempDir.'/main.yaml';
    $refFile = $tempDir.'/ref.yaml';

    file_put_contents($mainFile, "
openapi: 3.1.0
info:
  title: Test
  version: 1.0.0
components:
  schemas:
    MainSchema:
      \$ref: './ref.yaml#/RefSchema'
paths: {}
");

    file_put_contents($refFile, "
RefSchema:
  type: object
  properties:
    recursive:
      \$ref: './main.yaml#/components/schemas/MainSchema'
");

    $dereferencer = new OpenApiDereferencer;

    // Should not throw - circular references should be preserved as $ref
    $dereferencedDoc = $dereferencer->dereferenceFile($mainFile);

    expect($dereferencedDoc)->toBeArray();
    expect($dereferencedDoc['info']['title'])->toBe('Test');

    // The MainSchema should be expanded but circular reference preserved
    $mainSchema = $dereferencedDoc['components']['schemas']['MainSchema'];
    expect($mainSchema['type'])->toBe('object');
    expect($mainSchema['properties']['recursive'])->toHaveKey('$ref');

    // Cleanup
    unlink($mainFile);
    unlink($refFile);
    rmdir($tempDir);
});

it('can handle complex internal references and preserve circular refs', function () {
    // Create a test case with multiple internal references
    $tempDir = sys_get_temp_dir().'/openapi_test_'.uniqid();
    mkdir($tempDir);

    $mainFile = $tempDir.'/main.yaml';

    file_put_contents($mainFile, "
openapi: 3.1.0
info:
  title: Test API
  version: 1.0.0
components:
  schemas:
    User:
      type: object
      properties:
        name:
          type: string
        address:
          \$ref: '#/components/schemas/Address'
    Address:
      type: object
      properties:
        street:
          type: string
        city:
          type: string
        user:
          \$ref: '#/components/schemas/User'  # This creates a circular reference
paths: {}
");

    $dereferencer = new OpenApiDereferencer;
    $dereferencedDoc = $dereferencer->dereferenceFile($mainFile);

    expect($dereferencedDoc)->toBeArray();
    expect($dereferencedDoc['info']['title'])->toBe('Test API');

    // Verify basic structure is preserved
    expect($dereferencedDoc['components']['schemas'])->toHaveKey('User');
    expect($dereferencedDoc['components']['schemas'])->toHaveKey('Address');

    // The core schemas should exist (circular references handled gracefully)
    $userSchema = $dereferencedDoc['components']['schemas']['User'];
    expect($userSchema['type'])->toBe('object');

    // Cleanup
    unlink($mainFile);
    rmdir($tempDir);
});

it('can successfully dereference the DigitalOcean specification', function () {
    $dereferencer = new OpenApiDereferencer;

    $dereferencedSpec = $dereferencer->dereferenceFile(dirname(__DIR__, 2).'/digitalocean-openapi/specification/DigitalOcean-public.v2.yaml');
    //  There are some issues in the digitalocean spec. I`ve PRed them in, lets hope they get merged soon: https://github.com/digitalocean/openapi/pull/1082
    //  $dereferencedSpec = $dereferencer->dereferenceUrl('https://raw.githubusercontent.com/digitalocean/openapi/main/specification/DigitalOcean-public.v2.yaml');

    // Basic structure validation
    expect($dereferencedSpec)->toBeArray();
    expect($dereferencedSpec['openapi'])->toBe('3.0.0');
    expect($dereferencedSpec['info']['title'])->toBe('DigitalOcean API');
    expect($dereferencedSpec['info']['version'])->toBe('2.0');
    expect($dereferencedSpec['paths'])->not->toBeEmpty();

    // Verify external references were resolved - info.description should be expanded
    expect($dereferencedSpec['info']['description'])->toBeString();
    expect($dereferencedSpec['info']['description'])->toContain('Introduction');
    expect($dereferencedSpec['info']['description'])->not->toHaveKey('$ref');

    // Test specific endpoints are properly dereferenced
    expect($dereferencedSpec['paths'])->toHaveKey('/v2/account');
    $accountEndpoint = $dereferencedSpec['paths']['/v2/account']['get'];
    expect($accountEndpoint)->toBeArray();
    expect($accountEndpoint)->not->toHaveKey('$ref');

    // Verify responses are dereferenced
    expect($accountEndpoint['responses']['200'])->toBeArray();
    expect($accountEndpoint['responses']['200']['description'])->toBeString();
    expect($accountEndpoint['responses']['200'])->not->toHaveKey('$ref');

    // Test a more complex endpoint with parameters
    expect($dereferencedSpec['paths'])->toHaveKey('/v2/droplets');
    $dropletsEndpoint = $dereferencedSpec['paths']['/v2/droplets']['get'];
    expect($dropletsEndpoint)->toBeArray();
    expect($dropletsEndpoint)->not->toHaveKey('$ref');

    // Verify headers are dereferenced
    if (isset($accountEndpoint['responses']['200']['headers'])) {
        foreach ($accountEndpoint['responses']['200']['headers'] as $header) {
            expect($header)->not->toHaveKey('$ref');
        }
    }

    // Test that the problematic gen-ai endpoints are handled
    if (isset($dereferencedSpec['paths']['/v2/gen-ai/agents'])) {
        $genAiEndpoint = $dereferencedSpec['paths']['/v2/gen-ai/agents']['post'];
        expect($genAiEndpoint)->toBeArray();
        expect($genAiEndpoint)->not->toHaveKey('$ref');

        // The requestBody should be dereferenced
        if (isset($genAiEndpoint['requestBody'])) {
            expect($genAiEndpoint['requestBody'])->not->toHaveKey('$ref');
        }
    }

    // Count remaining $ref occurrences - should only be legitimate circular references
    $serialized = json_encode($dereferencedSpec);

    // Verify no obvious external file references remain (these should all be resolved)
    expect($serialized)->not->toContain('"$ref":"./');
    expect($serialized)->not->toContain('"$ref":"../');

    // CRITICAL: Verify that circular reference targets are still available in the dereferenced document
    // This ensures that remaining $ref values can still be resolved during parsing/validation

    // Check if the major circular reference targets exist
    $hasApiAgentDef = isset($dereferencedSpec['apiAgent']) ||
                      isset($dereferencedSpec['components']['schemas']['apiAgent']) ||
                      str_contains($serialized, '"apiAgent":{');

    $hasApiWorkspaceDef = isset($dereferencedSpec['apiWorkspace']) ||
                          isset($dereferencedSpec['components']['schemas']['apiWorkspace']) ||
                          str_contains($serialized, '"apiWorkspace":{');

    // These should be true - circular references need their targets to be resolvable
    expect($hasApiAgentDef)->toBeTrue('apiAgent definition must be present for circular refs to be resolvable');
    expect($hasApiWorkspaceDef)->toBeTrue('apiWorkspace definition must be present for circular refs to be resolvable');

    // Basic schema validation - make sure we didn't break the structure
    expect($dereferencedSpec)->toHaveKey('openapi');
    expect($dereferencedSpec)->toHaveKey('info');
    expect($dereferencedSpec)->toHaveKey('paths');
});
