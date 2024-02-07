<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Runtime;

use App\Components\ApiClients\CommunitiesApiClient;
use App\Components\ApiClients\Response\CommunitiesResponse;
use App\Entity\Church;
use Twig\Extension\RuntimeExtensionInterface;

class CommunitiesExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly CommunitiesApiClient $client,
    ) {
    }

    public function fetchCommunities(Church|int $church): ?CommunitiesResponse
    {
        if (\is_int($church)) {
            return $this->client->getCommunityInfoWithChurchId($church);
        }

        return $this->client->getCommunityInfoWithChurch($church);
    }
}
