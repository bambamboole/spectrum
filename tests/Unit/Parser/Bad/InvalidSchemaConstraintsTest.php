<?php declare(strict_types=1);

use Bambamboole\OpenApi\Exceptions\ParseException;

it('rejects schema with invalid constraints', function () {
    $this->expectSchema([
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'Test API',
            'version' => '1.0.0',
        ],
        'paths' => [],
        'components' => [
            'schemas' => [
                'InvalidSchema' => [
                    'type' => 'string',
                    'minLength' => 10,
                    'maxLength' => 5, // maxLength < minLength (invalid)
                ],
            ],
        ],
    ])->toThrow(function (ParseException $e) {
        expect($e->getMessages())->toBe([
            'components.schemas.InvalidSchema.maxLength' => [
                'The max length must be greater than or equal 10.',
            ],
        ]);
    });
});
