<?php

include("load.php");

try {
    $action = \Request::Text('q');
    $path = new Path($action);

    if (method_exists($path->className, 'factory')) {
        $class = $path->className;
        $html = $class::factory($path->arguments);
    } else {
        $html = new $path->className($path->arguments);
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
