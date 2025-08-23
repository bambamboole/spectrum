<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Validation\Spec\ValidSecurityReferencesRule;
use Bambamboole\OpenApi\Validation\Validator;

it('passes validation when all security references are valid', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'security' => [
                        ['api_key' => []],
                        ['oauth2' => ['read:users']],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
            ],
            '/admin' => [
                'post' => [
                    'security' => [
                        ['basic_auth' => []],
                    ],
                    'responses' => [
                        '201' => ['description' => 'Created'],
                    ],
                ],
            ],
        ],
        'security' => [
            ['api_key' => []],
        ],
        'components' => [
            'securitySchemes' => [
                'api_key' => [
                    'type' => 'apiKey',
                    'name' => 'X-API-Key',
                    'in' => 'header',
                ],
                'oauth2' => [
                    'type' => 'oauth2',
                    'flows' => [
                        'authorizationCode' => [
                            'authorizationUrl' => 'https://example.com/oauth/authorize',
                            'tokenUrl' => 'https://example.com/oauth/token',
                            'scopes' => [
                                'read:users' => 'Read user data',
                            ],
                        ],
                    ],
                ],
                'basic_auth' => [
                    'type' => 'http',
                    'scheme' => 'basic',
                ],
            ],
        ],
    ]
    ));

    $errors = Validator::validateDocument($document, ValidSecurityReferencesRule::class);

    expect($errors->getErrors())->toBeEmpty();
});

it('fails validation when global security references undefined scheme', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'security' => [
            ['undefined_scheme' => []],
            ['api_key' => []],
        ],
        'components' => [
            'securitySchemes' => [
                'api_key' => [
                    'type' => 'apiKey',
                    'name' => 'X-API-Key',
                    'in' => 'header',
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ValidSecurityReferencesRule::class);

    $errorList = $errors->getErrors();
    expect($errorList)->toHaveCount(1);
    expect($errorList[0]->path)->toBe('security.0.undefined_scheme');
    expect($errorList[0]->message)->toBe('Security requirement references undefined security scheme. Available schemes: api_key');
});

it('fails validation when operation security references undefined scheme', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'security' => [
                        ['missing_scheme' => []],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
                'post' => [
                    'security' => [
                        ['api_key' => []],
                        ['another_missing' => []],
                    ],
                    'responses' => [
                        '201' => ['description' => 'Created'],
                    ],
                ],
            ],
        ],
        'components' => [
            'securitySchemes' => [
                'api_key' => [
                    'type' => 'apiKey',
                    'name' => 'X-API-Key',
                    'in' => 'header',
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ValidSecurityReferencesRule::class);

    $errorList = $errors->getErrors();
    expect($errorList)->toHaveCount(2);
    expect(collect($errorList)->pluck('path')->toArray())->toContain('paths./users.get.security.0.missing_scheme', 'paths./users.post.security.1.another_missing');
});

it('fails validation when no security schemes are defined but security requirements exist', function () {
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
        'security' => [
            ['api_key' => []],
        ],
        // No components or securitySchemes defined
    ]));

    $errors = Validator::validateDocument($document, ValidSecurityReferencesRule::class);

    $errorList = $errors->getErrors();
    expect($errorList)->toHaveCount(1);
    expect($errorList[0]->path)->toBe('security.0.api_key');
});

it('passes validation when no security requirements are defined', function () {
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
        // No security requirements defined
    ]));

    $errors = Validator::validateDocument($document, ValidSecurityReferencesRule::class);

    expect($errors->getErrors())->toBeEmpty();
});

it('passes validation when operations have no security requirements', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
                'post' => [
                    'responses' => [
                        '201' => ['description' => 'Created'],
                    ],
                ],
            ],
        ],
        'security' => [
            ['api_key' => []],
        ],
        'components' => [
            'securitySchemes' => [
                'api_key' => [
                    'type' => 'apiKey',
                    'name' => 'X-API-Key',
                    'in' => 'header',
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ValidSecurityReferencesRule::class);

    expect($errors->getErrors())->toBeEmpty();
});

it('handles multiple security requirements with mixed validity', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'security' => [
                        ['api_key' => [], 'oauth2' => ['read:users']],
                        ['missing_scheme' => [], 'api_key' => []],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Success'],
                    ],
                ],
            ],
        ],
        'security' => [
            ['valid_scheme' => []],
            ['invalid_global' => []],
        ],
        'components' => [
            'securitySchemes' => [
                'api_key' => [
                    'type' => 'apiKey',
                    'name' => 'X-API-Key',
                    'in' => 'header',
                ],
                'oauth2' => [
                    'type' => 'oauth2',
                    'flows' => [
                        'implicit' => [
                            'authorizationUrl' => 'https://example.com/oauth/authorize',
                            'scopes' => [
                                'read:users' => 'Read user data',
                            ],
                        ],
                    ],
                ],
                'valid_scheme' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ValidSecurityReferencesRule::class);

    $errorList = $errors->getErrors();
    expect($errorList)->toHaveCount(2);
    expect(collect($errorList)->pluck('path')->toArray())->toContain('security.1.invalid_global', 'paths./users.get.security.1.missing_scheme');
    $globalError = collect($errorList)->firstWhere('path', 'security.1.invalid_global');
    expect($globalError->message)->toBe('Security requirement references undefined security scheme. Available schemes: api_key, oauth2, valid_scheme');
});
