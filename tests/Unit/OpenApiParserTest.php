<?php declare(strict_types=1);

use App\OpenApiParser;

it('throws exception for invalid JSON', function () {
    expect(fn () => OpenApiParser::make()->parseJson('invalid json'))
        ->toThrow(JsonException::class);
});

it('throws exception for invalid YAML', function () {
    expect(fn () => OpenApiParser::make()->parseYaml("invalid:\n  - yaml\n    - structure"))
        ->toThrow(Exception::class);
});

it('throws exception for non-existent file', function () {
    expect(fn () => OpenApiParser::make()->parseFile('non-existent.json'))
        ->toThrow(InvalidArgumentException::class);
});
