<?php declare(strict_types=1);

use Bambamboole\OpenApi\OpenApiParser;

it('can resolve external file references through parser', function () {
    $parser = OpenApiParser::make();
    $document = $parser->parseFile(dirname(__DIR__).'/Fixtures/external/main.yaml');

    expect($document->paths['/users']->get->responses['200']->description)->toBe('User response');
    expect($document->paths['/users/{id}']->get->responses['200']->content['application/json']->schema->type)->toBe('object');
    expect($document->paths['/users/{id}']->get->responses['200']->content['application/json']->schema->properties['email']->format)->toBe('email');
});
