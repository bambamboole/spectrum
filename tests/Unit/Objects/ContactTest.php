<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\Contact;

it('can create empty Contact', function () {
    $contact = Contact::fromArray([]);

    expect($contact->name)->toBeNull()
        ->and($contact->email)->toBeNull()
        ->and($contact->url)->toBeNull();
});

it('can create Contact with all fields', function () {
    $contact = Contact::fromArray([
        'name' => 'API Support',
        'email' => 'support@example.com',
        'url' => 'https://example.com/support',
    ]);

    expect($contact->name)->toBe('API Support')
        ->and($contact->email)->toBe('support@example.com')
        ->and($contact->url)->toBe('https://example.com/support');
});

it('validates email format', function () {
    expect(fn () => Contact::fromArray([
        'email' => 'invalid-email',
    ]))
        ->toThrow(ParseException::class, 'email must be a valid email address');
});

it('validates url format', function () {
    expect(fn () => Contact::fromArray([
        'url' => 'not-a-url',
    ]))
        ->toThrow(ParseException::class, 'url must be a valid URL');
});
