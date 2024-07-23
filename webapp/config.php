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
	'openstreetmap' => [
		'user:pwd' => 'user:password',
		'apiurl' => 'https://api.openstreetmap.org/'	
    ],	
    'token' => [
        'web' => "2 weeks",
		'API' => "15 minutes"
    ],
	'smtp' => [
		'Host' => 'mailcatcher',
		'Port' => 1025,
		'SMTPAuth' => false,
		'SMTPSecure' => false
	],		
    'mail' => [
        'sender' => ['info@miserend.hu','miserend.hu'],
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
	'openstreetmap' => [
		'user:pwd' => 'devuser:devpassword',
		'apiurl' => 'https://master.apis.dev.openstreetmap.org/'	
    ],	
	
	
	
    'error_reporting' => E_ERROR | E_WARNING | E_PARSE
];

$environment['development'] = [
    'debug' => 1,
    'mail' => [
        'debug' => 0
    ],
	'openstreetmap' => false,
    'error_reporting' => E_ALL
];

$environment['production'] = [];