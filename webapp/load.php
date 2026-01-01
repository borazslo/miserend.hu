<?php

define('PATH', dirname(__FILE__) . "/");

$vars = array();

if (!@include __DIR__ . '/vendor/autoload.php') {
    die('You must set up the project dependencies, run the following commands:
        wget http://getcomposer.org/composer.phar
        php composer.phar install');
}
date_default_timezone_set('Europe/Budapest');

$config = array();
$db = false;

include_once('functions.php');

$env = env('MISEREND_WEBAPP_ENVIRONMENT', 'staging'); /* testing, staging, production, vagrant */
configurationSetEnvironment($env);

error_reporting($config['error_reporting'] ? $config['error_reporting'] : 0);
define('DOMAIN', $config['path']['domain']);

Translator::init('hu'); // vagy autodetect
// Short alias for Translator::translate(). Use as t('key') in PHP or templates when available.
function t($text, $default = null) {
    return Translator::translate($text, $default);
}

//Felhasználó
if (isset($_REQUEST['login'])) {
    try {
        \User::login($_REQUEST['login'], $_REQUEST['passw']);
    } catch (\Exception $ex) {        
        addMessage('Hibás név és/vagy jelszó!<br/><br/>Ha elfelejtetted a jelszavadat, <a href="/user/lostpassword">kérj ITT új jelszót</a>.', 'danger');
    }
}
if (isset($_REQUEST['logout'])) {
    \User::logout();
}
$user = \User::load();

include_once('twig_extras.php');
$loader = new \Twig\Loader\FilesystemLoader(PATH . 'templates');
$twig = new \Twig\Environment($loader);
$twig->addFilter(new \Twig\TwigFilter('miserend_date', 'twig_hungarian_date_format'));
$twig->addFilter(new \Twig\TwigFilter('trans', 'twig_translate'));
// DANGER: a twig declarálva van / meg van hívva a Class/Html/Html.php -ban is. Így ott is módosítani kellhet a filterket

//
//  Useful CONSTANTS
//
// ATTRIBUTES, LANGUAGES, ROLES 
//
use Illuminate\Database\Capsule\Manager as DB;

$_egyhazmegyek = collect(DB::table('egyhazmegye')->get())->keyBy('id')->sortBy('sorrend');
$_espereskeruletek = collect(DB::table('espereskerulet')->get())->keyBy('id');
$_orszagok = collect(DB::table('orszagok')->get())->keyBy('id');
$_megyek = collect(DB::table('megye')->select('*','megyenev as nev')->get())->keyBy('id');
$_varosok = collect(DB::table('varosok')->get())->keyBy('id');


$_honapok = [
	1 => ['jan','január'],
	2 => ['feb','február'],
	3 => ['márc','március'],
	4 => ['ápr','április'],
	5 => ['máj','május'],
	6 => ['jún','június'],
	7 => ['júl','július'],
	8 => ['aug','augusztus'],
	9 => ['szept','szeptember'],
	10 => ['okt','október'],
	11 => ['nov','november'],
	12 => ['dec','december'],
];

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

// Gyűjtse össze a CalMass modellekben előforduló, egyedi "lang" mezőértékeket
$_calmass_langs = collect(\Eloquent\CalMass::select('lang')->distinct()->pluck('lang'))
    ->filter(function($v){ return $v !== null && $v !== ''; })
    ->map(function($v){ return trim($v); })
    ->unique()
    ->sort()
    ->values()
    ->all();
asort($_calmass_langs);
$_calmass_langs = array_values($_calmass_langs);
$huIndex = array_search('hu', $_calmass_langs, true);
if ($huIndex !== false) {
    array_splice($_calmass_langs, $huIndex, 1);
    array_unshift($_calmass_langs, 'hu');
}

foreach($_calmass_langs as $k => $langAbbrev) {
    $nyelvek[$langAbbrev] = [
        'abbrev' => $langAbbrev,
        'name' => t('LANGUAGES.' . $langAbbrev),
        'file' => 'zaszloikon/' . $langAbbrev . '.gif',
        'description' => t('LANGUAGES.' . $langAbbrev) . ' nyelven'
    ];
}
define("LANGUAGES", serialize($nyelvek));

$roles = ['miserend', 'user'];
define("ROLES", serialize($roles));
?>