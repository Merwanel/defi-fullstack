<?php

return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
        ],
        'environments' => [
            'default_migration_table' => 'phinxlog',
            'default_environment' => 'development',
            'production' => [
                'adapter' => 'mysql',
                'host' => 'localhost',
                'name' => 'production_db',
                'user' => 'root',
                'pass' => '',
                'port' => '3306',
                'charset' => 'utf8mb4',
            ],
            'development' => [
                'adapter' => 'mysql',
                'host' => getenv('DB_HOST') ?: 'localhost',
                'name' => getenv('DB_NAME') ?: 'development_db',
                'user' => getenv('DB_USER') ?: 'app_user',
                'pass' => getenv('DB_PASSWORD') ?: 'app_password',
                'port' => getenv('DB_PORT') ?: '3306',
                'charset' => 'utf8mb4',
            ],
            'testing' => [
                'adapter' => 'sqlite',
                'name' => __DIR__ . '/db/testing',
                'suffix' => '.sqlite3',
            ]
        ],
        'version_order' => 'creation'
    ];
