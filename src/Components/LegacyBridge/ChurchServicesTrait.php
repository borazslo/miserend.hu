<?php

namespace App\Components\LegacyBridge;

use App\Legacy\Html;
use Symfony\Contracts\Service\Attribute\SubscribedService;

trait ChurchServicesTrait
{
    #[SubscribedService(key: Html\Church\Church::class)]
    private function churchChurchView(): Html\Church\Church
    {
        return $this->container->get(Html\Church\Church::class);
    }

}
