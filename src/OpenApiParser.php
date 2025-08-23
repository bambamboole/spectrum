<?php declare(strict_types=1);
namespace Bambamboole\OpenApi;

use Bambamboole\OpenApi\Exceptions\ParseException;
use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

class OpenApiParser
{
    protected static ?self $instance = null;

    public function __construct(
        protected readonly Filesystem $fs,
    ) {}

    public static function make(): self
    {
        if (! self::$instance) {
            self::$instance = new self(new Filesystem);
        }

        return self::$instance;
    }

    public function parseJson(string $json): OpenApiDocument
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return $this->parseArray($data);
    }

    public function parseYaml(string $yaml): OpenApiDocument
    {
        return $this->parseArray(Yaml::parse($yaml));
    }

    public function parseFile(string $filePath, array $options = []): OpenApiDocument
    {
        if (! $this->fs->exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        $data = match (Str::afterLast($filePath, '.')) {
            'json' => json_decode($this->fs->get($filePath), true, 512, JSON_THROW_ON_ERROR),
            'yml', 'yaml' => Yaml::parse($this->fs->get($filePath)),
            default => throw new ParseException("Unsupported file type: {$filePath}"),
        };
        ReferenceResolver::initialize($data, $filePath);

        return $this->parseArray($data);
    }

    public function parseArray(array $data): OpenApiDocument
    {
        return OpenApiDocument::fromArray($data);
    }
}
