<?php
session_start();

$vars = array();

include_once('config.inc');
include_once('functions.php');
include_once('functions_osm.php');
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

//Twig       
require_once 'vendor/twig/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();


if(isset($_REQUEST['template'])) $_SESSION['template'] = $_REQUEST['template'];
if(!isset($_SESSION['template'])) $_SESSION['template'] = 'templates'; 
$template = $_SESSION['template'];

$loader = new Twig_Loader_Filesystem($template);
$twig = new Twig_Environment($loader); // cache?        


exec('git rev-parse --verify HEAD 2> /dev/null', $output);
$hash = $output[0];
if($hash != '') $vars['version']['hash'] = $hash;

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


if (!is_dir('fajlok/igenaptar')) {
    die('Nincsen faljok/igenaptar könyvtár. Upsz.');
}

$milyen = array(
    'csal' => array(
        'abbrev' => 'csal',
        'name' => 'családos/mocorgós',
        'file' => 'lany.png',
        'group' => 'age'
        ),
    'd' => array(
        'abbrev' => 'd',
        'name' => 'diák',
        'file' => 'diak.gif',
        'group' => 'age'
        ),
    'ifi' => array(
        'abbrev' => 'ifi',
        'name' => 'ifjúsági/egyetemista',
        'file' => 'fiu.png',
        'group' => 'age'
        ),

    'g' => array(
        'abbrev' => 'g',
        'name' => 'gitáros',
        'file' => 'gitar.gif',
        'group' => 'music'
        ),
    'cs' => array(
        'abbrev' => 'cs',
        'name' => 'csendes',
        'file' => 'csendes.gif',
        'group' => 'music'
        ),
    
    'gor' => array(
        'abbrev' => 'gor',
        'name' => 'görögkatolikus liturgia',
        'file' => 'jelzes1.png',
        'group' => 'liturgy',
        'isitmass' => 'false'
        ),    
    'rom' => array(
        'abbrev' => 'rom',
        'name' => 'római katolikus szentmise',
        'file' => 'jelzes10.png',
        'group' => 'liturgy',
        'isitmass' => 'false'
        ),    
    'regi' => array(
        'abbrev' => 'regi',
        'name' => 'régi rítusú szentmise',
        'file' => 'jelzes6.png',
        'group' => 'liturgy',
        'isitmass' => 'false'
        ), 

    'ige' => array(
        'abbrev' => 'ige',
        'name' => 'igeliturgia',
        'file' => 'biblia.gif',
        'group' => 'liturgy'
        ), 
    'vecs' => array(
        'abbrev' => 'vecs',
        'name' => 'vecsernye',
        'file' => 'jelzes7.png',
        'group' => 'liturgy'
        ), 
    'utr' => array(
        'abbrev' => 'utr',
        'name' => 'utrenye',
        'file' => 'jelzes8.png',
        'group' => 'liturgy'        
        ),
    'szent' => array(
        'abbrev' => 'szent',
        'name' => 'szentségimádás',
        'file' => 'jelzes9.png',
        'group' => 'liturgy'        
        )
);
foreach($milyen as $k => $v) {
    if(!isset($v['description']))
        $milyen[$k]['description'] = $v['name'];
}
define ("ATTRIBUTES", serialize ($milyen));

$nyelv = array(
    'h' => 'magyar',
    'en' => 'angol',
    'fr' => 'francia',
    'gr' => 'görög',
    'hr' => 'horvát',
    'va' => 'latin',
    'pl' => 'lengyel',
    'de' => 'német',
    'it' => 'olasz',
    'ro' => 'román',
    'es' => 'spanyol',
    'sk' => 'szlovák',
    'si' => 'szlovén',
    'uk' => 'ukrán'
    );
foreach($nyelv as $k => $v) {
        $nyelv[$k] = array(
            'abbrev' => $k,
            'name' => $v,
            'file' => 'zaszloikon/'.$k.'.gif',
            'description' => $v." nyelven"
        );
}
define("LANGUAGES", serialize($nyelv));

$periods = array(
    0 => array(
        'abbrev'=>0,
        'name'=>'',
        'description'=>'minden'
        ),
    1 => array(
        'abbrev'=>1,
        'name'=>'1.'
        ),
    2 => array(
        'abbrev'=>2,
        'name'=>'2.'
        ),
    3 => array(
        'abbrev'=>3,
        'name'=>'3.'
        ),
    4 => array(
        'abbrev'=>4,
        'name'=>'4.'
        ),
    5 => array(
        'abbrev'=>5,
        'name'=>'5.'
        ),
    '-1' => array(
        'abbrev'=>'-1',
        'name'=>'utolsó'
        ),
    'ps' => array(
        'abbrev'=>'ps',
        'name'=>'páros'
        ),
    'pt'=>array(
        'abbrev'=>'pt',
        'name'=>'páratlan'
        )
);
define("PERIODS", serialize($periods));

?>