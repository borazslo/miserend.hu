<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
