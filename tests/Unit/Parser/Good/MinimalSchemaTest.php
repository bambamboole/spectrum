<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\OpenApiParser;

it('can parse minimal OpenAPI schema')
    ->expect(fn () => OpenApiParser::make()->parseArray($this->schema()))
    ->toBeInstanceOf(OpenApiDocument::class);
