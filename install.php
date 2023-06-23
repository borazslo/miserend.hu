#!/usr/bin/env php
<?php
fwrite(STDERR, "[info] Installation begins.\n");

use Illuminate\Database\Capsule\Manager as DB;

require 'vendor/autoload.php';
include_once('functions.php');

$env = env('MISEREND_WEBAPP_ENVIRONMENT', 'staging'); /* testing, staging, production, vagrant */
fwrite(STDERR, sprintf("[debug] \$env = '%s'\n", $env));
configurationSetEnvironment('testing');

//mysql_query("SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");

mysql_query("SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");


$result = DB::table('information_schema.tables')
        ->where('table_type', "=", 'BASE TABLE')
        ->where('table_schema', '=', $config['connection']['database'])
        ->count();
if ($result) {
    $output = sprintf("[error] Database '%s' is not empty.\n", $config['connection']['database']
    );
} else {
    $command = sprintf('MYSQL_PWD=%s mysql -h %s -u %s %s < %s 2>&1', escapeshellarg($config['connection']['password']), escapeshellarg($config['connection']['host']), escapeshellarg($config['connection']['user']), escapeshellarg($config['connection']['database']), 'mysql/dump.sql'
    );
    $output = shell_exec($command);
}

fwrite(STDERR, "[info] Installation ended.\n");
