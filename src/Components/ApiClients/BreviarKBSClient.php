<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients;

use App\Components\ApiClients\Exceptions\ContentUnavailableException;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BreviarKBSClient
{
    private readonly XmlEncoder $xmlEncoder;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly HttpClientInterface $httpClient,
    ) {
        $this->xmlEncoder = new XmlEncoder();
    }

    public function fetchCalendar(): array
    {
        return $this->fetchCalendarAt(new \DateTime());
    }

    public function fetchCalendarAt(\DateTimeInterface $date): array
    {
        try {
            $rawContent = $this->cache->get('kbs-breviar-'.$date->format('Y-m-d'), function (ItemInterface $item) use ($date) {
                $item->expiresAfter(3600 * 24);

                $url = $this->getEndpointUrl().'?'.http_build_query([
                        'qt' => 'pxml',
                        'r' => $date->format('Y'),
                        'm' => $date->format('m'),
                        'd' => $date->format('d'),
                        'j' => 'hu',
                    ]);

                $response = $this->httpClient->request('GET', $url, [
                    'timeout' => 2,
                ]);

                return $response->getContent();
            });

            return $this->xmlEncoder->decode($rawContent, 'xml');
        } catch (ServerException|ClientException $exception) {
            $statusCode = $exception->getResponse()->getStatusCode();
            throw new ContentUnavailableException(sprintf('Content unavailable. Response status: %d', $statusCode));
        } catch (TransportException $exception) {
            throw new ContentUnavailableException(sprintf('Content unavailable. Transport error message: "%s"', $exception->getMessage()));
        }
    }

    private function getEndpointUrl(): string
    {
        return 'https://breviar.kbs.sk/cgi-bin/l.cgi';
    }
}
