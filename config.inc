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

$config['path']['domain'] = '';

$config['mapquest'] = array(
    'appkey' => '***',
    'useitforsearch' => false
);


$config['mail'] = array(
        'sender' => 'miserend.hu <info@miserend.hu>',
        'debug' => 0, /* 0,1,2,3 */
        'debugger' => 'eleklaszlosj@gmail.com'
    );

$config['debug'] = 0;

?>
