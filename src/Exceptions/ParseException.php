<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Exceptions;

class ParseException extends OpenApiException
{
    protected array $messages = [];

    public static function withMessages(array $messages): self
    {
        $exception = new self('Could not parse OpenAPI document');
        $exception->messages = $messages;

        return $exception;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
