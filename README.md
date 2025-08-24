# Spectrum

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bambamboole/spectrum.svg?style=flat-square)](https://packagist.org/packages/bambamboole/spectrum)
[![Total Downloads](https://img.shields.io/packagist/dt/bambamboole/spectrum.svg?style=flat-square)](https://packagist.org/packages/bambamboole/spectrum)
![GitHub Actions](https://github.com/bambamboole/spectrum/actions/workflows/main.yml/badge.svg)

A modern PHP CLI tool for parsing, validating, and dereferencing OpenAPI 3.1.1 specifications. What started as a weekend
project to scratch my own itch has grown into something potentially useful for the broader PHP community. Let's see
where this goes!

## Features

- **Parse** OpenAPI 3.1.1 specifications from JSON or YAML
- **Validate** against (not yet) comprehensive ruleset with detailed error reporting
- **Dereference** all `$ref` links to create standalone specifications
- **Custom rulesets** for project-specific validation requirements
- **Extensible architecture** for adding new validation rules

## Installation

### Download Binary

```bash
curl -L https://example.com/releases/latest/spectrum -o spectrum
chmod +x spectrum
sudo mv spectrum /usr/local/bin/
```

## Usage

### Validate OpenAPI Specifications

```bash
# Basic validation
spectrum openapi.yaml

# With custom ruleset
spectrum openapi.yaml --ruleset=strict-rules.yaml

# JSON output for CI/CD
spectrum openapi.yaml --format=json --output=validation-report.json
```

### Dereference Specifications

```bash
# Resolve all $ref links
spectrum deref openapi.yaml

# Output to file
spectrum deref openapi.yaml --output=resolved.yaml

# Convert format while dereferencing
spectrum deref openapi.json --format=yaml --output=openapi.yaml
```

### Create Custom Rulesets

```bash
# Interactive ruleset creation
spectrum create:ruleset

# Quick ruleset from template
spectrum create:ruleset my-rules --template=strict
```

## Validation Rules

Spectrum includes comprehensive validation for:

- **Required fields** across all OpenAPI objects
- **Path parameter** consistency between paths and operations
- **Response codes** format and validity
- **Security reference** integrity
- **Schema compliance** with OpenAPI 3.1.1 specification
- **Custom rules** via extensible ruleset system

## Output Formats

### Table (Default)

Clean, human-readable validation results with severity indicators.

### JSON

Machine-readable format perfect for CI/CD integration:

```json
{
    "valid": false,
    "errors": [
        {
            "severity": "error",
            "message": "Missing required field 'info'",
            "path": "#/"
        }
    ]
}
```

### Compact

Minimal output showing only error count and critical issues.

## Custom Rulesets

Create YAML rulesets to customize validation behavior:

```yaml
name: "My API Standards"
description: "Custom validation rules for our APIs"
rules:
    - RequiredFieldsRule
    - PathParametersRule
    - ResponseCodeRule
    -   class: "App\\Validation\\Rules\\Semver"
        config:
            strict: true
```

## Requirements

- PHP 8.2+
- Composer (for library installation)

## Development

```bash
# Clone repository
git clone https://github.com/bambamboole/spectrum.git
cd spectrum

# Install dependencies
composer install

# Run tests
composer test

# Static analysis
composer phpstan:analyse

# Code formatting
composer lint
```

## Testing

Spectrum uses Pest for testing with comprehensive coverage of real-world OpenAPI specifications:

```bash
composer test
composer test:coverage  # Generate HTML coverage report
```

## Contributing

Contributions welcome! This project follows these principles:

- **Self-documenting code** over extensive comments
- **Meaningful tests** over quantity metrics
- **Real-world examples** in tests and documentation
- **Strict typing** throughout

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

## Architecture

- **Parser**: Converts YAML/JSON to typed PHP objects
- **Validator**: Applies configurable rulesets with detailed reporting
- **Dereferencer**: Resolves `$ref` links with circular reference detection
- **CLI**: Laravel Zero-based commands with rich output formatting


## License

MIT License. See [LICENSE.md](LICENSE.md) for details.

## Credits

- [Manuel Christlieb](https://github.com/bambamboole) - Creator
- [All Contributors](../../contributors)

Built with [Laravel Zero](https://laravel-zero.com/) and [Pest](https://pestphp.com/).
