<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas;

interface SchemaFixtureInterface
{
    /**
     * Whether this fixture should pass validation
     */
    public function passes(): bool;

    /**
     * The OpenAPI schema/document to test
     */
    public function schema(): array;

    /**
     * Expected validation rules that should be applied
     */
    public function rules(): array;

    /**
     * Expected validation violations (for fixtures that should fail)
     */
    public function violations(): array;
}
