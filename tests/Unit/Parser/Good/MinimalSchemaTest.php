<?php declare(strict_types=1);

use App\Objects\OpenApiDocument;
use App\OpenApiParser;

it('can parse minimal OpenAPI schema')
    ->expect(fn () => OpenApiParser::make()->parseArray($this->schema()))
    ->toBeInstanceOf(OpenApiDocument::class);
