<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients\Response;

use App\Components\ApiClients\Exceptions\UnexpectedTypeException;

/**
 * @phpstan-import-type CelebrationType from BreviarResponse
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
     * @param CelebrationType $celebration
     */
    public static function initWithArray(array $celebration): self
    {
        $instance = new self();

        $instance->yearLetter = $celebration['LiturgicalYearLetter'];
        $instance->week = self::filterInteger($celebration['LiturgicalWeek']);
        $instance->weekOfPsalter = self::filterInteger($celebration['LiturgicalWeekOfPsalter']);
        $instance->seasonId = (int) $celebration['LiturgicalSeason']['@Id'];
        $instance->type = (int) $celebration['LiturgicalCelebrationType']['@Id'];
        $instance->typeString = $celebration['LiturgicalCelebrationType']['#'];
        $instance->typeLocal = !empty($celebration['LiturgicalCelebrationTypeLocal']) ? $celebration['LiturgicalCelebrationTypeLocal'] : null;
        $instance->level = self::filterInteger($celebration['LiturgicalCelebrationLevel']);
        $instance->required = self::filterBoolean($celebration['LiturgicalCelebrationRequired']);
        $instance->name = \strlen($celebration['LiturgicalCelebrationName']) > 0 ? $celebration['LiturgicalCelebrationName'] : null;
        $instance->colorId = $celebration['LiturgicalCelebrationColor']['@Id'];
        $instance->readingsId = $celebration['LiturgicalReadingsId'];

        return $instance;
    }

    private static function filterInteger(string $value): int
    {
        if (($filtered = filter_var($value, \FILTER_VALIDATE_INT)) === false) {
            throw UnexpectedTypeException::unexpectedType('int', $filtered);
        }

        return $filtered;
    }

    private static function filterBoolean(string $value): bool
    {
        return filter_var($value, \FILTER_VALIDATE_BOOLEAN);
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
