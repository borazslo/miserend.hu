<?php
session_start();

include_once('config.inc');
include_once('functions.php');
include_once('classes.php');
include_once("design.php");


if($config['debug'] > 0)  error_reporting(E_ERROR | E_WARNING | E_PARSE);
else error_reporting(0);

$db_name = $config['connection']['database'];
dbconnect();

//URL ellenőrzése, hátha másik honlapra kell átirányítani
include_once('ell.php');
$urlT=url_ell();

if(isset($_REQUEST['login']) OR isset($_REQUEST['kilep'])) {
    unset($_SESSION['auth']);
    unset($_COOKIE['auth']);
    setcookie('auth', null, time());
}

if(isset($_REQUEST['login'])) {
    if(!login($_REQUEST['login'],$_REQUEST['passw'])) {
        $loginhiba = 'Hibás név és/vagy jelszó!';

    }
}

$user = getuser();
/*
//Letiltott IP-ről van-e szó
$tiltott_IP_T=ip_ell($fooldal_id);
//$tiltott_IP és belep_tiltott_IP true vagy false
/**/


if(!isset($_SESSION['isAndroidOS'])) { 
    //MobileDetect
    require_once 'vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php';        
    $detect = new Mobile_Detect;
    //$isMobile = $detect->isMobile();
    //$isTablet = $detect->isTablet();
    if($detect->isAndroidOS() == 1 AND $_SESSION['isAndroidOS'] != 'shown') {
        $vars['title'] = 'Miserend androidra is!';
        echo $twig->render('android_advertisment.html',$vars);
        $_SESSION['isAndroidOS'] = 'shown';
        exit;
    } elseif ($detect->isAndroidOS() == 1) {
        $_SESSION['isAndroidOS'] = 'shown';    
    } else {
        $_SESSION['isAndroidOS'] = false;
    }    
}

//Twig       
require_once 'vendor/twig/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader); // cache?        


if (!is_dir('fajlok/igenaptar')) {
    die('Nincsen faljok/igenaptar könyvtár. Upsz.');
}



?>