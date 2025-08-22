<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\OpenApiParser;

it('can parse minimal OpenAPI schema')
    ->expect(fn () => OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Minimal API',
            'version' => '1.0.0',
        ],
        'paths' => [],
    ]))
    ->toBeInstanceOf(OpenApiDocument::class);
