<?php

namespace App\Components\LegacyBridge;

use App\Legacy\Html;
use Symfony\Contracts\Service\Attribute\SubscribedService;

trait BaseServicesTrait
{
    #[SubscribedService(key: Html\Map::class)]
    private function mapView(): Html\Map
    {
        return $this->container->get(Html\Map::class);
    }

    #[SubscribedService(key: Html\Home::class)]
    private function homeView(): Html\Home
    {
        return $this->container->get(Html\Home::class);
    }

}
