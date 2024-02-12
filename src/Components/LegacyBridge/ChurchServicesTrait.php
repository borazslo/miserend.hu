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

trait ChurchServicesTrait
{
    #[SubscribedService(key: Html\Church\Church::class)]
    private function churchChurchView(): Html\Church\Church
    {
        return $this->container->get(Html\Church\Church::class);
    }

    #[SubscribedService(key: Html\Church\Catalogue::class)]
    private function churchAdminList(): Html\Church\Catalogue
    {
        return $this->container->get(Html\Church\Catalogue::class);
    }
}
