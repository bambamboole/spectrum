<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\OpenApiDocument;
use Bambamboole\OpenApi\OpenApiParser;
use Bambamboole\OpenApi\Tests\Fixtures\Schemas\Bad\BadSchemaFixture;
use Bambamboole\OpenApi\Tests\Fixtures\Schemas\Good\GoodSchemaFixture;
use Bambamboole\OpenApi\Tests\Fixtures\Schemas\SchemaFixtureInterface;

it('can parse a valid JSON OpenAPI document', function () {
    $schema = json_encode([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
    ]);

    expect(OpenApiParser::fromJson($schema))->toBeInstanceOf(OpenApiDocument::class);
});

it('throws exception for invalid JSON', function () {
    expect(fn () => OpenApiParser::fromJson('invalid json'))
        ->toThrow(InvalidArgumentException::class);
});

it('throws exception for missing openapi field', function () {
    $schema = json_encode([
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
    ]);
    expect(fn () => OpenApiParser::fromJson($schema))->toThrow(ParseException::class);
});

it('throws exception for missing info field', function () {
    expect(fn () => OpenApiParser::fromJson(json_encode([
        'openapi' => '3.0.0',
        'paths' => [],
    ])))
        ->toThrow(ParseException::class);
});

it('throws exception for missing paths field', function () {
    expect(fn () => OpenApiParser::fromJson(json_encode([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
    ])))
        ->toThrow(ParseException::class);
});

it('can parse the test schema file')
    ->expect(fn () => OpenApiParser::fromFile(__DIR__.'/../schemas/test-models.json'))
    ->toBeInstanceOf(OpenApiDocument::class);

it('throws exception for non-existent file', function () {
    expect(fn () => OpenApiParser::fromFile('non-existent.json'))
        ->toThrow(ParseException::class);
});

it('can parse good schema fixtures successfully', function (SchemaFixtureInterface $fixture) {

    $document = OpenApiParser::fromArray($fixture->schema());
    expect($document)->toBeInstanceOf(OpenApiDocument::class);

})->with(GoodSchemaFixture::all());

it('rejects bad schema fixtures with appropriate errors', function (SchemaFixtureInterface $fixture) {

    expect(fn () => OpenApiParser::fromArray($fixture->schema()))->toThrow(ParseException::class);

})->with(BadSchemaFixture::all());
