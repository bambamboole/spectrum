<?php declare(strict_types=1);

namespace App\Objects;

use App\Validation\Validator;

/**
 * The incoming webhooks that may be received as part of this API and that the API consumer may choose to implement.
 * Closely related to the callbacks feature, this section describes requests initiated other than by an API call,
 * for example by an out of band registration.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#fixed-fields-0
 */
readonly class Webhooks extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            // Webhooks is a map of webhook names to PathItem objects
            // Each key should be a valid webhook name, each value should be a PathItem
        ];
    }

    public function __construct(
        /** @var array<string, PathItem> Map of webhook names to PathItem objects */
        public array $webhooks = [],
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        Validator::validate($data, self::rules(), $keyPrefix);

        $webhooks = [];
        foreach ($data as $key => $value) {
            // Skip extension properties
            if (str_starts_with($key, 'x-')) {
                continue;
            }

            // Each webhook value should be a PathItem
            if (is_array($value)) {
                $webhooks[$key] = PathItem::fromArray($value, $keyPrefix.'.'.$key);
            }
        }

        return new self(
            webhooks: $webhooks,
            x: self::extractX($data),
        );
    }

    /**
     * Get a specific webhook by name.
     */
    public function getWebhook(string $name): ?PathItem
    {
        return $this->webhooks[$name] ?? null;
    }

    /**
     * Get all webhook names.
     *
     * @return string[]
     */
    public function getWebhookNames(): array
    {
        return array_keys($this->webhooks);
    }

    /**
     * Check if a webhook exists.
     */
    public function hasWebhook(string $name): bool
    {
        return isset($this->webhooks[$name]);
    }
}
