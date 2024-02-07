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

trait UserServicesTrait
{
    #[SubscribedService(key: Html\User\Catalogue::class)]
    private function userCatalogue(): Html\User\Catalogue
    {
        return $this->container->get(Html\User\Catalogue::class);
    }
}
