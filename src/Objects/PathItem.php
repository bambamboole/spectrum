<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Objects;

use Bambamboole\OpenApi\ReferenceResolver;
use Bambamboole\OpenApi\Validation\Validator;

/**
 * Describes the operations available on a single path.
 *
 * @see https://spec.openapis.org/oas/v3.1.1.html#path-item-object
 */
readonly class PathItem extends OpenApiObject
{
    public static function rules(): array
    {
        return [
            'summary' => ['sometimes', 'string', 'filled'],
            'description' => ['sometimes', 'string', 'filled'],
            'get' => ['sometimes', 'array'],
            'put' => ['sometimes', 'array'],
            'post' => ['sometimes', 'array'],
            'delete' => ['sometimes', 'array'],
            'options' => ['sometimes', 'array'],
            'head' => ['sometimes', 'array'],
            'patch' => ['sometimes', 'array'],
            'trace' => ['sometimes', 'array'],
            'servers' => ['sometimes', 'array'],
            'parameters' => ['sometimes', 'array'],
        ];
    }

    public function __construct(
        public ?string $summary = null,
        public ?string $description = null,
        public ?Operation $get = null,
        public ?Operation $put = null,
        public ?Operation $post = null,
        public ?Operation $delete = null,
        public ?Operation $options = null,
        public ?Operation $head = null,
        public ?Operation $patch = null,
        public ?Operation $trace = null,
        /** @var Server[]|null */
        public ?array $servers = null,
        /** @var Parameter[]|null */
        public ?array $parameters = null,
        /** @var array<string, mixed> Specification extensions (x-* properties) */
        public array $x = [],
    ) {}

    public static function fromArray(array $data, string $keyPrefix = ''): self
    {
        $data = ReferenceResolver::resolveRef($data);
        Validator::validate($data, self::rules(), $keyPrefix);

        return new self(
            summary: $data['summary'] ?? null,
            description: $data['description'] ?? null,
            get: isset($data['get']) ? Operation::fromArray($data['get'], $keyPrefix.'.get') : null,
            put: isset($data['put']) ? Operation::fromArray($data['put'], $keyPrefix.'.put') : null,
            post: isset($data['post']) ? Operation::fromArray($data['post'], $keyPrefix.'.post') : null,
            delete: isset($data['delete']) ? Operation::fromArray($data['delete'], $keyPrefix.'.delete') : null,
            options: isset($data['options']) ? Operation::fromArray($data['options'], $keyPrefix.'.options') : null,
            head: isset($data['head']) ? Operation::fromArray($data['head'], $keyPrefix.'.head') : null,
            patch: isset($data['patch']) ? Operation::fromArray($data['patch'], $keyPrefix.'.patch') : null,
            trace: isset($data['trace']) ? Operation::fromArray($data['trace'], $keyPrefix.'.trace') : null,
            servers: isset($data['servers']) ? Server::multiple($data['servers'], $keyPrefix.'.servers') : null,
            parameters: isset($data['parameters']) ? Parameter::multiple($data['parameters'], $keyPrefix.'.parameters') : null,
            x: self::extractX($data),
        );
    }

    /**
     * Get all operations defined on this path item.
     *
     * @return array<string, Operation>
     */
    public function getOperations(): array
    {
        $operations = [];
        $verbs = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'];
        foreach ($verbs as $verb) {
            if (isset($this->$verb)) {
                $operations[$verb] = $this->$verb;
            }
        }

        return $operations;
    }
}
