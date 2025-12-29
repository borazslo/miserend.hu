<?php

use Carbon\Carbon;
use Carbon\CarbonInterface;

class SimpleRRule
{
    private $start;
    private $until;
    private $count;
    private $freq;
    private $interval;
    private $byWeekday;
    private $bySetpos;
    private $byWeekNo;
    private $debugCallback;

    public function __construct(array $rrule, callable $debugCallback = null)
    {
        $this->start      = Carbon::parse($rrule['dtstart']);
        $this->until      = !empty($rrule['until']) ? Carbon::parse($rrule['until']) : null;
        $this->count      = $rrule['count'] ?? null;
        $this->freq       = strtoupper($rrule['freq'] ?? 'DAILY');
        $this->interval   = $rrule['interval'] ?? 1;
        $this->byWeekday  = $this->normalizeByWeekday($rrule['byweekday'] ?? []);
        $this->bySetpos   = $rrule['bysetpos'] ?? null;
        $this->byWeekNo   = $rrule['byweekno'] ?? [];
        $this->debugCallback = $debugCallback;
    }

    private function logDebug(string $msg, array $ctx = []): void
    {
        if ($this->debugCallback) {
            call_user_func($this->debugCallback, $msg, $ctx);
        }
    }

    private function normalizeByWeekday(array $days): array
    {
        $map = [
            'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4,
            'FR' => 5, 'SA' => 6, 'SU' => 7,
        ];
        return array_map(fn($d) => $map[strtoupper($d)] ?? $d, $days);
    }

    public function getOccurrences(): array
    {
        $occurrences = [];
        $current = clone $this->start;
        $generated = 0;

        $weekdayMap = [
            1 => CarbonInterface::MONDAY,
            2 => CarbonInterface::TUESDAY,
            3 => CarbonInterface::WEDNESDAY,
            4 => CarbonInterface::THURSDAY,
            5 => CarbonInterface::FRIDAY,
            6 => CarbonInterface::SATURDAY,
            7 => CarbonInterface::SUNDAY,
        ];

        $this->logDebug("getOccurrences indul", [
            'start'     => $this->start->toIso8601String(),
            'until'     => $this->until?->toIso8601String(),
            'freq'      => $this->freq,
            'count'     => $this->count,
            'interval'  => $this->interval,
            'byWeekday' => $this->byWeekday,
            'bySetpos'  => $this->bySetpos,
        ]);

        while (
            (!$this->count || $generated < $this->count) &&
            (!$this->until || $current->lte($this->until))
        ) {
            switch ($this->freq) {
                case 'DAILY':
                    // Ha van BYDAY, akkor csak a megadott napokon generálunk
                    $weekday = $current->dayOfWeek === 0 ? 7 : $current->dayOfWeek;
                    if (empty($this->byWeekday) || in_array($weekday, $this->byWeekday)) {
                        $occurrences[] = clone $current;
                        $generated++;
                        $this->logDebug("Daily occurrence", [
                            'date' => $current->toIso8601String(),
                            'weekday' => $weekday,
                            'generated' => $generated
                        ]);
                    }
                    $current->addDays($this->interval);
                    break;

                case 'WEEKLY':
                    // Heti frekvencia: minden héten BYDAY szerinti napokat generálunk
                    $weekStart = $current->copy()->startOfWeek(Carbon::MONDAY);

                    // ha van megadva byWeekNo, és az aktuális hét száma nincs benne, akkor ugorjuk át
                    if (!empty($this->byWeekNo) && !in_array((int)$weekStart->format('W'), $this->byWeekNo, true)) {
                        $current->addWeeks($this->interval);
                        break;
                    }

                    foreach ($this->byWeekday as $weekday) {
                        $occurrence = $weekStart->copy()->addDays($weekday - 1)
                            ->setTimeFrom($this->start);

                        if ($occurrence->gte($current->copy()->startOfWeek()) &&
                            $occurrence->lte($current->copy()->endOfWeek()) &&
                            (!$this->until || $occurrence->lte($this->until)) &&
                            (!$this->count || $generated < $this->count) &&
                            $occurrence->gte($this->start)) {
                            $occurrences[] = $occurrence;
                            $generated++;
                            $this->logDebug("Weekly occurrence", [
                                'date' => $occurrence->toIso8601String(),
                                'weekday' => $weekday,
                                'weekNo' => (int)$weekStart->format('W'),
                                'generated' => $generated
                            ]);
                        }
                    }
                    $current = $weekStart->copy()->addWeeks($this->interval);
                    break;

                case 'MONTHLY':
                    // Havi frekvencia: ha van BYDAY → hónapon belüli napokat számoljuk
                    if (!empty($this->byWeekday)) {
                        foreach ($this->byWeekday as $weekday) {
                            $monthStart = $current->copy()->startOfMonth();
                            if ($this->bySetpos) {
                                if($this->bySetpos > 0) {
                                    // Pozitív BYSETPOS esetén az adott hónap elejétől számolunk
                                    $occurrence = $monthStart->copy()->nthOfMonth($this->bySetpos, $weekdayMap[$weekday]);
                                } elseif($this->bySetpos == -1)  {
                                    // -1 BYSETPOS esetén a hónap utolsóját számoljuk
                                    $occurrence = $monthStart->copy()->lastOfMonth($weekdayMap[$weekday]);
                                } else {
                                    throw new Exception("Unsupported BYSETPOS value: {$this->bySetpos}");
                                }
                                // pySetpos 5 esetén gyarkan előfordulhat, hogy nincs ilyen nap a hónapban
                                if($occurrence)                                
                                    $occurrence->setTimeFrom($this->start);

                            } else {
                                // Ha nincs BYSETPOS → alapértelmezés: első ilyen nap
                                $occurrence = $monthStart->copy()->nthOfMonth(1, $weekdayMap[$weekday])
                                    ->setTimeFrom($this->start);
                            }

                            if ($occurrence &&
                                (!$this->until || $occurrence->lte($this->until)) &&
                                (!$this->count || $generated < $this->count) &&
                                $occurrence->gte($this->start)) {
                                $occurrences[] = $occurrence;
                                $generated++;
                                $this->logDebug("Monthly occurrence", [
                                    'date' => $occurrence->toIso8601String(),
                                    'weekday' => $weekday,
                                    'bySetpos' => $this->bySetpos,
                                    'generated' => $generated
                                ]);
                            }
                        }
                    } else {
                        // Nincs BYDAY → simán ugyanaz a nap minden hónapban
                        $occurrence = $current->copy();
                        if ((!$this->until || $occurrence->lte($this->until)) &&
                            (!$this->count || $generated < $this->count)) {
                            $occurrences[] = $occurrence;
                            $generated++;
                            $this->logDebug("Monthly occurrence (no BYDAY)", [
                                'date' => $occurrence->toIso8601String(),
                                'generated' => $generated
                            ]);
                        }
                    }
                    $current->addMonths($this->interval)->startOfMonth();
                    break;

                default:
                    throw new Exception("Unsupported frequency: {$this->freq}");
            }
        }

        $this->logDebug("getOccurrences vége", ['total' => count($occurrences)]);
        return $occurrences;
    }

}
