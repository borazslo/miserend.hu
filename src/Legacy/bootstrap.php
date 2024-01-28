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

use App\Legacy\Application;
use App\User;
use Illuminate\Database\Capsule\Manager as DB;

date_default_timezone_set('Europe/Budapest');

$vars = [];
$config = [];
$db = false;

include_once 'functions.php';

$app = new Application();

$env = env('MISEREND_WEBAPP_ENVIRONMENT', 'staging'); /* testing, staging, production, vagrant */
$app->loadConfig($env);

error_reporting($app->getConfig()['error_reporting'] ?: 0);
define('DOMAIN', $app->getConfig()['path']['domain']);

return $app;
