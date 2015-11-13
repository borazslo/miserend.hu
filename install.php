<?php
require_once('functions.php');
require_once('config.php');

exec('mysql -h "'.$config['connection']['host'].'" -u "'.$config['connection']['user'].'" --password="'.$config['connection']['password'].'" "'.$config['connection']['database'].'" < mysql_sample.sql');

if (!is_dir('fajlok')) {
    //exec('ln -s ../fajlok fajlok',$em);
    if (!is_dir('fajlok')) {
        exec('rm fajlok');
        mkdir('fajlok'); 
        mkdir('fajlok/templomok'); 
    }
}
if (!is_dir('kepek')) {
    //exec('ln -s ../kepek kepek',$em);
    if (!is_dir('kepek')) {
        mkdir('kepek'); 
        mkdir('kepek/templomok'); 
        mkdir('fajlok/staticmaps'); 
    }
}

if (!is_dir('fajlok/igenaptar')) {
    mkdir('fajlok/igenaptar'); 
}



?>