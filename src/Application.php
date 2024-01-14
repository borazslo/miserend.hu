<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Symfony\Component\Dotenv\Dotenv;
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

    public function getDebug(): bool
    {
        return true;
    }

    public function loadDotenv(): void
    {
        $options = [
            'project_dir' => PROJECT_ROOT,
        ];

        (new Dotenv('APP_ENV', 'APP_DEBUG'))
            ->setProdEnvs((array) ($options['prod_envs'] ?? ['prod']))
            ->usePutenv($options['use_putenv'] ?? false)
            ->bootEnv($options['project_dir'].'/'.($options['dotenv_path'] ?? '.env'), 'dev', (array) ($options['test_envs'] ?? ['test']), $options['dotenv_overload'] ?? false);
    }

    protected Kernel $kernel;

    public function getKernel(): Kernel
    {
        if (!isset($this->kernel)) {
            // TODO env and debug from dotenv
            $this->kernel = new Kernel('dev', true);
        }

        return $this->kernel;
    }

    public function forwardToSymfony(Request $request)
    {
        return $this->getKernel()->handle($request);
    }
}
