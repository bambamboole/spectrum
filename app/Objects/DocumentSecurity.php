<?php declare(strict_types=1);

namespace App\Objects;

class DocumentSecurity
{
    public function __construct(
        /** @var Security[] */
        public readonly array $security,
        /** @var SecurityScheme[] */
        public readonly array $securitySchemes,
    ) {}
}
