<?php declare(strict_types=1);

it('accepts schema with invalid security reference during parsing', function () {
    // Parsing should succeed - validation of security scheme references will happen later
    $documentFn = fn () => \Bambamboole\OpenApi\OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'securitySchemes' => [
                'ApiKeyAuth' => [
                    'type' => 'apiKey',
                    'in' => 'header',
                    'name' => 'X-API-Key',
                ],
            ],
        ],
        'security' => [
            ['ApiKeyAuth' => []],
            ['NonExistentAuth' => []], // References non-existent security scheme - validation will happen post-parsing
        ],
    ]);

    expect($documentFn())->toBeInstanceOf(\Bambamboole\OpenApi\Objects\OpenApiDocument::class);
});
