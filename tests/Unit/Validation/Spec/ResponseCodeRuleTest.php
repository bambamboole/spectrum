<?php declare(strict_types=1);

use App\Objects\OpenApiDocument;
use App\Validation\Spec\ResponseCodeRule;
use App\Validation\Validator;

it('passes validation with standard HTTP status codes', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '200' => ['description' => 'Success'],
                        '404' => ['description' => 'Not Found'],
                        '500' => ['description' => 'Internal Server Error'],
                    ],
                ],
                'post' => [
                    'responses' => [
                        '201' => ['description' => 'Created'],
                        '400' => ['description' => 'Bad Request'],
                        'default' => ['description' => 'Unexpected error'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ResponseCodeRule::class);

    expect($errors->getErrors())->toBeEmpty();
    expect($errors->getWarnings())->toBeEmpty();
});

it('fails validation with invalid response codes', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '999' => ['description' => 'Invalid high code'],
                        '99' => ['description' => 'Invalid low code'],
                        'invalid' => ['description' => 'Non-numeric code'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ResponseCodeRule::class);

    $errorList = $errors->getErrors();
    expect($errorList)->toHaveCount(3);
    expect(collect($errorList)->pluck('path')->toArray())->toContain(
        'paths./users.get.responses.999',
        'paths./users.get.responses.99',
        'paths./users.get.responses.invalid'
    );

    $error999 = collect($errorList)->firstWhere('path', 'paths./users.get.responses.999');
    $error99 = collect($errorList)->firstWhere('path', 'paths./users.get.responses.99');
    $errorInvalid = collect($errorList)->firstWhere('path', 'paths./users.get.responses.invalid');

    expect($error999->message)->toBe('Response code \'999\' must be between 100-599');
    expect($error99->message)->toBe('Response code \'99\' must be between 100-599');
    expect($errorInvalid->message)->toBe('Response code \'invalid\' must be a valid HTTP status code or \'default\'');
});

it('warns about non-standard HTTP status codes', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '200' => ['description' => 'Success'],
                        '299' => ['description' => 'Non-standard success code'],
                        '420' => ['description' => 'Non-standard client error'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ResponseCodeRule::class);

    $warningList = $errors->getWarnings();
    expect($warningList)->toHaveCount(2);
    expect(collect($warningList)->pluck('path')->toArray())->toContain(
        'paths./users.get.responses.299',
        'paths./users.get.responses.420'
    );

    $warning299 = collect($warningList)->firstWhere('path', 'paths./users.get.responses.299');
    expect($warning299->message)->toBe('Response code \'299\' is not a standard HTTP status code');
});

it('accepts default response code', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '200' => ['description' => 'Success'],
                        'default' => ['description' => 'Unexpected error'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ResponseCodeRule::class);

    expect($errors->getErrors())->toBeEmpty();
    expect($errors->getWarnings())->toBeEmpty();
});

it('suggests adding client error responses for mutating operations', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'post' => [
                    'responses' => [
                        '201' => ['description' => 'Created'],
                        // No 4xx errors
                    ],
                ],
                'put' => [
                    'responses' => [
                        '200' => ['description' => 'Updated'],
                        // No 4xx errors
                    ],
                ],
                'get' => [
                    'responses' => [
                        '200' => ['description' => 'Success'],
                        // GET operations don't get 4xx suggestions
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ResponseCodeRule::class);

    $infoList = $errors->getInfo();
    expect(collect($infoList)->pluck('path')->toArray())->toContain(
        'paths./users.post.responses',
        'paths./users.put.responses',
        'paths./users.get.responses'
    );
});

it('suggests adding server error responses', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '200' => ['description' => 'Success'],
                        '404' => ['description' => 'Not Found'],
                        // No 5xx or default
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ResponseCodeRule::class);

    $infoList = $errors->getInfo();
    expect($infoList)->toHaveCount(1);
    expect($infoList[0]->path)->toBe('paths./users.get.responses');
    expect($infoList[0]->message)->toBe('Consider adding a 5xx server error response or \'default\' response');
});

it('does not suggest error responses when default is present', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'post' => [
                    'responses' => [
                        '201' => ['description' => 'Created'],
                        'default' => ['description' => 'Error'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ResponseCodeRule::class);

    expect($errors->getInfo())->toBeEmpty();
});

it('handles mixed response codes correctly', function () {
    $document = OpenApiDocument::fromArray($this->schema([
        'paths' => [
            '/users' => [
                'get' => [
                    'responses' => [
                        '200' => ['description' => 'Success'],
                        '400' => ['description' => 'Bad Request'],
                        '500' => ['description' => 'Internal Server Error'],
                        '299' => ['description' => 'Non-standard but valid range'],
                        '999' => ['description' => 'Invalid range'],
                    ],
                ],
            ],
        ],
    ]));

    $errors = Validator::validateDocument($document, ResponseCodeRule::class);

    $errorList = $errors->getErrors();
    $warningList = $errors->getWarnings();

    // Should have at least one error for invalid response code 999
    expect($errorList)->not->toBeEmpty();

    // Should have at least one warning for non-standard code 299
    expect($warningList)->not->toBeEmpty();

    // Should have paths containing the response codes we're testing
    $allErrorPaths = collect($errorList)->pluck('path')->toArray();
    $allWarningPaths = collect($warningList)->pluck('path')->toArray();

    expect($allErrorPaths)->toContain('paths./users.get.responses.999');
    expect($allWarningPaths)->toContain('paths./users.get.responses.299');
});
