<?php

use App\Services\BR\BREncryption;
use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    #'default' => env('DB_CONNECTION', 'sqlite'),
    'default' => env('DB_CONNECTION', 'sqlsrv'),


    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [
        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'BRCRM'),
            'username' => env('DB_AUTH_ENCRYPTED', false) ? BREncryption::decrypt((string)env('DB_USERNAME', ''), (string)env('DB_USERNAME_KEY_ONE', 'Sandra'), (string)env('DB_USERNAME_KEY_TWO', 'Kahungo')) : env('DB_USERNAME', ''),
            'password' => env('DB_AUTH_ENCRYPTED', false) ? BREncryption::decrypt((string)env('DB_PASSWORD', ''), (string)env('DB_PASSWORD_KEY_ONE', 'Barack'), (string)env('DB_PASSWORD_KEY_TWO', 'Obama')) : env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'options' => [
                // PDO::SQLSRV_ATTR_QUERY_TIMEOUT => 60,//s
            ],
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', true),
        ],

        /*'brcbs' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL_CBS'),
            'host' => env('DB_HOST_CBS', 'localhost'),
            'port' => env('DB_PORT_CBS', '1433'),
            'database' => env('DB_DATABASE_CBS', 'BRCBS'),
            'username' => env('DB_USERNAME_CBS', 'administrator'),
            'password' => env('DB_PASSWORD_CBS', ''),
            'charset' => env('DB_CHARSET_CBS', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'options' => [
                //PDO::SQLSRV_ATTR_QUERY_TIMEOUT => 300,
            ]
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],*/


    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 't_Migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
