<?php declare(strict_types=1);

test('path argument is required', function () {
    $this->artisan('validate')->assertExitCode(1)->expectsOutputToContain('path argument is required');
});

test('validates valid OpenAPI specification successfully with no issues', function () {
    $this->artisan('validate', [
        'path' => __DIR__.'/../Fixtures/valid-spec.yaml',
    ])
        ->assertExitCode(0)
        ->doesntExpectOutputToContain('Severity:'); // No validation issues should be reported
});

test('detects path parameter validation issues', function () {
    $this->artisan('validate', [
        'path' => __DIR__.'/../Fixtures/invalid-spec.yaml',
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('Severity: WARNING')
        ->expectsTable(['Path', 'message'], [
            ['paths./users/{id}.get.parameters', 'Path parameter \'id\' found in path template but not defined in parameters'],
            ['paths./posts/{postId}/comments/{commentId}.get.parameters', 'Path parameter \'postId\' found in path template but not defined in parameters'],
            ['paths./posts/{postId}/comments/{commentId}.get.parameters', 'Path parameter \'commentId\' found in path template but not defined in parameters'],
        ]);
});

test('validates with custom ruleset making path parameters errors', function () {
    $this->artisan('validate', [
        'path' => __DIR__.'/../Fixtures/invalid-spec.yaml',
        '--ruleset' => __DIR__.'/../Fixtures/custom-ruleset.yaml',
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('Severity: ERROR')
        ->expectsTable(['Path', 'message'], [
            ['paths./users/{id}.get.parameters', 'Path parameter \'id\' found in path template but not defined in parameters'],
            ['paths./posts/{postId}/comments/{commentId}.get.parameters', 'Path parameter \'postId\' found in path template but not defined in parameters'],
            ['paths./posts/{postId}/comments/{commentId}.get.parameters', 'Path parameter \'commentId\' found in path template but not defined in parameters'],
        ]);
});

test('detects empty paths validation warning', function () {
    $this->artisan('validate', [
        'path' => __DIR__.'/../Fixtures/minimal-spec.yaml',
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('Severity: WARNING')
        ->expectsTable(['Path', 'message'], [
            ['paths', 'Paths object should not be empty - API must define at least one path'],
        ]);
});
