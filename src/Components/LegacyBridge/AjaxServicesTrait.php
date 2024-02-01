<?php

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
