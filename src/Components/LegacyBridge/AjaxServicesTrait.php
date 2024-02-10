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

trait AjaxServicesTrait
{
    #[SubscribedService(key: Html\Ajax\Chat::class)]
    private function ajaxChatView(): Html\Ajax\Chat
    {
        return $this->container->get(Html\Ajax\Chat::class);
    }

    #[SubscribedService(key: Html\Ajax\BoundaryGeoJson::class)]
    private function ajaxBoundaryGeoJsonView(): Html\Ajax\BoundaryGeoJson
    {
        return $this->container->get(Html\Ajax\BoundaryGeoJson::class);
    }
}
