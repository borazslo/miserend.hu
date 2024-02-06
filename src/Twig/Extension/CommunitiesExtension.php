<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\CommunitiesExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
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
