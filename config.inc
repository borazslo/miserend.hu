<?php

$config = array();

$config['connection'] = array(
        'host' => 'localhost',
        'user' => '***',
        'password' => '***',
        'database' => 'vpa_portal',
        'prefix' => '', /* Még nem működik */
    );

$config['mail'] = array(
        'sender' => 'miserend.hu <info@miserend.hu>',
        'debug' => 0, /* 0,1,2,3 */
        'debugger' => 'eleklaszlosj@gmail.com'
    );

$config['path']['domain'] = '';

$config['debug'] = 1;

?>
