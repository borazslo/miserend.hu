<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients\Tests;

use App\Components\ApiClients\CommunitiesApiClient;
use App\Components\ApiClients\Exceptions\ContentUnavailableException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Exception\TransportException;

class CommunitiesApiClientTest extends TestCase
{
    private NullAdapter $cacheAdapter;

    protected function setUp(): void
    {
        $this->cacheAdapter = new NullAdapter();
    }

    public function testEmptyDataResponse(): void
    {
        $callback = function ($method, $url, $options): MockResponse {
            $this->assertSame('GET', $method);
            $this->assertSame('https://kozossegek.hu/api/v1/miserend/408', $url);

            return new MockResponse(file_get_contents(__DIR__.'/Fixtures/empty_data.json'), [
                'http_code' => 200,
                'headers' => [
                    'content-type' => 'application/json',
                ],
            ]);
        };

        $httpClient = new MockHttpClient($callback);
        $client = new CommunitiesApiClient($this->cacheAdapter, $httpClient);

        $response = $client->getCommunityInfoWithChurchId(408);

        $this->assertSame('Szentlélek és Gyümölcsoltó Boldogasszony-templom', $response->getInstituteName());
        $this->assertSame('https://kozossegek.hu/templom/godollo/szentlelek-es-gyumolcsolto-boldogasszony-templom', $response->getInstituteUrl());
        $this->assertSame('Gödöllő', $response->getCity());
        $this->assertSame('Fácán sor 3.', $response->getAddress());
        $this->assertSame([], $response->getCommunities());
    }

    public function testFullData(): void
    {
        $callback = function ($method, $url, $options): MockResponse {
            $this->assertSame('GET', $method);
            $this->assertSame('https://kozossegek.hu/api/v1/miserend/2413', $url);

            return new MockResponse(file_get_contents(__DIR__.'/Fixtures/full_data.json'), [
                'http_code' => 200,
                'headers' => [
                    'content-type' => 'application/json',
                ],
            ]);
        };

        $httpClient = new MockHttpClient($callback);
        $client = new CommunitiesApiClient($this->cacheAdapter, $httpClient);

        $response = $client->getCommunityInfoWithChurchId(2413);

        $this->assertSame('Institution name', $response->getInstituteName());
        $this->assertSame('https://kozossegek.hu/templom/slug', $response->getInstituteUrl());
        $this->assertSame('city of institution', $response->getCity());
        $this->assertSame('address of institution', $response->getAddress());

        $this->assertCount(4, $response->getCommunities());
        $first = $response->getCommunities()[0];
        $this->assertSame('Community #1', $first->getName());
        $this->assertSame('tinédzser, fiatal felnőtt', $first->getAgeGroup());
        $this->assertSame('https://kozossegek.hu/kozosseg/slug-99', $first->getUrl());
        $this->assertSame('<p>description #1</p>', $first->getDescription());
        $this->assertSame([], $first->getTags()); // nem dolgozzuk fel mert nem hordoz erdemi informaciot
    }

    public function testResponse404(): void
    {
        $callback = function ($method, $url, $options): MockResponse {
            $this->assertSame('GET', $method);
            $this->assertSame('https://kozossegek.hu/api/v1/miserend/408', $url);

            return new MockResponse(file_get_contents(__DIR__.'/Fixtures/404_response.json'), [
                'http_code' => 404,
                'headers' => [
                    'content-type' => 'application/json',
                ],
            ]);
        };

        $httpClient = new MockHttpClient($callback);
        $client = new CommunitiesApiClient($this->cacheAdapter, $httpClient);

        $this->expectException(ContentUnavailableException::class);
        $this->expectExceptionMessage('Content unavailable. Response status: 404');
        $client->getCommunityInfoWithChurchId(408);
    }

    public function testResponse500(): void
    {
        $callback = function ($method, $url, $options): MockResponse {
            $this->assertSame('GET', $method);
            $this->assertSame('https://kozossegek.hu/api/v1/miserend/408', $url);

            return new MockResponse('', [
                'http_code' => 500,
            ]);
        };

        $httpClient = new MockHttpClient($callback);
        $client = new CommunitiesApiClient($this->cacheAdapter, $httpClient);

        $this->expectException(ContentUnavailableException::class);
        $this->expectExceptionMessage('Content unavailable. Response status: 500');
        $client->getCommunityInfoWithChurchId(408);
    }

    public function testTransportError(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse([new TransportException('Error at transport level')]),
        ]);
        $client = new CommunitiesApiClient($this->cacheAdapter, $httpClient);

        $this->expectException(ContentUnavailableException::class);
        $this->expectExceptionMessage('Content unavailable. Transport error message: "Error at transport level"');
        $client->getCommunityInfoWithChurchId(408);
    }
}
