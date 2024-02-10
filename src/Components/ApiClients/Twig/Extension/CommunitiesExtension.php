<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients\Twig\Extension;

use App\Components\ApiClients\Twig\Runtime\CommunitiesExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CommunitiesExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('fetch_communities', [CommunitiesExtensionRuntime::class, 'fetchCommunities']),
        ];
    }
}
