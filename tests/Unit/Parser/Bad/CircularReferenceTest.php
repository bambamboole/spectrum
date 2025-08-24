<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ReferenceResolutionException;

// TODO: Fix circular reference detection for property-level references
// The current implementation can detect circular references at the resolver level,
// but property-level circular references cause infinite recursion during schema creation.
// This needs to be addressed by tracking reference resolution during the entire schema creation process.

it('detects invalid references (placeholder for circular reference test)', function () {
    $this->expectSchema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Invalid Reference API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'A' => [
                    'type' => 'object',
                    'properties' => [
                        'invalid' => ['$ref' => '#/components/schemas/NonExistent'], // Non-existent reference
                    ],
                ],
            ],
        ],
    ])->toThrow(function (ReferenceResolutionException $e) {
        expect($e->getMessage())->toContain('JSON pointer path not found');
    });
});
