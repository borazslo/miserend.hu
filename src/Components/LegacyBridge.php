<?php

namespace App\Components;

use App\Legacy\Html;
use Illuminate\Database\Capsule\Manager;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

#[Autoconfigure(public: true)]
class LegacyBridge implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    public function __invoke(Request $request): Response
    {
        // el kell inditsuk az adatbazist a singleton hivasok miatt
        $manager = $this->databaseManager();

        $class = ltrim($request->attributes->get('_legacy_class'), '\\');
        $method = $request->attributes->get('_legacy_method');
        $object = $this->container->get($class);
        return $object->{$method}($request);
    }

    #[SubscribedService(key: Html\Ajax\Chat::class)]
    private function ajaxChat(): Html\Ajax\Chat
    {
        return $this->container->get(Html\Ajax\Chat::class);
    }

    #[SubscribedService(key: Manager::class)]
    private function databaseManager(): Manager
    {
        return $this->container->get(Manager::class);
    }
}
