<?php

session_start();

$vars = array();

if (!@include __DIR__ . '/vendor/autoload.php') {
    die('You must set up the project dependencies, run the following commands:
        wget http://getcomposer.org/composer.phar
        php composer.phar install');
}

include_once('functions.php');
include_once('config.php');
//include_once('functions_osm.php');

if ($config['debug'] > 0) {
    ini_set('display_errors', 1);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

//TODO: megszűntetni?
$vars['design_url'] = $design_url = $config['path']['domain'];
define('DOMAIN', $config['path']['domain']);

//TODO: megszűntetni a $db_name-t. Bár akkor már PDO mindenhiva
$db_name = $config['connection']['database'];
dbconnect();

//Felhasználó kiléptetés
if (isset($_REQUEST['login']) OR isset($_REQUEST['kilep'])) {
    quit();
}
//Felhasználó beléptetés
if (isset($_REQUEST['login'])) {
    if (!login($_REQUEST['login'], $_REQUEST['passw'])) {
        addMessage('Hibás név és/vagy jelszó!<br/><br/>Ha elfelejtetted a jelszavadat, <a href="?q=user/lostpassword">kérj ITT új jelszót</a>.', 'danger');
    }
}

//Felhasználó betöltése
$user = getuser();
//Felhasználó aktív még mindig
if ($user->loggedin)
    $user->active();


//TODO: delete this (see: \Html\Html::loadTwig());
require_once 'vendor/twig/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('templates2');
$twig = new Twig_Environment($loader); // cache?      

//GIT version
exec('git rev-parse --verify HEAD 2> /dev/null', $output);
if ($output[0] != '')
    $vars['version']['hash'] = $output[0];


//
//  Useful CONSTANTS
//
// ATTRIBUTES, LANGUAGES, PERIODS, ROLES 
//
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
        'isitmass' => true
    ),
    'rom' => array(
        'abbrev' => 'rom',
        'name' => 'római katolikus szentmise',
        'file' => 'jelzes10.png',
        'group' => 'liturgy',
        'isitmass' => true
    ),
    'regi' => array(
        'abbrev' => 'regi',
        'name' => 'régi rítusú szentmise',
        'file' => 'jelzes6.png',
        'group' => 'liturgy',
        'isitmass' => true
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
foreach ($milyen as $k => $v) {
    if (!isset($v['description']))
        $milyen[$k]['description'] = $v['name'];
}
define("ATTRIBUTES", serialize($milyen));

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
foreach ($nyelv as $k => $v) {
    $nyelv[$k] = array(
        'abbrev' => $k,
        'name' => $v,
        'file' => 'zaszloikon/' . $k . '.gif',
        'description' => $v . " nyelven"
    );
}
define("LANGUAGES", serialize($nyelv));

$periods = array(
    0 => array(
        'abbrev' => 0,
        'name' => '',
        'description' => 'minden'
    ),
    1 => array(
        'abbrev' => 1,
        'name' => '1.'
    ),
    2 => array(
        'abbrev' => 2,
        'name' => '2.'
    ),
    3 => array(
        'abbrev' => 3,
        'name' => '3.'
    ),
    4 => array(
        'abbrev' => 4,
        'name' => '4.'
    ),
    5 => array(
        'abbrev' => 5,
        'name' => '5.'
    ),
    '-1' => array(
        'abbrev' => '-1',
        'name' => 'utolsó'
    ),
    'ps' => array(
        'abbrev' => 'ps',
        'name' => 'páros'
    ),
    'pt' => array(
        'abbrev' => 'pt',
        'name' => 'páratlan'
    )
);
define("PERIODS", serialize($periods));

$roles = ['miserend', 'user'];
define("ROLES", serialize($roles));
?>