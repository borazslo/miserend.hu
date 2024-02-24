<?php

namespace App\Tests\Unit\Components;

use App\Components\AccessibilityHelper;
use App\Entity\OsmTag;
use PHPUnit\Framework\TestCase;

class AccessibilityHelperTest extends TestCase
{
    private static function initHelper(string $key, ?string $tagValue): AccessibilityHelper
    {
        $osmTag = new OsmTag();
        $osmTag->setValue($tagValue);
        $osmTags = [
            $key => $osmTag,
        ];

        return new AccessibilityHelper($osmTags);
    }

    /**
     * @dataProvider wheelchairDataProvider
     */
    public function testWheelchair(string $tagValue, int|string|null $expectedValue): void
    {
        $helper = self::initHelper('wheelchair', $tagValue);
        $this->assertSame($expectedValue, $helper->getWheelchair());
    }

    public function wheelchairDataProvider(): \Generator
    {
        yield ['yes', AccessibilityHelper::ACCESSIBILITY_VALUE_YES];
        yield ['no', AccessibilityHelper::ACCESSIBILITY_VALUE_NO];
        yield ['limited', AccessibilityHelper::ACCESSIBILITY_VALUE_LIMITED];
        yield ['YeS', AccessibilityHelper::ACCESSIBILITY_VALUE_NA];
        yield ['invalid', AccessibilityHelper::ACCESSIBILITY_VALUE_NA];
    }

    /**
     * @dataProvider toiletsWheelchairDataProvider
     */
    public function testToiletsWheelchair(string $tagValue, int|string|null $expectedValue): void
    {
        $helper = self::initHelper('toilets:wheelchair', $tagValue);
        $this->assertSame($expectedValue, $helper->getToiletsWheelchair());
    }

    public function toiletsWheelchairDataProvider(): \Generator
    {
        yield ['yes', AccessibilityHelper::ACCESSIBILITY_VALUE_YES];
        yield ['no', AccessibilityHelper::ACCESSIBILITY_VALUE_NO];
        yield ['limited', AccessibilityHelper::ACCESSIBILITY_VALUE_NA];
        yield ['YeS', AccessibilityHelper::ACCESSIBILITY_VALUE_NA];
        yield ['invalid', AccessibilityHelper::ACCESSIBILITY_VALUE_NA];
    }

    /**
     * @dataProvider hearingLoopDataProvider
     */
    public function testHearingLoop(string $tagValue, int|string|null $expectedValue): void
    {
        $helper = self::initHelper('hearing_loop', $tagValue);
        $this->assertSame($expectedValue, $helper->getHearingLoop());
    }

    public function hearingLoopDataProvider(): \Generator
    {
        yield ['yes', AccessibilityHelper::ACCESSIBILITY_VALUE_YES];
        yield ['no', AccessibilityHelper::ACCESSIBILITY_VALUE_NO];
        yield ['limited', AccessibilityHelper::ACCESSIBILITY_VALUE_LIMITED];
        yield ['YeS', AccessibilityHelper::ACCESSIBILITY_VALUE_NA];
        yield ['invalid', AccessibilityHelper::ACCESSIBILITY_VALUE_NA];
    }

    /**
     * @dataProvider wheelchairDescriptionDataProvider
     */
    public function testWheelchairDescription(?string $tagValue, int|string|null $expectedValue): void
    {
        $helper = self::initHelper('wheelchair:description', $tagValue);
        $this->assertSame($expectedValue, $helper->getWheelchairDescription());
    }

    public function wheelchairDescriptionDataProvider(): \Generator
    {
        yield ['text', 'text'];
        yield [null, null];
    }

    /**
     * @dataProvider wheelchairDescriptionDataProvider
     */
    public function testDisabledDescription(?string $tagValue, int|string|null $expectedValue): void
    {
        $helper = self::initHelper('disabled:description', $tagValue);
        $this->assertSame($expectedValue, $helper->getDisabledDescription());
    }

    public function disabledDescriptionDataProvider(): \Generator
    {
        yield ['text', 'text'];
        yield [null, null];
    }
}
