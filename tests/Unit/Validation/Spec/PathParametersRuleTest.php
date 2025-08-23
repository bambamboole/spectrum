<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Validation\Spec\PathParametersRule;
use Bambamboole\OpenApi\Validation\Validator;

it('passes validation when path parameters are correctly defined', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users/{id}' => [
                'get' => [
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                        ],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
            ],
            '/users/{userId}/posts/{postId}' => [
                'get' => [
                    'parameters' => [
                        [
                            'name' => 'userId',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                        ],
                        [
                            'name' => 'postId',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                        ],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
            ],
        ],
    ]));

    $result = Validator::validateDocument($document, PathParametersRule::class);

    expect($result->all())->toBeEmpty();
});

it('passes validation with path-level parameters', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users/{id}' => [
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'get' => [
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
                'put' => [
                    'responses' => [
                        '200' => ['description' => 'Updated'],
                    ],
                ],
            ],
        ],
    ]));

    $result = Validator::validateDocument($document, PathParametersRule::class);

    expect($result->all())->toBeEmpty();
});

it('fails validation when path parameter is missing from parameters', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users/{id}' => [
                'get' => [
                    'parameters' => [
                        [
                            'name' => 'limit',
                            'in' => 'query',
                            'schema' => ['type' => 'integer'],
                        ],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
            ],
        ],
    ]));

    $result = Validator::validateDocument($document, PathParametersRule::class);

    $errorList = $result->getErrors();
    expect($errorList)->toHaveCount(1);
    expect($errorList[0]->path)->toBe('paths./users/{id}.get.parameters');
    expect($errorList[0]->message)->toBe('Path parameter \'id\' found in path template but not defined in parameters');
});

it('fails validation when path parameter is not marked as required', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users/{id}' => [
                'get' => [
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => false, // Should be true
                            'schema' => ['type' => 'integer'],
                        ],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, PathParametersRule::class);

    $errorList = $errors->getErrors();
    expect($errorList)->toHaveCount(1);
    expect($errorList[0]->path)->toBe('paths./users/{id}.get.parameters.id.required');
    expect($errorList[0]->message)->toBe('Path parameter \'id\' must be required');
});

it('fails validation when path parameter is defined but not used in path', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users/{id}' => [
                'get' => [
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                        ],
                        [
                            'name' => 'unused',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, PathParametersRule::class);

    $errorList = $errors->getErrors();
    expect($errorList)->toHaveCount(1);
    expect($errorList[0]->path)->toBe('paths./users/{id}.get.parameters.unused');
    expect($errorList[0]->message)->toBe('Path parameter \'unused\' is defined but not used in path template');
});

it('handles multiple path parameters with mixed validity', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users/{userId}/posts/{postId}' => [
                'get' => [
                    'parameters' => [
                        [
                            'name' => 'userId',
                            'in' => 'path',
                            'required' => false, // Should be true
                            'schema' => ['type' => 'integer'],
                        ],
                        [
                            'name' => 'unused',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                        // Missing postId parameter
                    ],
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, PathParametersRule::class);

    $errorList = $errors->getErrors();
    expect($errorList)->toHaveCount(3);
    expect(collect($errorList)->pluck('path')->toArray())->toContain(
        'paths./users/{userId}/posts/{postId}.get.parameters.userId.required',
        'paths./users/{userId}/posts/{postId}.get.parameters',
        'paths./users/{userId}/posts/{postId}.get.parameters.unused'
    );
});

it('passes validation for paths without parameters', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, PathParametersRule::class);

    expect($errors->getErrors())->toBeEmpty();
});

it('handles query and header parameters correctly', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users/{id}' => [
                'get' => [
                    'parameters' => [
                        [
                            'name' => 'id',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'integer'],
                        ],
                        [
                            'name' => 'limit',
                            'in' => 'query',
                            'schema' => ['type' => 'integer'],
                        ],
                        [
                            'name' => 'Authorization',
                            'in' => 'header',
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, PathParametersRule::class);

    expect($errors->getErrors())->toBeEmpty();
});
