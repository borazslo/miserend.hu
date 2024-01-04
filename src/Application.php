<?php

namespace App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

class Application
{
    private $routes;

    public function loadRoutes(): void
    {
        $collection = require_once PROJECT_ROOT.'/config/legacy_routing.php';

        $this->routes = $collection;
    }

    public function matchRoute(Request $request): array
    {
        $context = new RequestContext();
        $context->fromRequest($request);

        $matcher = new UrlMatcher($this->routes, $context);

        return $matcher->matchRequest($request);
    }

    public function forwardToSymfony(Request $request)
    {
        // TODO env and debug from dotenv
        $kernel = new Kernel('dev', true);
        return $kernel->handle($request);
    }
}