<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use App\Html\Html;
use App\Legacy\ContainerAwareInterface;
use App\Legacy\Response\HttpResponseInterface;
use App\Legacy\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

$app = require_once '../src/Legacy/bootstrap.php';

try {
    if (\PHP_SAPI == 'cli') {
        if (isset($argv)) {
            foreach ($argv as $arg) {
                $e = explode('=', $arg);
                if (2 == \count($e)) {
                    $_REQUEST[$e[0]] = $e[1];
                    if ('env' == $e[0]) {
                        configurationSetEnvironment($e[1]);
                    }
                } else {
                    $_REQUEST[$e[0]] = 0;
                }
            }
        }
    }

    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

    $app->loadRoutes();
    try {
        $matchedRoute = $app->matchRoute($request);
        $request->attributes->add($matchedRoute);
    } catch (ResourceNotFoundException $notFoundException) {
        throw new \Exception('Az oldal nem található');
    }

    if (isset($matchedRoute['handler']) && 'symfony' === $matchedRoute['handler']) {
        $app->loadDotenv();

        $response = $app->forwardToSymfony($request);

        if (Kernel::VERSION_ID >= 60400) {
            $response->send(false);

            if (\function_exists('fastcgi_finish_request') && !$app->getDebug()) {
                fastcgi_finish_request();
            } else {
                Response::closeOutputBuffers(0, true);
                flush();
            }
        } else {
            $response->send();
        }

        if ($app->getKernel() instanceof TerminableInterface) {
            $app->getKernel()->terminate($request, $response);
        }

        exit(0);
    }

    // todo ez csak dev es teszt kornyezetben fusson
    Debug::enable();

    if (isset($matchedRoute['_class'])) {
        $className = $matchedRoute['_class'];

        $container = $app->buildContainer($className::getSubscribedServices());
        $container->get(\Illuminate\Database\Capsule\Manager::class);

        /** @var Security $security */
        $security = $container->get(Security::class);
        $security->captureAuthentication();
        $security->captureLogout();

        $html = new $className();

        if ($html instanceof ContainerAwareInterface) {
            $html->setContainer($container);
        }
    }

    if (isset($matchedRoute['_method'])) {
        $response = $html->{$matchedRoute['_method']}($request);

        $response->send(false);

        exit(0);
    }

} catch (\Exception $e) {
    dump($e);
    exit;
    if (isset($html)) {
        addMessage($e->getMessage(), 'danger');
    } else {
        // Mi lenne, ha a hibaüzenetünket szeben írnánk ki?
        $html = new Html($matchedRoute ?? []);

        $html->template = 'Exception.twig';
        $html->errorMessage = $e->getMessage();
        $html->errorTrace = '';

        foreach ($e->getTrace() as $trace) {
            if (isset($trace['class'])) {
                $html->errorTrace .= $trace['class'].'::'.$trace['function'].'()';
            }
            if (isset($trace['file'])) {
                $html->errorTrace .= $trace['file'].':'.$trace['line'].' -> '.$trace['function'].'()';
            }
            $html->errorTrace .= '<br>';
        }
    }
}
if (isset($html)) {
    if ($html instanceof HttpResponseInterface) {
        $response = $html->getResponse();
        $response->send();
    } else {
        $html->render();
        if ('' != trim($html->html)) {
            if (isset($html->api->format) && 'json' == $html->api->format) {
                header('Content-Type: application/json');
            }
            echo $html->html;
        }
    }
}
