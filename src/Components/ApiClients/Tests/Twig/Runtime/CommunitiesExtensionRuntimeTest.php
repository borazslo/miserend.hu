<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients\Tests\Twig\Runtime;

use App\Components\ApiClients\CommunitiesApiClient;
use App\Components\ApiClients\Twig\Runtime\CommunitiesExtensionRuntime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class CommunitiesExtensionRuntimeTest extends TestCase
{
    public function testError()
    {
        $cache = new NullAdapter();
        $httpClient = new MockHttpClient([
            new MockResponse([new TransportException('Error at transport level')]),
        ]);
        $runtime = new CommunitiesExtensionRuntime(new CommunitiesApiClient($cache, $httpClient));

        $this->assertNull($runtime->fetchCommunities(408));
    }
}
