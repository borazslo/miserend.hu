<?php

include_once('load.php');

ini_set('memory_limit', '256M');

try {
    $action = \Request::SimpletextRequired('q');
} catch (Exception $e) {
    dieJsonException($e->getMessage());
}

try {
    switch ($action) {
        case 'login':
            $api = new Api\Login();
            break;
        case 'user':
            $api = new Api\User();
            break;
        case 'favorites':
            $api = new Api\Favorites();
            break;

        case 'report':
            $api = Api\ReportFactory::create();
            break;

        case 'updated':
            $api = new Api\Updated();
            break;

        case 'table':
            $api = new Api\Table();
            break;
        case 'sqlite':
            $api = new Api\Sqlite();
            break;

        default:
            throw new Exception("API action '$action' is not supported.");
    }

    $api->run();
    $api->printOutput();
    
} catch (Exception $e) {
    $api->printException($e);
}
?>