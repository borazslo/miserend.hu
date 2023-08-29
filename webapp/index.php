<?php
 apache_setenv('MISEREND_WEBAPP_ENVIRONMENT', 'development');
include("load.php");

try {
    if (php_sapi_name() == "cli") {
        if (isset($argv)) {
            foreach ($argv as $arg) {
                $e = explode("=", $arg);
                if (count($e) == 2) {
                    $_REQUEST[$e[0]] = $e[1];
                    if ($e[0] == 'env') {
                        configurationSetEnvironment($e[1]);
                    }
                } else
                    $_REQUEST[$e[0]] = 0;
            }
        }
    }

    $action = \Request::Text('q');
    $path = new Path($action);

    if ($path->url == 'home' AND isset($_REQUEST['templom'])) {
        $path = new Path('templom/' . $_REQUEST['templom']);
    }

    $class = $path->className;
    if (method_exists($path->className, 'factory')) {
        $html = $class::factory($path->arguments);
    } else {
        $html = new $class($path->arguments);
    }
} catch (\Exception $e) {
    if (isset($html)) {
        addMessage($e->getMessage(), 'danger');
    } else {
        \Html\Html::printExceptionVerbose($e);
    }
}
if (isset($html)) {
    $html->render();
    if (trim($html->html) != '') {        
        if(isset($html->api->format) AND $html->api->format == 'json')
            header('Content-Type: application/json');
        echo $html->html;
    }
}
?>
