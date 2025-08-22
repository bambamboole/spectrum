# Development Guidelines

## Project Overview

This PHP package provides parsing and validation capabilities for OpenAPI specifications. It aims to be a modern, well-maintained alternative to existing implementations.

## References

- **OpenAPI Specification**: https://spec.openapis.org/oas/v3.1.1.html
- **Reference Implementation**: https://github.com/cebe/php-openapi (for comparison, but note it's unmaintained)

## Code Standards

### Strict Types
Always declare strict types at the beginning of every PHP file:

```php
<?php

declare(strict_types=1);
```

### Self-Explanatory Code
Write code that speaks for itself. Choose descriptive variable names, method names, and class names that make the intent clear without requiring extensive documentation.

**Good:**
```php
public function validateRequestBodySchema(array $requestBody, Schema $schema): ValidationResult
{
    return $this->schemaValidator->validate($requestBody, $schema);
}
```

**Avoid:**
```php
// Validates the request body against the schema
public function validate($data, $schema): mixed
{
    return $this->validator->check($data, $schema);
}
```

### Comments
Comments should be used sparingly and only when:
- Complex business logic requires explanation
- Non-obvious algorithmic decisions need justification
- Workarounds for external dependencies need context

Avoid comments that simply restate what the code does.

### OpenAPI Constructs

Implement support for all major OpenAPI 3.1.1 constructs:
- **Info Object**: API metadata
- **Paths Object**: Available paths and operations
- **Components Object**: Reusable schemas, responses, parameters, etc.
- **Schema Object**: JSON Schema for data validation
- **Parameter Object**: Operation parameters
- **Request Body Object**: Request body definitions
- **Response Object**: Response definitions
- **Security Scheme Object**: Security definitions

### Error Handling

Use typed exceptions that provide meaningful context:

```php
class InvalidSchemaException extends OpenApiException
{
    public function __construct(string $schemaPath, string $reason, ?\Throwable $previous = null)
    {
        parent::__construct("Invalid schema at '{$schemaPath}': {$reason}", 0, $previous);
    }
}
```

## Testing Standards

### Framework
Use **Pest** for all tests. Leverage its expressive syntax for clear test descriptions.

### Quality Over Quantity
Focus on meaningful tests that:
- Test real-world scenarios
- Cover edge cases and error conditions
- Validate complex business logic
- Ensure backward compatibility

Avoid trivial tests that add no value (e.g., testing simple getters/setters).

### Test Structure
```php
it('validates a complete OpenAPI specification with all components')
    ->expect(fn() => $parser->parse($complexSpecification))
    ->not->toThrow()
    ->and(fn() => $parser->parse($complexSpecification)->isValid())
    ->toBeTrue();

it('rejects invalid schema references')
    ->expect(fn() => $parser->parse($specWithInvalidRef))
    ->toThrow(InvalidSchemaException::class, 'Schema reference not found');
```

### Test Data
Store test schemas and specifications in `tests/schemas/` directory. Use realistic examples from actual OpenAPI specifications when possible.

## Architecture Principles

### Single Responsibility
Each class should have one clear purpose:
- `OpenApiParser`: Parse OpenAPI documents
- `SchemaValidator`: Validate data against schemas
- `ComponentResolver`: Resolve $ref references

### Immutability
Prefer immutable objects where possible. Use readonly properties and return new instances for modifications.

### Type Safety
Leverage PHP's type system extensively:
- Use union types for multi-type parameters
- Implement strict return type declarations
- Use generics in PHPDoc where helpful

## Performance Considerations

- Lazy-load components and schemas
- Cache resolved references
- Use generators for large datasets
- Validate incrementally where possible

## Validation Approach

Follow JSON Schema validation principles:
1. **Structural validation**: Ensure required properties exist
2. **Type validation**: Verify data types match schema
3. **Constraint validation**: Check format, length, range constraints
4. **Cross-reference validation**: Resolve and validate $ref links

## API Design

Design a fluent, intuitive API:

```php
$result = OpenApiParser::create()
    ->withStrictValidation()
    ->enableSchemaCache()
    ->parse($specificationContent);

if ($result->hasErrors()) {
    foreach ($result->getErrors() as $error) {
        echo $error->getMessage();
    }
}
```

## Contributing

When adding new features:
1. Reference the OpenAPI specification section being implemented
2. Include comprehensive tests with real-world examples
3. Update relevant documentation
4. Ensure all static analysis passes (PHPStan)
5. Follow existing code patterns and naming conventions