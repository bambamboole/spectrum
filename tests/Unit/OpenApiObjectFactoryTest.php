<?php declare(strict_types=1);

use Bambamboole\OpenApi\Context\ParsingContext;
use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Factories\OpenApiDocumentFactory;
use Bambamboole\OpenApi\Objects\Components;
use Bambamboole\OpenApi\Objects\Contact;
use Bambamboole\OpenApi\Objects\Info;
use Bambamboole\OpenApi\Objects\Schema;
use Bambamboole\OpenApi\OpenApiParser;

it('can create Info object via factory', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = new OpenApiDocumentFactory($context);

    // Use reflection to access the private createInfo method
    $reflection = new \ReflectionClass($factory);
    $createInfoMethod = $reflection->getMethod('createInfo');
    $createInfoMethod->setAccessible(true);

    $info = $createInfoMethod->invoke($factory, [
        'title' => 'Test API',
        'version' => '1.0.0',
        'description' => 'A test API',
    ]);

    expect($info)->toBeInstanceOf(Info::class)
        ->and($info->title)->toBe('Test API')
        ->and($info->version)->toBe('1.0.0')
        ->and($info->description)->toBe('A test API');
});

it('validates Info object via Laravel validator', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = new OpenApiDocumentFactory($context);

    // Use reflection to access the private createInfo method
    $reflection = new \ReflectionClass($factory);
    $createInfoMethod = $reflection->getMethod('createInfo');
    $createInfoMethod->setAccessible(true);

    try {
        $createInfoMethod->invoke($factory, [
            'version' => '1.0.0', // missing title
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('title')
            ->and($e->getMessages()['title'])->toContain('title is required.');
    }
});

it('validates Contact email via Laravel validator', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = new OpenApiDocumentFactory($context);

    // Use reflection to access the private createContact method
    $reflection = new \ReflectionClass($factory);
    $createContactMethod = $reflection->getMethod('createContact');
    $createContactMethod->setAccessible(true);

    try {
        $createContactMethod->invoke($factory, [
            'email' => 'invalid-email', // invalid email format
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('email')
            ->and($e->getMessages()['email'])->toContain('email must be a valid email address.');
    }
});

it('validates Schema constraints via Laravel validator', function () {
    $context = ParsingContext::fromDocument(['openapi' => '3.0.0', 'info' => [], 'paths' => []]);
    $factory = new OpenApiDocumentFactory($context);

    // Use reflection to access the private createSchema method
    $reflection = new \ReflectionClass($factory);
    $createSchemaMethod = $reflection->getMethod('createSchema');
    $createSchemaMethod->setAccessible(true);

    try {
        $createSchemaMethod->invoke($factory, [
            'type' => 'string',
            'minLength' => -1, // invalid constraint
        ]);
        expect(false)->toBeTrue('Expected ParseException to be thrown');
    } catch (ParseException $e) {
        expect($e->getMessages())->toHaveKey('minLength')
            ->and($e->getMessages()['minLength'])->toContain('The min length must be at least 0.');
    }
});

it('can create complex OpenAPI document via factory', function () {
    $document = OpenApiParser::make()->parseArray([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Factory Test API',
            'version' => '2.0.0',
            'contact' => [
                'email' => 'test@example.com',
                'url' => 'https://example.com',
            ],
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'User' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'type' => 'string',
                            'minLength' => 1,
                        ],
                        'email' => [
                            'type' => 'string',
                            'format' => 'email',
                        ],
                    ],
                    'required' => ['name', 'email'],
                ],
            ],
        ],
    ]);

    expect($document->info->title)->toBe('Factory Test API')
        ->and($document->info->version)->toBe('2.0.0')
        ->and($document->info->contact)->toBeInstanceOf(Contact::class)
        ->and($document->info->contact->email)->toBe('test@example.com')
        ->and($document->components)->toBeInstanceOf(Components::class)
        ->and($document->components->schemas)->toHaveKey('User')
        ->and($document->components->schemas['User'])->toBeInstanceOf(Schema::class)
        ->and($document->components->schemas['User']->type)->toBe('object');
});
