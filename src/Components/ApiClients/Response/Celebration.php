<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients\Response;

/**
 * @template TCelebration of array{
 *      "@Id": int,
 *      Id: string,
 *      StringTitle: mixed,
 *      LiturgicalYearLetter: string,
 *      LiturgicalWeek: string,
 *      LiturgicalWeekOfPsalter: string,
 *      LiturgicalSeason: array{
 *          "@Id": int,
 *          "#": string,
 *      },
 *      "LiturgicalCelebrationType": array{
 *          "@Id": int,
 *          "#": string,
 *      },
 *      "LiturgicalCelebrationTypeLocal": string,
 *      "LiturgicalCelebrationLevel": string,
 *      "LiturgicalCelebrationRequired": "string",
 *      "LiturgicalCelebrationName": array{
 *          "@Id": int,
 *          "#": string,
 *      },
 *      "LiturgicalReadingsId": string,
 *  }
 */
class Celebration
{
    private string $yearLetter;

    private int $week;

    private int $weekOfPsalter;

    private int $seasonId;

    private int $type;

    private string $typeString;

    private ?string $typeLocal = null;

    private int $level;

    private bool $required;

    private ?string $name = null;

    private int $colorId;

    private string $readingsId;

    /**
     * @param TCelebration $celebration
     */
    public static function initWithArray(array $celebration): self
    {
        $instance = new self();

        $instance->yearLetter = $celebration['LiturgicalYearLetter'];
        $instance->week = filter_var($celebration['LiturgicalWeek'], \FILTER_VALIDATE_INT);
        $instance->weekOfPsalter = filter_var($celebration['LiturgicalWeekOfPsalter'], \FILTER_VALIDATE_INT);
        $instance->seasonId = (int) $celebration['LiturgicalSeason']['@Id'];
        $instance->type = (int) $celebration['LiturgicalCelebrationType']['@Id'];
        $instance->typeString = $celebration['LiturgicalCelebrationType']['#'];
        $instance->typeLocal = !empty($celebration['LiturgicalCelebrationTypeLocal']) ? $celebration['LiturgicalCelebrationTypeLocal'] : null;
        $instance->level = filter_var($celebration['LiturgicalCelebrationLevel'], \FILTER_VALIDATE_INT);
        $instance->required = filter_var($celebration['LiturgicalCelebrationRequired'], \FILTER_VALIDATE_BOOLEAN);
        $instance->name = !empty($celebration['LiturgicalCelebrationName']) ? $celebration['LiturgicalCelebrationName'] : null;
        $instance->colorId = $celebration['LiturgicalCelebrationColor']['@Id'];
        $instance->readingsId = $celebration['LiturgicalReadingsId'];

        return $instance;
    }

    public function getYearLetter(): string
    {
        return $this->yearLetter;
    }

    public function getWeek(): int
    {
        return $this->week;
    }

    public function getWeekOfPsalter(): int
    {
        return $this->weekOfPsalter;
    }

    public function getSeasonId(): int
    {
        return $this->seasonId;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getTypeString(): string
    {
        return $this->typeString;
    }

    public function getTypeLocal(): ?string
    {
        return $this->typeLocal;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getColorId(): int
    {
        return $this->colorId;
    }

    public function getReadingsId(): string
    {
        return $this->readingsId;
    }
}
