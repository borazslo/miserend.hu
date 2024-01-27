<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

use App\Kernel;
use App\Twig\Extensions\WebpackCompatibilityExtension;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class Application
{
    private $routes;
    private array $config;

    public function loadConfig(string $env): void
    {
        $environment = include PROJECT_ROOT.'/config/config.php';
        if (!array_key_exists($env, $environment)) {
            $env = 'default';
        }
        $config = $environment['default'];
        $config['env'] = $env;
        if ('default' != $env) {
            overrideArray($config, $environment[$env]);
        }
        putenv('MISEREND_WEBAPP_ENVIRONMENT='.$env);

        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

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

    public function forwardToSymfony(Request $request): Response
    {
        return $this->getKernel()->handle($request);
    }

    private function registerTwig(ContainerBuilder $builder): void
    {
        $builder->register(FilesystemLoader::class, FilesystemLoader::class)
            ->setArguments([
                [PROJECT_ROOT.\DIRECTORY_SEPARATOR.'templates'],
            ]);

        $builder->register(WebpackCompatibilityExtension::class, WebpackCompatibilityExtension::class);

        $builder->setAlias(LoaderInterface::class, FilesystemLoader::class);
        $builder
            ->register(Environment::class, Environment::class)
            ->addMethodCall('addExtension', [
                new Reference(WebpackCompatibilityExtension::class),
            ])
            ->setAutowired(true)
            ->setPublic(true)
        ;
    }

    private function registerSecurity(ContainerBuilder $builder): void
    {
        $builder->register(Security::class, Security::class)
            ->setPublic(true);
    }

    private function registerConfiguration(ContainerBuilder $builder): void
    {
        $builder->register(ConfigProvider::class, ConfigProvider::class)
            ->setArguments([
                $this->config,
            ])
            ->setPublic(true);

        $builder->register(ConstantsProvider::class, ConstantsProvider::class)
            ->setAutowired(true)
            ->setLazy(true)
            ->setPublic(true);
    }

    private function registerDatabase(ContainerBuilder $builder): void
    {
        $builder->register(DB::class, DB::class)
            ->addMethodCall('addConnection', [
                [
                    'driver' => 'mysql',
                    'host' => $this->config['connection']['host'],
                    'database' => $this->config['connection']['database'],
                    'username' => $this->config['connection']['user'],
                    'password' => $this->config['connection']['password'],
                    'charset' => 'utf8',
                    'collation' => 'utf8_general_ci',
                    'prefix' => '',
                ],
                'default',
            ])
            ->addMethodCall('bootEloquent')
            ->setPublic(true);

        $builder->register(UserRepository::class, UserRepository::class)
            ->setAutowired(true)
            ->setLazy(true)
            ->setPublic(true);

        $builder->register(MessageRepository::class, MessageRepository::class)
            ->setAutowired(true)
            ->setLazy(true)
            ->setPublic(true);
    }

    public function buildContainer(array $subscribedServices): ContainerInterface
    {
        $containerBuilder = new ContainerBuilder();
        $this->registerTwig($containerBuilder);
        $this->registerSecurity($containerBuilder);
        $this->registerConfiguration($containerBuilder);
        $this->registerDatabase($containerBuilder);
        $containerBuilder->compile();

        return $containerBuilder;
    }
}
