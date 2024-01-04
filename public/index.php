<?php
 //apache_setenv('MISEREND_WEBAPP_ENVIRONMENT', 'development');

namespace App;

use App\Html\Html;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

require_once '../src/load.php';

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

    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

    $app = new Application();
    $app->loadRoutes();
    try {
        $matchedRoute = $app->matchRoute($request);
    } catch (ResourceNotFoundException $notFoundException) {
        throw new \Exception('Az oldal nem található');
    }

    if (isset($matchedRoute['_class'])) {
        $className = $matchedRoute['_class'];

        if (method_exists($className, 'factory')) {
            $html = $className::factory($matchedRoute);
        } else {
            $html = new $className($matchedRoute);
        }
    }

    if (isset($matchedRoute['handler']) && $matchedRoute['handler'] === 'symfony') {
        $response = $app->forwardToSymfony($request);

        $response->send();

        exit(0);
    }

/*
    if ($path->url == 'home' AND isset($_REQUEST['templom'])) {
        $path = new Path('templom/' . $_REQUEST['templom']);
    }
  */

} catch (\Exception $e) {
    if (isset($html)) {
        addMessage($e->getMessage(), 'danger');
    } else {
		// Mi lenne, ha a hibaüzenetünket szeben írnánk ki?
		$html = new Html($matchedRoute ?? []);
		
		$html->template = 'Exception.twig';
		$html->errorMessage = $e->getMessage();
		$html->errorTrace = '';
		
        foreach ($e->getTrace() as $trace) {
            if (isset($trace['class']))
                $html->errorTrace .= $trace['class'] . "::" . $trace['function'] . "()";
            if (isset($trace['file']))
                $html->errorTrace .= $trace['file'] . ":" . $trace['line'] . " -> " . $trace['function'] . "()";
            $html->errorTrace .= "<br>";
        }
		
		
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
