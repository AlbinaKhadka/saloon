<?php

return [

    'default' => 'default',

    'documentations' => [

        /*
        |--------------------------------------------------------------------------
        | Default / Authorization API Documentation
        |--------------------------------------------------------------------------
        */

        'default' => [

            'api' => [
                'title' => 'Authorization of Saloon',
            ],

            'routes' => [

                /*
                * Swagger UI route
                */

                'api' => 'api/documentation',

                /*
                * Generated documentation route
                */

                'docs' => 'docs',
            ],

            'paths' => [

                /*
                * Edit to include full URL in UI for assets
                */

                'use_absolute_path' => env(
                    'L5_SWAGGER_USE_ABSOLUTE_PATH',
                    true
                ),

                /*
                * Swagger UI assets
                */

                'swagger_ui_assets_path' => env(
                    'L5_SWAGGER_UI_ASSETS_PATH',
                    'vendor/swagger-api/swagger-ui/dist/'
                ),

                /*
                * Generated JSON documentation filename
                */

                'docs_json' => 'api-docs.json',

                /*
                * Generated YAML documentation filename
                */

                'docs_yaml' => 'api-docs.yaml',

                /*
                * Format used by Swagger UI
                */

                'format_to_use_for_docs' => env(
                    'L5_FORMAT_TO_USE_FOR_DOCS',
                    'json'
                ),

                /*
                * Swagger annotations
                */

                'annotations' => [
                    base_path('app/Http/Controllers'),
                    base_path('app/Models'),
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Public API Documentation
        |--------------------------------------------------------------------------
        */

        'public' => [

            'api' => [
                'title' => 'Public Website API (No Auth)',
            ],

            'routes' => [

                'api'             => 'api/public-documentation',
                'docs'            => 'public-docs',
                'oauth2_callback' => 'api/public-oauth2-callback',
            ],

            'paths' => [

                /*
                * Include full URL in UI for assets
                */

                'use_absolute_path' => env(
                    'L5_SWAGGER_USE_ABSOLUTE_PATH',
                    true
                ),

                /*
                * Swagger UI assets
                */

                'swagger_ui_assets_path' => env(
                    'L5_SWAGGER_UI_ASSETS_PATH',
                    'vendor/swagger-api/swagger-ui/dist/'
                ),

                /*
                * Generated JSON documentation filename
                */

                'docs_json' => 'public-api-docs.json',

                /*
                * Generated YAML documentation filename
                */

                'docs_yaml' => 'public-api-docs.yaml',

                /*
                * Format used by Swagger UI
                */

                'format_to_use_for_docs' => env(
                    'L5_FORMAT_TO_USE_FOR_DOCS',
                    'json'
                ),

                /*
                * Public Swagger annotations
                */

                'annotations' => [
                    base_path('app/Docs/Public'),
                    base_path('app/Models'),
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [

        'routes' => [

            /*
            * Route for accessing parsed Swagger annotations.
            */

            'docs' => 'docs',

            /*
            * OAuth2 callback
            */

            'oauth2_callback' => 'api/oauth2-callback',

            /*
            * Middleware
            */

            'middleware' => [
                'api'             => [],
                'asset'           => [],
                'docs'            => [],
                'oauth2_callback' => [],
            ],

            /*
            * Route group options
            */

            'group_options' => [],
        ],

        /*
        |--------------------------------------------------------------------------
        | Paths
        |--------------------------------------------------------------------------
        */

        'paths' => [

            /*
            * Where generated documentation is stored
            */

            'docs' => storage_path('api-docs'),

            /*
            * Swagger UI views
            */

            'views' => base_path(
                'resources/views/vendor/l5-swagger'
            ),

            /*
            * API base path
            */

            'base' => env(
                'L5_SWAGGER_BASE_PATH',
                null
            ),

            /*
            * Excluded paths
            */

            'excludes' => [],
        ],

        /*
        |--------------------------------------------------------------------------
        | Scan Options
        |--------------------------------------------------------------------------
        */

        'scanOptions' => [

            /*
            * Custom generator
            */

            'generator_factory' => null,

            /*
            * Default processors
            */

            'default_processors_configuration' => [],

            /*
            * Analyser
            */

            'analyser' => null,

            /*
            * Analysis
            */

            'analysis' => null,

            /*
            * Custom processors
            */

            'processors' => [
                // \App\SwaggerProcessors\SchemaQueryParameter::class,
            ],

            /*
            * File pattern
            */

            'pattern' => null,

            /*
            * Excluded directories
            */

            'exclude' => [],

            /*
            * OpenAPI specification version
            */

            'open_api_spec_version' => env(
                'L5_SWAGGER_OPEN_API_SPEC_VERSION',
                \L5Swagger\Generator::OPEN_API_DEFAULT_SPEC_VERSION
            ),
        ],

        /*
        |--------------------------------------------------------------------------
        | Security Definitions
        |--------------------------------------------------------------------------
        */

        'securityDefinitions' => [

            'securitySchemes' => [

                /*
                * Sanctum
                *
                * Uncomment this if you want the Authorize button
                * to accept a Bearer token.
                */

                /*
                'sanctum' => [
                    'type' => 'apiKey',
                    'description' => 'Enter token in format: Bearer {token}',
                    'name' => 'Authorization',
                    'in' => 'header',
                ],
                */
            ],

            'security' => [
                [

                    /*
                    * Example:
                    *
                    * 'sanctum' => []
                    */

                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Generate Documentation
        |--------------------------------------------------------------------------
        */

        'generate_always' => env(
            'L5_SWAGGER_GENERATE_ALWAYS',
            false
        ),

        /*
        * Generate YAML copy
        */

        'generate_yaml_copy' => env(
            'L5_SWAGGER_GENERATE_YAML_COPY',
            false
        ),

        /*
        |--------------------------------------------------------------------------
        | Proxy
        |--------------------------------------------------------------------------
        */

        'proxy' => false,

        /*
        |--------------------------------------------------------------------------
        | Additional Config URL
        |--------------------------------------------------------------------------
        */

        'additional_config_url' => null,

        /*
        |--------------------------------------------------------------------------
        | Operations Sort
        |--------------------------------------------------------------------------
        */

        'operations_sort' => env(
            'L5_SWAGGER_OPERATIONS_SORT',
            null
        ),

        /*
        |--------------------------------------------------------------------------
        | Validator
        |--------------------------------------------------------------------------
        */

        'validator_url' => null,

        /*
        |--------------------------------------------------------------------------
        | Swagger UI
        |--------------------------------------------------------------------------
        */

        'ui' => [

            'display' => [

                /*
                * Dark mode
                */

                'dark_mode' => env(
                    'L5_SWAGGER_UI_DARK_MODE',
                    false
                ),

                /*
                * Documentation expansion
                */

                'doc_expansion' => env(
                    'L5_SWAGGER_UI_DOC_EXPANSION',
                    'none'
                ),

                /*
                * Filter
                */

                'filter' => env(
                    'L5_SWAGGER_UI_FILTERS',
                    true
                ),
            ],

            'authorization' => [

                /*
                * Send credentials
                */

                'withCredentials' => true,

                /*
                * Persist authorization
                */

                'persist_authorization' => env(
                    'L5_SWAGGER_UI_PERSIST_AUTHORIZATION',
                    false
                ),

                'oauth2' => [

                    /*
                    * PKCE
                    */

                    'use_pkce_with_authorization_code_grant' => false,
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Constants
        |--------------------------------------------------------------------------
        */

        'constants' => [

            'L5_SWAGGER_CONST_HOST' => env(
                'L5_SWAGGER_CONST_HOST',
                'http://my-default-host.com'
            ),
        ],
    ],

];
