<?php

$config = array();
$db = false;

$config['env'] = env('MISEREND_WEBAPP_ENVIRONMENT', 'staging'); /* testing, staging, production */

$config['connection'] = array(
    'host' => env('MYSQL_MISEREND_HOST', 'localhost'),
    'user' => env('MYSQL_MISEREND_USER', 'miserend'),
    'password' => env('MYSQL_MISEREND_PASSWORD', '***'),
    'database' => env('MYSQL_MISEREND_DATABASE', 'miserend'),
    'prefix' => '', /* Még nem működik */
);

$config['path']['domain'] = 'http://miserend.hu/';

$config['mapquest'] = array(
    'appkey' => '***',
    'useitforsearch' => false
);

$config['token']['timeout'] = "15 minutes";

$config['mail'] = array(
    'sender' => 'miserend.hu <info@miserend.hu>',
    'debug' => 0, /* 0,1,2,3,5 */
    'debugger' => 'eleklaszlosj@gmail.com'
);
$config['debug'] = 0;

date_default_timezone_set('Europe/Budapest');


switch ($config['env']) {
    case 'testing':
        $config['debug'] = 1;
        $config['mail']['debug'] = 3;
        $config['path']['domain'] = 'http://localhost/miserend.hu/';
        $config['connection']['database'] = 'miserend_testing';
        break;

    case 'staging':
        $config['debug'] = 1;
        $config['mail']['debug'] = 2;
        $config['path']['domain'] = 'http://staging.miserend.hu/';
        break;
}
?>
