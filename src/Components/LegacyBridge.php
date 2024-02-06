<?php

namespace App\Components;

use App\Components\LegacyBridge\AjaxServicesTrait;
use App\Components\LegacyBridge\BaseServicesTrait;
use App\Components\LegacyBridge\ChurchServicesTrait;
use App\Components\LegacyBridge\UserServicesTrait;
use App\Legacy\Services\ConfigProvider;
use Illuminate\Database\Capsule\Manager;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

#[Autoconfigure(public: true)]
class LegacyBridge implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;
    use BaseServicesTrait;
    use AjaxServicesTrait;
    use ChurchServicesTrait;
    use UserServicesTrait;

    public function __invoke(Request $request): Response
    {
        // el kell inditsuk az adatbazist a singleton hivasok miatt
        $manager = $this->databaseManager();

        $class = ltrim($request->attributes->get('_legacy_class'), '\\');
        $method = $request->attributes->get('_legacy_method');

        if (!$this->container->has($class)) {
            throw new ServiceNotFoundException($class);
        }

        // init legacy stuffs
        define('DOMAIN', $this->configProvider()->getConfig()['path']['domain']);

        $object = $this->container->get($class);
        return $object->{$method}($request);
    }

    #[SubscribedService(key: Manager::class)]
    private function databaseManager(): Manager
    {
        return $this->container->get(Manager::class);
    }

    #[SubscribedService(key: ConfigProvider::class)]
    private function configProvider(): ConfigProvider
    {
        return $this->container->get(ConfigProvider::class);
    }
}
