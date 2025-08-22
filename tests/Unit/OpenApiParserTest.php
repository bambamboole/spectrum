<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\OpenApiParser;
use Bambamboole\OpenApi\Tests\Fixtures\Schemas\Bad\BadSchemaFixture;
use Bambamboole\OpenApi\Tests\Fixtures\Schemas\Good\AbstractGoodSchemaFixture;
use Bambamboole\OpenApi\Tests\Fixtures\Schemas\SchemaFixtureInterface;

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

it('can parse good schema fixtures successfully', function (SchemaFixtureInterface $fixture) {

    $document = OpenApiParser::make()->parseArray($fixture->schema());
    expect($document)->toBeInstanceOf(OpenApiDocument::class);

})->with(AbstractGoodSchemaFixture::all());

it('rejects bad schema fixtures with appropriate errors', function (SchemaFixtureInterface $fixture) {

    expect(fn () => OpenApiParser::make()->parseArray($fixture->schema()))
        ->toThrow(function (ParseException $e) use ($fixture) {
            expect($e->getMessages())->toMatchArray($fixture->violations());
        });

})->with(BadSchemaFixture::all());
