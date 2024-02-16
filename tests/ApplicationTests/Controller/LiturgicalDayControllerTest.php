<?php

namespace App\Tests\ApplicationTests\Controller;

use App\Components\ApiClients\BreviarKBSClient;
use App\Controller\LiturgicalDayController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class LiturgicalDayControllerTest extends WebTestCase
{
    private $controller;
    private $cache;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->controller = static::$kernel->getContainer()->get(LiturgicalDayController::class);
        $this->cache = new NullAdapter();
    }

    public function testRegistrationPageLoad(): void
    {
        $callback = function ($method, $url, $options): MockResponse {
            $this->assertSame('GET', $method);
            $this->assertSame('https://breviar.kbs.sk/cgi-bin/l.cgi?qt=pxml&r=2024&m=02&d=14&j=hu', $url);

            return new MockResponse(file_get_contents(__DIR__.'/../Fixtures/breviar_fq_cinerum_response.xml'), [
                'http_code' => 200,
                'headers' => [
                    'content-type' => 'text/xml',
                ],
            ]);
        };
        $httpClient = new MockHttpClient($callback);

        $breviarClient = new BreviarKBSClient($this->cache, $httpClient);

        $response = $this->controller->__invoke($breviarClient, new \DateTime('2024-02-14'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringEqualsFile(__DIR__.'/../Fixtures/breviar_fq_cinerum_html.txt', $response->getContent());
    }

    public function testSundayContent()
    {
        $httpClient = new MockHttpClient([]);
        $breviarClient = new BreviarKBSClient($this->cache, $httpClient);
        $response = $this->controller->__invoke($breviarClient, new \DateTime('2024-02-18'));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    public function testTransportError(): void
    {
        $httpClient = new MockHttpClient(function ($method, $url, $options): MockResponse {
            $this->assertSame('GET', $method);

            return new MockResponse([new TransportException('Error at transport level')]);
        });
        $breviarClient = new BreviarKBSClient($this->cache, $httpClient);
        $response = $this->controller->__invoke($breviarClient);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }

    public function testStatusCode500()
    {
        $httpClient = new MockHttpClient(function ($method, $url, $options): MockResponse {
            $this->assertSame('GET', $method);

            return new MockResponse('', [
                'http_code' => 500,
            ]);
        });
        $breviarClient = new BreviarKBSClient($this->cache, $httpClient);
        $response = $this->controller->__invoke($breviarClient);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('', $response->getContent());
    }
}
