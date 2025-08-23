<?php declare(strict_types=1);
namespace Bambamboole\OpenApi\Factories;

use Bambamboole\OpenApi\Context\ParsingContext;
use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Factories\Concerns\ValidatesOpenApiObjects;
use Bambamboole\OpenApi\Objects\DocumentSecurity;
use Bambamboole\OpenApi\Objects\Security;
use Bambamboole\OpenApi\Objects\SecurityScheme;

use function collect;

class OpenApiSecurityFactory
{
    use ValidatesOpenApiObjects;

    public function __construct(ParsingContext $context)
    {
        $this->context = $context;
    }

    public static function create(ParsingContext $context): self
    {
        return new self($context);
    }

    public function validateAndCreateDocumentSecurity(array $data): DocumentSecurity
    {
        $securitySchemes = collect($data['components']['securitySchemes'] ?? [])
            ->map(fn ($securityScheme, $key) => $this->createSecurityScheme($securityScheme, "components.securitySchemes.{$key}"))
            ->all();

        $security = [];
        if (isset($data['security'])) {
            $this->validateSecurity($data['security'], $securitySchemes);
            $security = collect($data['security'])
                ->map(fn ($securityRequirement, $index) => $this->createSecurity($securityRequirement, "security.{$index}"))
                ->all();
        }

        return new DocumentSecurity($security, $securitySchemes);
    }

    public function createSecurityScheme(array $data, string $keyPrefix = ''): SecurityScheme
    {
        $this->validate($data, SecurityScheme::class, $keyPrefix);

        return new SecurityScheme(
            type: $data['type'],
            description: $data['description'] ?? null,
            name: $data['name'] ?? null,
            in: $data['in'] ?? null,
            scheme: $data['scheme'] ?? null,
            bearerFormat: $data['bearerFormat'] ?? null,
            flows: $data['flows'] ?? null,
            openIdConnectUrl: $data['openIdConnectUrl'] ?? null,
        );
    }

    public function createSecurity(array $data, string $keyPrefix = ''): Security
    {
        $this->validate($data, Security::class, $keyPrefix);

        return new Security(
            requirements: $data,
        );
    }

    private function validateSecurity(array $security, array $securitySchemes): void
    {
        $schemeNames = array_keys($securitySchemes);
        $errors = [];

        foreach ($security as $index => $securityRequirement) {
            if (! is_array($securityRequirement)) {
                $errors["security.{$index}"] = ['Security requirement must be an object.'];

                continue;
            }

            foreach (array_keys($securityRequirement) as $schemeName) {
                if (! in_array($schemeName, $schemeNames, true)) {
                    $errors["security.{$index}.{$schemeName}"] = [
                        "Security scheme '{$schemeName}' is not defined in components.securitySchemes.",
                    ];
                }
            }
        }

        if (! empty($errors)) {
            throw ParseException::withMessages($errors);
        }
    }
}
