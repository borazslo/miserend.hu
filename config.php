<?php

$environment['default'] = [
    'connection' => [
        'host' => env('MYSQL_MISEREND_HOST', 'localhost'),
        'user' => env('MYSQL_MISEREND_USER', 'miserend'),
        'password' => env('MYSQL_MISEREND_PASSWORD', '***'),
        'database' => env('MYSQL_MISEREND_DATABASE', 'miserend')
    ],
    'path' => [
        'domain' => 'http://miserend.hu'
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
    'path' => [
        'domain' => 'http://localhost/miserend.hu'
    ],
    'connection' => [
        'database' => 'miserend_testing',
        'user' => 'root',
        'password' => 'root'
    ]
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
        'database' => 'miserend_staging'
    ],
    'error_reporting' => E_ERROR | E_WARNING | E_PARSE
];

$environment['vagrant'] = [
    'debug' => 1,
    'mail' => [
        'debug' => 0
    ],
    'connection' => [
        'database' => 'miserend',
        'user' => 'root',
        'password' => 'root'
    ],
    'error_reporting' => E_ALL
];
