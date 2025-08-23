<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Validation\Spec\RequiredFieldsRule;
use Bambamboole\OpenApi\Validation\Validator;

it('passes validation when all best practices are followed', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
            'description' => 'A comprehensive test API',
        ],
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '200' => [
                            'description' => 'Success',
                        ],
                        '404' => [
                            'description' => 'Not Found',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, RequiredFieldsRule::class);

    expect($errors->getErrors())->toBeEmpty();
});

it('warns when paths are empty', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [],
    ]));

    $errors = Validator::validateDocument($document, RequiredFieldsRule::class);

    $warningList = $errors->getWarnings();
    expect($warningList)->toHaveCount(1);
    expect($warningList[0]->path)->toBe('paths');
    expect($warningList[0]->message)->toBe('Paths object should not be empty - API must define at least one path');
});

it('warns when operation has no success response', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '400' => [
                            'description' => 'Bad Request',
                        ],
                        '404' => [
                            'description' => 'Not Found',
                        ],
                    ],
                ],
                'post' => [
                    'responses' => [
                        '201' => [
                            'description' => 'Created',
                        ],
                        '400' => [
                            'description' => 'Bad Request',
                        ],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, RequiredFieldsRule::class);

    $warningList = $errors->getWarnings();
    expect($warningList)->toHaveCount(1);
    expect($warningList[0]->path)->toBe('paths./users.get.responses');
    expect($warningList[0]->message)->toBe('Operation should define at least one success response (2xx)');
});

it('accepts various success response formats', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '200' => ['description' => 'OK'],
                    ],
                ],
            ],
            '/posts' => [
                'post' => [
                    'responses' => [
                        '201' => ['description' => 'Created'],
                    ],
                ],
            ],
            '/files' => [
                'delete' => [
                    'responses' => [
                        '204' => ['description' => 'No Content'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, RequiredFieldsRule::class);

    // Should not have warnings for any of these operations
    expect($errors)->not->toHaveKey('warning');
});

it('provides info suggestion when description is missing', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
            // No description
        ],
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

    $errors = Validator::validateDocument($document, RequiredFieldsRule::class);

    $infoList = $errors->getInfo();
    expect($infoList)->toHaveCount(1);
    expect($infoList[0]->path)->toBe('info.description');
    expect($infoList[0]->message)->toBe('Info description is recommended for better API documentation');
});

it('reports multiple validation issues with different severities', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
            // Missing description
        ],
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '400' => ['description' => 'Bad Request'],
                        // No success response
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, RequiredFieldsRule::class);

    $warningList = $errors->getWarnings();
    expect($warningList)->toHaveCount(1);
    expect($warningList[0]->path)->toBe('paths./users.get.responses');
});
