<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

if (is_file(__DIR__.'/../var/data.db')) {
    unlink(__DIR__.'/../var/data.db');
}

passthru(sprintf('APP_ENV=%s php "%s/../bin/console" cache:clear --no-warmup -q',
$_ENV['APP_ENV'],
__DIR__
));

$skipDatabaseCreate = (bool) ($_ENV['SKIP_DB_CREATE'] ?? false);

if ($skipDatabaseCreate === false) {
    passthru(sprintf('APP_ENV=%s php "%s/../bin/console" doctrine:database:create -q',
        $_ENV['APP_ENV'],
        __DIR__
    ));

    passthru(sprintf('APP_ENV=%s php "%s/../bin/console" doctrine:schema:create -q',
        $_ENV['APP_ENV'],
        __DIR__
    ));
}
