<?php declare(strict_types=1);

namespace Bambamboole\OpenApi;

use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\Validation\Validator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\Factory;
use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;

class OpenApiParser
{
    protected static ?self $instance = null;

    public function __construct(
        protected readonly Filesystem $fs,
        protected readonly Factory $http
    ) {}

    public static function make(): self
    {
        if (! self::$instance) {
            self::$instance = new self(new Filesystem, new Factory);
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

    public function parseFile(string $filePath): OpenApiDocument
    {
        if (! $this->fs->exists($filePath)) {
            throw new InvalidArgumentException("File not found: {$filePath}");
        }

        // Use the dereferencer to fully resolve all references upfront
        $dereferencer = new OpenApiDereferencer($this->fs, $this->http);
        $data = $dereferencer->dereferenceFile($filePath);

        return OpenApiDocument::fromArray($data);
    }

    public function parseUrl(string $url): OpenApiDocument
    {
        // Use the dereferencer to fully resolve all references upfront
        $dereferencer = new OpenApiDereferencer($this->fs, $this->http);
        $data = $dereferencer->dereferenceUrl($url);

        return OpenApiDocument::fromArray($data);
    }

    public function parseArray(array $data): OpenApiDocument
    {
        // For in-memory parsing, only resolve internal references (no external files)
        $dereferencer = new OpenApiDereferencer($this->fs, $this->http);
        $dereferencedData = $dereferencer->dereferenceArray($data);

        return OpenApiDocument::fromArray($dereferencedData);
    }

    public function enablePerformanceMode(): self
    {
        Validator::enablePerformanceMode();

        return $this;
    }

    public function disablePerformanceMode(): self
    {
        Validator::disablePerformanceMode();

        return $this;
    }

    public function isPerformanceModeEnabled(): bool
    {
        return Validator::isPerformanceModeEnabled();
    }
}
