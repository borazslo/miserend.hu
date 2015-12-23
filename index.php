<?php

include("load.php");

try {
    $action = \Request::Text('q');
    $path = new Path($action);

    $class = $path->className;
    if (method_exists($path->className, 'factory')) {        
        $html = $class::factory($path->arguments);
    } else {
        $html = new $class($path->arguments);
    }
} catch (\Exception $e) {
    if ($html) {
        addMessage($e->getMessage(), 'danger');
    } else {
        echo $e->getMessage();
    }
}
if ($html) {
    $html->render();
    echo $html->html;
}
?>
