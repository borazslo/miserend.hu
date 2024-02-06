<?php

namespace App\Legacy\Services;

use Illuminate\Database\Capsule\Manager as DB;

class DatabaseManagerFactory
{
    public static function factory(ConfigProvider $configProvider): DB
    {
        $config = $configProvider->getConfig();

        $manager = new DB();
        $manager->addConnection([
            'driver' => 'mysql',
            'host' => $config['connection']['host'],
            'database' => $config['connection']['database'],
            'username' => $config['connection']['user'],
            'password' => $config['connection']['password'],
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
        ], 'default');
        $manager->setAsGlobal();
        $manager->bootEloquent();
        return $manager;
    }
}
