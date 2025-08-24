<?php declare(strict_types=1);

test('requires path argument', function () {
    // Test that the command fails when no path is provided
    expect(fn () => $this->artisan('dereference'))
        ->toThrow(RuntimeException::class, 'Not enough arguments (missing: "path")');
});

test('fails with non-existent file', function () {
    $this->artisan('dereference', [
        'path' => 'non-existent-file.yaml',
    ])
        ->assertExitCode(1)
        ->expectsOutputToContain('File not found: non-existent-file.yaml');
});

test('dereferences simple spec successfully', function () {
    $this->artisan('dereference', [
        'path' => __DIR__.'/../Fixtures/valid-spec.yaml',
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('openapi:');
});

test('outputs JSON format when requested', function () {
    $this->artisan('dereference', [
        'path' => __DIR__.'/../Fixtures/valid-spec.yaml',
        '--format' => 'json',
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('"openapi":');
});

test('outputs YAML format by default', function () {
    $this->artisan('dereference', [
        'path' => __DIR__.'/../Fixtures/valid-spec.yaml',
        '--format' => 'yaml',
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('openapi:')
        ->doesntExpectOutputToContain('"openapi":');
});

test('writes output to file when specified', function () {
    $outputFile = 'tests/Fixtures/dereferenced-output.json';

    // Ensure file doesn't exist
    if (file_exists($outputFile)) {
        unlink($outputFile);
    }

    $this->artisan('dereference', [
        'path' => __DIR__.'/../Fixtures/valid-spec.yaml',
        '--format' => 'json',
        '--output' => $outputFile,
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('Dereferencing OpenAPI specification')
        ->expectsOutputToContain("Dereferenced specification written to: {$outputFile}");

    // Verify file was created with valid content
    expect(file_exists($outputFile))->toBeTrue();

    $content = file_get_contents($outputFile);
    $decoded = json_decode($content, true);
    expect($decoded)->not->toBeNull();
    expect($decoded['openapi'])->toBe('3.1.1');

    // Clean up
    unlink($outputFile);
});

test('detects input format from file extension', function () {
    // Test with JSON input - should output JSON by default
    $jsonFile = 'tests/Fixtures/test-input.json';
    file_put_contents($jsonFile, json_encode([
        'openapi' => '3.1.1',
        'info' => ['title' => 'JSON Test', 'version' => '1.0.0'],
        'paths' => [],
    ]));

    $this->artisan('dereference', [
        'path' => $jsonFile,
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('"openapi":'); // Should be JSON format

    unlink($jsonFile);
});

test('stdout output excludes progress messages', function () {
    $this->artisan('dereference', [
        'path' => __DIR__.'/../Fixtures/valid-spec.yaml',
        '--format' => 'json',
    ])
        ->assertExitCode(0)
        ->doesntExpectOutputToContain('Dereferencing OpenAPI specification')
        ->doesntExpectOutputToContain('Parsing and resolving');
});

test('file output includes progress messages', function () {
    $outputFile = 'tests/Fixtures/progress-test.yaml';

    if (file_exists($outputFile)) {
        unlink($outputFile);
    }

    $this->artisan('dereference', [
        'path' => __DIR__.'/../Fixtures/valid-spec.yaml',
        '--output' => $outputFile,
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('Dereferencing OpenAPI specification')
        ->expectsOutputToContain('Parsing and resolving all references')
        ->expectsOutputToContain("Dereferenced specification written to: {$outputFile}");

    expect(file_exists($outputFile))->toBeTrue();
    unlink($outputFile);
});

test('handles invalid format option', function () {
    $this->artisan('dereference', [
        'path' => __DIR__.'/../Fixtures/valid-spec.yaml',
        '--format' => 'xml',
    ])
        ->assertExitCode(1)
        ->expectsOutputToContain('Invalid format \'xml\'. Valid formats: json, yaml');
});

test('processes external references successfully', function () {
    $this->artisan('dereference', [
        'path' => __DIR__.'/../Fixtures/external/main.yaml',
        '--format' => 'json',
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('"openapi":');
});

test('produces clean output without empty properties', function () {
    $this->artisan('dereference', [
        'path' => __DIR__.'/../Fixtures/valid-spec.yaml',
        '--format' => 'yaml',
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('openapi:')
        ->doesntExpectOutputToContain('x: {}')
        ->doesntExpectOutputToContain('x: []');
});
