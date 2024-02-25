<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients\Tests\Response;

use App\Components\ApiClients\Response\BreviarResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class BreviarResponseTest extends TestCase
{
    private XmlEncoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new XmlEncoder();
    }

    public function testInitWithArray(): void
    {
        $decoded = $this->encoder->decode(file_get_contents(__DIR__.'/../Fixtures/breviar_qu_h1_f6_response.xml'), 'xml');
        $this->assertIsArray($decoded);

        $response = BreviarResponse::initWithArray($decoded);

        $this->assertSame('2024-02-23', $response->getDay()->format('Y-m-d'));
        $this->assertCount(2, $response->getCelebrations());

        $celebrations = $response->getCelebrations();
        $this->assertSame('B', $celebrations[0]->getYearLetter());
        $this->assertSame(1, $celebrations[0]->getWeek());
        $this->assertSame(1, $celebrations[0]->getWeekOfPsalter());
        $this->assertSame(6, $celebrations[0]->getSeasonId());
        $this->assertSame(0, $celebrations[0]->getType());
        $this->assertNull($celebrations[0]->getTypeLocal());
        $this->assertSame(9, $celebrations[0]->getLevel());
        $this->assertTrue($celebrations[0]->isRequired());
        $this->assertNull($celebrations[0]->getName());
        $this->assertSame(4, $celebrations[0]->getColorId());
        $this->assertSame('1P5', $celebrations[0]->getReadingsId());
    }

    public function testIniWithArrayWithSingleCelebration(): void
    {
        $decoded = $this->encoder->decode(file_get_contents(__DIR__.'/../Fixtures/breviar_fq_cinerum_response.xml'), 'xml');
        $this->assertIsArray($decoded);

        $response = BreviarResponse::initWithArray($decoded);

        $this->assertCount(1, $response->getCelebrations());
    }
}
