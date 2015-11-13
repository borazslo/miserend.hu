<?php

$config = array();
$db = false;

$config['connection'] = array(
        'host' => 'localhost',
        'user' => '***',
        'password' => '***',
        'database' => 'vpa_portal',
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
        'debug' => 0, /* 0,1,2,3 */
        'debugger' => 'eleklaszlosj@gmail.com'
    );

$config['debug'] = 0;

date_default_timezone_set('Europe/Budapest');

?>
