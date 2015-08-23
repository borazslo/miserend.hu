<?php
if (!is_dir('fajlok')) {
    exec('ln -s ../fajlok fajlok',$em);
    if (!is_dir('fajlok')) {
        exec('rm fajlok');
        mkdir('fajlok'); 
        mkdir('fajlok/templomok'); 
    }
}
if (!is_dir('kepek')) {
    exec('ln -s ../kepek kepek',$em);
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