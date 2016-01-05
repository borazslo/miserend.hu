#!/usr/bin/env php
<?php
fwrite(STDERR, "[info] Installation begins.\n");

use Illuminate\Database\Capsule\Manager as DB;

require 'vendor/autoload.php';
include_once('functions.php');

$env = env('MISEREND_WEBAPP_ENVIRONMENT', 'staging'); /* testing, staging, production, vagrant */
fwrite(STDERR, sprintf("[debug] \$env = '%s'\n", $env));
configurationSetEnvironment('testing');

$result = DB::table('information_schema.tables')
        ->where('table_type', "=", 'BASE TABLE')
        ->where('table_schema', '=', $config['connection']['database'])
        ->count();
if ($result) {
    $output = sprintf("[error] Database '%s' is not empty.\n", $config['connection']['database']
    );
} else {
    $command = sprintf('MYSQL_PWD=%s mysql -h %s -u %s %s < %s 2>&1', escapeshellarg($config['connection']['password']), escapeshellarg($config['connection']['host']), escapeshellarg($config['connection']['user']), escapeshellarg($config['connection']['database']), 'mysql_sample.sql'
    );
    $output = shell_exec($command);
}
fwrite(STDERR, $output);
if (!is_dir('fajlok')) {
    mkdir('fajlok');
    mkdir('fajlok/templomok');
    mkdir('fajlok/tmp');
    mkdir('fajlok/igenaptar');
    mkdir('fajlok/staticmaps');
    mkdir('fajlok/sqlite');
}
if (!is_dir('kepek')) {
    mkdir('kepek');
    mkdir('kepek/templomok');
}
fwrite(STDERR, "[info] Installation ended.\n");
