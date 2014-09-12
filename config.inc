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

function dbconnect() {
    global $hiba,$hibauzenet,$hibauzenet_prog,$db_name,$modul_url,$fuggveny_url,$design_url,$nyitomodul;

    global $config, $db;
    $db_host = $config['connection']['host'];
    $db_uname = $config['connection']['user'];
    $db_upass = $config['connection']['password'];
    $db_name = $config['connection']['database'];
    try { 
		$db = new PDO('mysql:host='.$db_host.';dbname='.$db_name, $db_uname, $db_upass,array('charset'=>'UTF-8',PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	} catch (PDOException $exception) { 
		echo '<strong>Végzetes hiba törént!</strong>';
		echo '<br/>Nem sikerült a mysql szerverhez kapcsolódni!';
		echo '<br/>Részletek: '. $exception->getMessage(); 
	   exit; 
	} 

    $modul_url='moduls';
    $nyitomodul=1;

    

    if(!@mysql_connect($db_host, $db_uname, $db_upass)) {
    
        $hiba=true;
        $hibauzenet.='Elnézést kérünk, a szolgáltatás jelenleg nem érhető el.';
        $idopont=date('Y.m.d. H:i.s');
        $hibauzenet_prog.="Adatbázisszerverhez nem lehet csatlakozni!\n".mysql_error()."\n$idopont";
    }
    
    mysql_query("SET NAMES UTF8");
    
    if(!mysql_select_db($db_name)) {
        $hiba=true;
        $hibauzenet.='Elnézést kérünk, a szolgáltatás jelenleg nem érhető el.';
        $idopont=date('Y.m.d. H:i.s');
        $hibauzenet_prog.="Az '".$db_name."' adatbázis nem létezik, vagy nincs megfelelő jogosultság elérni azt!\n".mysql_error()."\n$idopont";
    }
}

$config['mail'] = array(
        'sender' => 'miserend.hu <info@miserend.hu>',
        'debug' => 0, /* 0,1,2,3 */
        'debugger' => 'eleklaszlosj@gmail.com'
    );
$config['debug'] = 1;

?>
