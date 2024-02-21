<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components;

use App\Entity\OsmTag;

/**
 * OSM Taglist alapján segít behatárolni az accessibility lehetőségeket.
 */
class AccessibilityHelper
{
    public function __construct(
        /** @var array<string, OsmTag> $tagList */
        private readonly array $tagList,
    ) {
    }

    public const ACCESSIBILITY_VALUE_NA = 0;
    public const ACCESSIBILITY_VALUE_YES = 1;
    public const ACCESSIBILITY_VALUE_LIMITED = 2;
    public const ACCESSIBILITY_VALUE_NO = 3;

    public function getWheelchair(): int
    {
        return match (($this->tagList['wheelchair'] ?? null)?->getValue()) {
            'yes' => self::ACCESSIBILITY_VALUE_YES,
            'limited' => self::ACCESSIBILITY_VALUE_LIMITED,
            'no' => self::ACCESSIBILITY_VALUE_NO,
            default => self::ACCESSIBILITY_VALUE_NA,
        };
    }

    public function getToiletsWheelchair(): int
    {
        return match (($this->tagList['toilets:wheelchair'] ?? null)?->getValue()) {
            'yes' => self::ACCESSIBILITY_VALUE_YES,
            'no' => self::ACCESSIBILITY_VALUE_NO,
            default => self::ACCESSIBILITY_VALUE_NA,
        };
    }

    public function getHearingLoop(): int
    {
        return match (($this->tagList['hearing_loop'] ?? null)?->getValue()) {
            'yes' => self::ACCESSIBILITY_VALUE_YES,
            'limited' => self::ACCESSIBILITY_VALUE_LIMITED,
            'no' => self::ACCESSIBILITY_VALUE_NO,
            default => self::ACCESSIBILITY_VALUE_NA,
        };
    }

    public function getWheelchairDescription(): ?string
    {
        return ($this->tagList['wheelchair:description'] ?? null)?->getValue();
    }

    public function getDisabledDescription(): ?string
    {
        return ($this->tagList['disabled:description'] ?? null)?->getValue();
    }
}
