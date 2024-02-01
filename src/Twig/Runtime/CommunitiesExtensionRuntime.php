<?php

namespace App\Twig\Runtime;

use App\Components\ApiClients\CommunitiesApiClient;
use App\Components\ApiClients\Response\CommunitiesResponse;
use App\Entity\Church;
use Twig\Extension\RuntimeExtensionInterface;

class CommunitiesExtensionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly CommunitiesApiClient $client,
    )
    {
    }

    public function fetchCommunities(Church|int $church): ?CommunitiesResponse
    {
        if (is_integer($church)) {
            return $this->client->getCommunityInfoWithChurchId($church);
        }

        return $this->client->getCommunityInfoWithChurch($church);
    }
}
