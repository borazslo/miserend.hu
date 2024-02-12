<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients;

use App\Components\ApiClients\Exceptions\ContentUnavailableException;
use App\Components\ApiClients\Response\CommunitiesResponse;
use App\Entity\Church;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CommunitiesApiClient
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly HttpClientInterface $httpClient,
        private readonly int $defaultCacheLength = 604_800,
    ) {
    }

    public function getCommunityInfoWithChurch(Church $church): ?CommunitiesResponse
    {
        return $this->getCommunityInfoWithChurchId($church->getId());
    }

    public function getCommunityInfoWithChurchId(int $churchId): ?CommunitiesResponse
    {
        \assert($churchId > 0);

        try {
            $jsonString = $this->cache->get($this->getCacheKey($churchId), function (ItemInterface $item) use ($churchId) {
                $item->expiresAfter($this->defaultCacheLength);

                $response = $this->httpClient->request('GET', $this->getEndpointUrl($churchId), [
                    'timeout' => 1,
                ]);

                return $response->getContent();
            });
        } catch (ServerException|ClientException $exception) {
            $statusCode = $exception->getResponse()->getStatusCode();
            throw new ContentUnavailableException(sprintf('Content unavailable. Response status: %d', $statusCode));
        } catch (TransportException $exception) {
            throw new ContentUnavailableException(sprintf('Content unavailable. Transport error message: "%s"', $exception->getMessage()));
        }

        return CommunitiesResponse::initWithJsonString($jsonString);
    }

    private function getEndpointUrl(int $churchId): string
    {
        return sprintf('https://kozossegek.hu/api/v1/miserend/%d', $churchId);
    }

    private function getCacheKey(int $churchId): string
    {
        return sprintf('communities-%d', $churchId);
    }
}
