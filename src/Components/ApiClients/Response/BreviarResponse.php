<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\ApiClients\Response;

/**
 * @template TCalendarDay of array{
 *      DateISO: string,
 *      DateDay: string,
 *      DateMonth: string,
 *      DateYear: string,
 *      DayOfWeek: array{
 *          "@Id": int,
 *          "#": string,
 *      },
 *      Celebration: TCelebration|array<TCelebration>,
 *      StringVolume: string,
 *  }
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
 * @template TBreviarResponse of array{
 *      "CalendarDay": TCalendarDay
 *  }
 */
class BreviarResponse
{
    private \DateTimeImmutable $day;

    /**
     * @var array<Celebration>
     */
    private array $celebrations = [];

    /**
     * @param TBreviarResponse $responseArray
     */
    public static function initWithArray(array $responseArray): self
    {
        $instance = new self();

        $instance->day = new \DateTimeImmutable($responseArray['CalendarDay']['DateISO']);

        if (isset($responseArray['CalendarDay']['Celebration']['@Id'])) {
            $celebrations = [$responseArray['CalendarDay']['Celebration']];
        } else {
            $celebrations = $responseArray['CalendarDay']['Celebration'];
        }

        /** @var array<TCelebration> $celebration */
        foreach ($celebrations as $celebration) {
            $instance->celebrations[] = Celebration::initWithArray($celebration);
        }

        return $instance;
    }

    public function getDay(): \DateTimeImmutable
    {
        return $this->day;
    }

    /**
     * @return array<Celebration>
     */
    public function getCelebrations(): array
    {
        return $this->celebrations;
    }
}
