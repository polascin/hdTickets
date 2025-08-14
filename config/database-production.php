<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Production Database Configuration with Master-Slave Replication
    |--------------------------------------------------------------------------
    |
    | This configuration file sets up master-slave database replication
    | to improve performance and ensure data redundancy for the Sports
    | Event Ticket Monitoring System.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url'    => env('DATABASE_URL'),

            // Master Configuration for Writes
            'write' => [
                'host'     => env('DB_HOST', 'prod-db-master.hdtickets.internal'),
                'port'     => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'hdtickets_production'),
                'username' => env('DB_USERNAME', 'hdtickets_prod_user'),
                'password' => env('DB_PASSWORD', ''),
            ],

            // Read Replicas Configuration for Reads
            'read' => [
                [
                    'host'     => 'prod-db-slave-1.hdtickets.internal',
                    'port'     => env('DB_READ_PORT', '3306'),
                    'database' => env('DB_READ_DATABASE', 'hdtickets_production'),
                    'username' => env('DB_READ_USERNAME', 'hdtickets_prod_read_user'),
                    'password' => env('DB_READ_PASSWORD', ''),
                ],
                [
                    'host'     => 'prod-db-slave-2.hdtickets.internal',
                    'port'     => env('DB_READ_PORT', '3306'),
                    'database' => env('DB_READ_DATABASE', 'hdtickets_production'),
                    'username' => env('DB_READ_USERNAME', 'hdtickets_prod_read_user'),
                    'password' => env('DB_READ_PASSWORD', ''),
                ],
            ],

            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => '',
            'prefix_indexes' => TRUE,
            'strict'         => TRUE,
            'engine'         => 'InnoDB',
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA             => env('MYSQL_ATTR_SSL_CA'),
                PDO::ATTR_PERSISTENT               => TRUE,
                PDO::ATTR_TIMEOUT                  => 30,
                PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => TRUE,
            ]) : [],

            // Connection Pool Configuration
            'pool' => [
                'min_connections'    => 5,
                'max_connections'    => 50,
                'connection_timeout' => 5,
                'wait_timeout'       => 30,
                'heartbeat_interval' => 30,
                'max_idle_time'      => 60,
            ],
        ],

        // Dedicated connection for heavy analytics queries
        'analytics' => [
            'driver' => 'mysql',
            'read'   => [
                [
                    'host'     => 'analytics-db-replica.hdtickets.internal',
                    'port'     => env('DB_READ_PORT', '3306'),
                    'database' => env('DB_READ_DATABASE', 'hdtickets_production'),
                    'username' => env('DB_ANALYTICS_USERNAME', 'hdtickets_analytics_user'),
                    'password' => env('DB_ANALYTICS_PASSWORD', ''),
                ],
            ],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => TRUE,
            'engine'    => 'InnoDB',
            'options'   => [
                PDO::ATTR_TIMEOUT => 120, // Longer timeout for analytics queries
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ],
        ],

        // Cache database for sessions and cache storage
        'cache_db' => [
            'driver'    => 'mysql',
            'host'      => env('CACHE_DB_HOST', 'cache-db.hdtickets.internal'),
            'port'      => env('CACHE_DB_PORT', '3306'),
            'database'  => env('CACHE_DB_DATABASE', 'hdtickets_cache'),
            'username'  => env('CACHE_DB_USERNAME', 'hdtickets_cache_user'),
            'password'  => env('CACHE_DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => TRUE,
            'engine'    => 'InnoDB',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Configuration for Production
    |--------------------------------------------------------------------------
    */

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix'  => env('REDIS_PREFIX', 'hdtickets_prod:'),
        ],

        'default' => [
            'url'          => env('REDIS_URL'),
            'host'         => env('REDIS_HOST', 'prod-redis-cluster.hdtickets.internal'),
            'username'     => env('REDIS_USERNAME'),
            'password'     => env('REDIS_PASSWORD'),
            'port'         => env('REDIS_PORT', '6379'),
            'database'     => env('REDIS_DB', '0'),
            'read_timeout' => 60,
            'context'      => [
                'auth'          => env('REDIS_PASSWORD'),
                'persistent_id' => 'hdtickets_prod',
            ],
        ],

        'cache' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', 'prod-redis-cluster.hdtickets.internal'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

        'session' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', 'prod-redis-cluster.hdtickets.internal'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '2'),
        ],

        'queue' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', 'prod-redis-cluster.hdtickets.internal'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_QUEUE_DB', '3'),
        ],
    ],
];
