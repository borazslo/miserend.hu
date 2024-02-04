<?php

namespace App\Components\LegacyBridge;

use App\Legacy\Html;
use Symfony\Contracts\Service\Attribute\SubscribedService;

trait UserServicesTrait
{

    #[SubscribedService(key: Html\User\Catalogue::class)]
    private function userCatalogue(): Html\User\Catalogue
    {
        return $this->container->get(Html\User\Catalogue::class);
    }
}
