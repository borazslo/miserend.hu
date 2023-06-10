<?php

$environment['default'] = [
    'connection' => [
        'host' => env('MYSQL_MISEREND_HOST', 'mysql'),
        'user' => env('MYSQL_MISEREND_USER', 'root'),
        'password' => env('MYSQL_MISEREND_PASSWORD', 'pw'),
        'database' => env('MYSQL_MISEREND_DATABASE', 'miserend')
    ],
    'path' => [
        'domain' => 'https://miserend.hu'
    ],
    'mapquest' => [
        'appkey' => env('MAPQUEST_CONSUMERKEY', '***'),
        'useitforsearch' => false
    ],
    'token' => [
        'timeout' => "15 minutes"
    ],
    'mail' => [
        'sender' => 'miserend.hu <info@miserend.hu>',
        'debug' => 0, /* 0,1,2,3,5 */
        'debugger' => 'eleklaszlosj@gmail.com'
    ],
    'debug' => 0,
    'error_reporting' => false
];

$environment['testing'] = [
    'debug' => 1,
    'mail' => [
        'debug' => 5
    ],   
    'connection' => [
        'host' => env('MYSQL_MISEREND_HOST', 'mysql'),
        'user' => env('MYSQL_MISEREND_USER', 'root'),
        'password' => env('MYSQL_MISEREND_PASSWORD', 'pw'),
        'database' => env('MYSQL_MISEREND_DATABASE', 'miserend')
    ],
    'error_reporting' => E_ERROR | E_WARNING | E_PARSE
];

$environment['staging'] = [
    'debug' => 1,
    'mail' => [
        'debug' => 2
    ],
    'path' => [
        'domain' => 'http://staging.miserend.hu'
    ],
    'connection' => [
        'host' => env('MYSQL_MISEREND_HOST', 'mysql'),
        'user' => env('MYSQL_MISEREND_USER', 'root'),
        'password' => env('MYSQL_MISEREND_PASSWORD', 'pw'),
        'database' => env('MYSQL_MISEREND_DATABASE', 'miserend')
    ],
    'error_reporting' => E_ERROR | E_WARNING | E_PARSE
];

$environment['vagrant'] = [
    'debug' => 1,
    'mail' => [
        'debug' => 0
    ],
    'connection' => [
        'host' => env('MYSQL_MISEREND_HOST', 'mysql'),
        'user' => env('MYSQL_MISEREND_USER', 'root'),
        'password' => env('MYSQL_MISEREND_PASSWORD', 'pw'),
        'database' => env('MYSQL_MISEREND_DATABASE', 'miserend')
    ],
    'error_reporting' => E_ALL
];

$environment['production'] = [];
