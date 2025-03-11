<?php

return [

    'connections' => [

        'default' => [
            'driver' => env('DB_DRIVER', 'mysql'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => sqlite_database_path(env('DB_DATABASE'), env('DB_DRIVER')),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'engine' => 'InnoDB',
        ],

        'legacy' => [
            'driver' => env('DB_LEGACY_DRIVER', 'mysql'),
            'host' => env('DB_LEGACY_HOST', '127.0.0.1'),
            'port' => env('DB_LEGACY_PORT', '3306'),
            'database' => sqlite_database_path(env('DB_LEGACY_DATABASE'), env('DB_LEGACY_DRIVER')),
            'username' => env('DB_LEGACY_USERNAME', 'forge'),
            'password' => env('DB_LEGACY_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'engine' => 'InnoDB',
        ],

    ],

];
