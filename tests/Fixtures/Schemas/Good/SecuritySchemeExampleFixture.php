<?php declare(strict_types=1);

namespace Bambamboole\OpenApi\Tests\Fixtures\Schemas\Good;

class SecuritySchemeExampleFixture extends AbstractGoodSchemaFixture
{
    public function schema(): array
    {
        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Security Example API',
                'version' => '1.0.0',
                'description' => 'API demonstrating various security schemes',
            ],
            'paths' => [
                '/users' => [
                    'get' => [
                        'summary' => 'Get users',
                        'responses' => [
                            '200' => [
                                'description' => 'List of users',
                            ],
                        ],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'ApiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-API-Key',
                        'description' => 'API key authentication',
                    ],
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                        'description' => 'JWT Bearer token authentication',
                    ],
                    'OAuth2Auth' => [
                        'type' => 'oauth2',
                        'description' => 'OAuth2 authentication',
                        'flows' => [
                            'authorizationCode' => [
                                'authorizationUrl' => 'https://example.com/oauth/authorize',
                                'tokenUrl' => 'https://example.com/oauth/token',
                                'scopes' => [
                                    'read' => 'Read access',
                                    'write' => 'Write access',
                                ],
                            ],
                        ],
                    ],
                    'OpenIdConnect' => [
                        'type' => 'openIdConnect',
                        'openIdConnectUrl' => 'https://example.com/.well-known/openid_configuration',
                        'description' => 'OpenID Connect authentication',
                    ],
                ],
            ],
            'security' => [
                ['ApiKeyAuth' => []],
                ['BearerAuth' => []],
                ['OAuth2Auth' => ['read', 'write']],
                ['OpenIdConnect' => []],
            ],
        ];
    }
}
