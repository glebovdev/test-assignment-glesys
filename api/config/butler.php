<?php

return [

    'audit' => [

        'url' => env('BUTLER_AUDIT_URL'),

        'token' => env('BUTLER_AUDIT_TOKEN'),

        'driver' => env('BUTLER_AUDIT_DRIVER'),

    ],

    'graphql' => [

        'include_debug_message' => env('BUTLER_GRAPHQL_INCLUDE_DEBUG_MESSAGE', false),
        'include_trace' => env('BUTLER_GRAPHQL_INCLUDE_TRACE', false),

        'namespace' => env('BUTLER_GRAPHQL_NAMESPACE', 'App\\Http\\Graphql\\'),

        'schema' => env('BUTLER_GRAPHQL_SCHEMA', base_path('app/Http/Graphql/schema.graphql')),

    ],

    'service' => [

        'extra' => [
            'config' => [
                'trustedproxy.proxies' => [
                    '10.0.0.0/24', // Traefik
                    '10.255.0.0/16', // Docker ingress
                ],
            ],
        ],

    ],

];
