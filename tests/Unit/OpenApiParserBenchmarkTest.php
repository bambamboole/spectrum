<?php declare(strict_types=1);

use Bambamboole\OpenApi\Objects\OpenApiDocument;
use Bambamboole\OpenApi\OpenApiDereferencer;
use Bambamboole\OpenApi\OpenApiParser;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Benchmark;

describe('OpenApiParserBenchmark', function () {
    it('benchmarks parsing performance components', function () {
        $parser = OpenApiParser::make();
        $dereferencer = new OpenApiDereferencer(new Filesystem, new Factory);

        // Test with a small specification
        $smallSpec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
            ],
            'paths' => [
                '/test' => [
                    'get' => [
                        'responses' => [
                            '200' => [
                                'description' => 'Success',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $smallJson = json_encode($smallSpec);

        // Benchmark small spec parsing
        $results = Benchmark::measure([
            'JSON decode only' => fn () => json_decode($smallJson, true),
            'Dereferencer only' => fn () => $dereferencer->dereferenceArray($smallSpec),
            'OpenApiDocument::fromArray only' => fn () => OpenApiDocument::fromArray($smallSpec),
            'Full parseJson' => fn () => $parser->parseJson($smallJson),
            'Full parseArray' => fn () => $parser->parseArray($smallSpec),
        ]);

        echo "\n=== Small Specification Benchmark ===\n";
        foreach ($results as $name => $time) {
            echo sprintf("%-30s: %s ms\n", $name, number_format($time, 2));
        }

        expect($results)->toBeArray();
    });

    it('benchmarks large specification parsing', function () {
        $digitalOceanPath = dirname(__DIR__, 2).'/digitalocean-openapi/specification/DigitalOcean-public.v2.yaml';

        if (! file_exists($digitalOceanPath)) {
            skip('DigitalOcean specification not available');
        }

        $parser = OpenApiParser::make();
        $dereferencer = new OpenApiDereferencer(new Filesystem, new Factory);
        $fs = new Filesystem;

        // Load the file content
        $yamlContent = $fs->get($digitalOceanPath);

        $results = Benchmark::measure([
            'YAML parse only' => fn () => \Symfony\Component\Yaml\Yaml::parse($yamlContent),
            'Dereferencer only' => fn () => $dereferencer->dereferenceFile($digitalOceanPath),
            'Full parseFile' => fn () => $parser->parseFile($digitalOceanPath),
        ], 1); // Only run once due to size

        dump($results);
        echo "\n=== Large Specification (DigitalOcean) Benchmark ===\n";
        foreach ($results as $name => $time) {
            echo sprintf("%-30s: %s ms\n", $name, number_format($time, 2));
        }

        expect($results)->toBeArray();
    });

    it('profiles object creation performance', function () {
        // Create a specification with many objects to test object creation overhead
        $manyObjectsSpec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
            ],
            'components' => [
                'schemas' => [],
            ],
            'paths' => [],
        ];

        // Add 100 schemas
        for ($i = 0; $i < 100; $i++) {
            $manyObjectsSpec['components']['schemas']["Schema{$i}"] = [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ];
        }

        // Add 50 paths
        for ($i = 0; $i < 50; $i++) {
            $manyObjectsSpec['paths']["/resource{$i}"] = [
                'get' => [
                    'responses' => [
                        '200' => [
                            'description' => 'Success',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => "#/components/schemas/Schema{$i}",
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'post' => [
                    'requestBody' => [
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => "#/components/schemas/Schema{$i}",
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'Created',
                        ],
                    ],
                ],
            ];
        }

        $parser = OpenApiParser::make();
        $dereferencer = new OpenApiDereferencer(new Filesystem, new Factory);

        $results = Benchmark::measure([
            'Dereferencer with many objects' => fn () => $dereferencer->dereferenceArray($manyObjectsSpec),
            'Object creation with many objects' => function () use ($manyObjectsSpec) {
                $dereferencer = new OpenApiDereferencer(new Filesystem, new Factory);
                $dereferenced = $dereferencer->dereferenceArray($manyObjectsSpec);

                return OpenApiDocument::fromArray($dereferenced);
            },
            'Full parsing with many objects' => fn () => $parser->parseArray($manyObjectsSpec),
        ]);

        echo "\n=== Many Objects Specification Benchmark ===\n";
        foreach ($results as $name => $time) {
            echo sprintf("%-30s: %s ms\n", $name, number_format($time, 2));
        }

        expect($results)->toBeArray();
    });

    it('benchmarks performance mode impact', function () {
        $parser = OpenApiParser::make();

        // Test with moderate number of objects
        $testSpec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Performance Test API',
                'version' => '1.0.0',
            ],
            'components' => [
                'schemas' => [],
            ],
            'paths' => [],
        ];

        // Add 100 schemas and 50 paths for meaningful comparison
        for ($i = 0; $i < 100; $i++) {
            $testSpec['components']['schemas']["Schema{$i}"] = [
                'type' => 'string',
            ];
        }

        for ($i = 0; $i < 50; $i++) {
            $testSpec['paths']["/test{$i}"] = [
                'get' => [
                    'responses' => [
                        '200' => [
                            'description' => 'Success',
                        ],
                    ],
                ],
            ];
        }

        $results = Benchmark::measure([
            'Normal mode (with validation)' => function () use ($parser, $testSpec) {
                $parser->disablePerformanceMode();

                return $parser->parseArray($testSpec);
            },
            'Performance mode (skip validation)' => function () use ($parser, $testSpec) {
                $parser->enablePerformanceMode();

                return $parser->parseArray($testSpec);
            },
        ]);

        echo '
=== Performance Mode Impact (100 schemas + 50 paths) ===
';
        foreach ($results as $name => $time) {
            echo sprintf('%-35s: %s ms
', $name, number_format($time, 2));
        }

        $speedup = $results['Normal mode (with validation)'] / $results['Performance mode (skip validation)'];
        echo sprintf('Speedup in performance mode: %.1fx faster
', $speedup);

        // Reset to normal mode
        $parser->disablePerformanceMode();

        expect($results)->toBeArray();
        expect($speedup)->toBeGreaterThan(10); // Expect at least 10x speedup
    });

})->skip('Performance benchmark - run manually');
