<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('PROJECT_ROOT', realpath(__DIR__.'/../../'));
const PATH = PROJECT_ROOT.'/';

if (!@include PROJECT_ROOT.'/vendor/autoload.php') {
    exit('You must set up the project dependencies, run the following commands:
        wget http://getcomposer.org/composer.phar
        php composer.phar install');
}

use App\User;
use Illuminate\Database\Capsule\Manager as DB;

date_default_timezone_set('Europe/Budapest');

$vars = [];
$config = [];
$db = false;

include_once 'functions.php';

$env = env('MISEREND_WEBAPP_ENVIRONMENT', 'staging'); /* testing, staging, production, vagrant */
configurationSetEnvironment($env);

error_reporting($config['error_reporting'] ?: 0);
define('DOMAIN', $config['path']['domain']);

// Felhasználó
if (isset($_REQUEST['login'])) {
    try {
        User::login($_REQUEST['login'], $_REQUEST['passw']);
    } catch (Exception $ex) {
        addMessage('Hibás név és/vagy jelszó!<br/><br/>Ha elfelejtetted a jelszavadat, <a href="/user/lostpassword">kérj ITT új jelszót</a>.', 'danger');
    }
}
if (isset($_REQUEST['logout'])) {
    User::logout();
}
$user = User::load();

$loader = new Twig\Loader\FilesystemLoader(PATH.'templates');
$twig = new Twig\Environment($loader);

//
//  Useful CONSTANTS
//
// ATTRIBUTES, LANGUAGES, PERIODS, ROLES
//

$_egyhazmegyek = collect(DB::table('egyhazmegye')->get())->keyBy('id')->sortBy('sorrend');
$_espereskeruletek = collect(DB::table('espereskerulet')->get())->keyBy('id');
$_orszagok = collect(DB::table('orszagok')->get())->keyBy('id');
$_megyek = collect(DB::table('megye')->select('*', 'megyenev as nev')->get())->keyBy('id');
$_varosok = collect(DB::table('varosok')->get())->keyBy('id');

$_honapok = [
    1 => ['jan', 'január'],
    2 => ['feb', 'február'],
    3 => ['márc', 'március'],
    4 => ['ápr', 'április'],
    5 => ['máj', 'május'],
    6 => ['jún', 'június'],
    7 => ['júl', 'július'],
    8 => ['aug', 'augusztus'],
    9 => ['szept', 'szeptember'],
    10 => ['okt', 'október'],
    11 => ['nov', 'november'],
    12 => ['dec', 'december'],
];

$milyen = [
    'csal' => [
        'abbrev' => 'csal',
        'name' => 'családos/mocorgós',
        'file' => 'lany.png',
        'group' => 'age',
    ],
    'd' => [
        'abbrev' => 'd',
        'name' => 'diák',
        'file' => 'diak.gif',
        'group' => 'age',
    ],
    'ifi' => [
        'abbrev' => 'ifi',
        'name' => 'ifjúsági/egyetemista',
        'file' => 'fiu.png',
        'group' => 'age',
    ],
    'g' => [
        'abbrev' => 'g',
        'name' => 'gitáros',
        'file' => 'gitar.gif',
        'group' => 'music',
    ],
    'cs' => [
        'abbrev' => 'cs',
        'name' => 'csendes',
        'file' => 'csendes.gif',
        'group' => 'music',
    ],
    'gor' => [
        'abbrev' => 'gor',
        'name' => 'görögkatolikus liturgia',
        'file' => 'jelzes1.png',
        'group' => 'liturgy',
        'isitmass' => true,
    ],
    'rom' => [
        'abbrev' => 'rom',
        'name' => 'római katolikus szentmise',
        'file' => 'jelzes10.png',
        'group' => 'liturgy',
        'isitmass' => true,
    ],
    'regi' => [
        'abbrev' => 'regi',
        'name' => 'régi rítusú szentmise',
        'file' => 'jelzes6.png',
        'group' => 'liturgy',
        'isitmass' => true,
    ],
    'ige' => [
        'abbrev' => 'ige',
        'name' => 'igeliturgia',
        'file' => 'biblia.gif',
        'group' => 'liturgy',
    ],
    'vecs' => [
        'abbrev' => 'vecs',
        'name' => 'vecsernye',
        'file' => 'jelzes7.png',
        'group' => 'liturgy',
    ],
    'utr' => [
        'abbrev' => 'utr',
        'name' => 'utrenye',
        'file' => 'jelzes8.png',
        'group' => 'liturgy',
    ],
    'szent' => [
        'abbrev' => 'szent',
        'name' => 'szentségimádás',
        'file' => 'jelzes9.png',
        'group' => 'liturgy',
    ],
];
foreach ($milyen as $k => $v) {
    if (!isset($v['description'])) {
        $milyen[$k]['description'] = $v['name'];
    }
}
define('ATTRIBUTES', serialize($milyen));

$nyelv = [
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
    'uk' => 'ukrán',
];
foreach ($nyelv as $k => $v) {
    $nyelv[$k] = [
        'abbrev' => $k,
        'name' => $v,
        'file' => 'zaszloikon/'.$k.'.gif',
        'description' => $v.' nyelven',
    ];
}
define('LANGUAGES', serialize($nyelv));

$periods = [
    0 => [
        'abbrev' => 0,
        'name' => '',
        'description' => 'minden',
    ],
    1 => [
        'abbrev' => 1,
        'name' => '1.',
    ],
    2 => [
        'abbrev' => 2,
        'name' => '2.',
    ],
    3 => [
        'abbrev' => 3,
        'name' => '3.',
    ],
    4 => [
        'abbrev' => 4,
        'name' => '4.',
    ],
    5 => [
        'abbrev' => 5,
        'name' => '5.',
    ],
    '-1' => [
        'abbrev' => '-1',
        'name' => 'utolsó',
    ],
    'ps' => [
        'abbrev' => 'ps',
        'name' => 'páros',
    ],
    'pt' => [
        'abbrev' => 'pt',
        'name' => 'páratlan',
    ],
];
define('PERIODS', serialize($periods));

$roles = ['miserend', 'user'];
define('ROLES', serialize($roles));
