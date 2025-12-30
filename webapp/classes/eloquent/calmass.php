<?php

namespace Eloquent;
use Carbon\Carbon;

class CalMass extends CalModel
{
    protected $table = 'cal_masses';

    protected $fillable = [
        'church_id',
        'period_id',
        'title',
        'types',
        'rite',
        'start_date',
        'duration',
        'rrule',
        'experiod',
        'exdate',
        'lang',
        'comment',
    ];

    protected $casts = [
        'church_id' => 'integer',
        'period_id' => 'integer',
        'title' => 'string',
        'types' => 'array',     // JSON stringből PHP tömb
        'rite' => 'string',
        'start_date' => 'string',
        'duration' => 'array',     // JSON
        'rrule' => 'array',     // JSON
        'experiod' => 'array',     // JSON
        'exdate' => 'array',     // JSON
        'lang' => 'string',
        'comment' => 'string',
    ];

    protected $primaryKey = 'id';
    protected $keyType = 'int';

    /**
     * Generálja a miseidőpontokat adott évekre,
     * és templomonként csoportosítva visszaadja őket.
     *
     * @param array $masses
     * @param array $years
     * @return array [templom_id => miseidőpontok tömbje]
     */
    static function generateMassInstancesForYears($masses, array $churchTimezones, array $years): array
    {
        $instancesByChurch = [];

        /*
        $this::logDebug("generateMassInstancesForYears indul", [
            'mass_count' => count($masses),
            'years'      => $years,
        ]);
*/
        if (empty($masses) || empty($years)) {
            //$this->logDebug("Nincs mise vagy év");
            return $instancesByChurch;
        }

        // --- 0) Ütközés elkerülés alkalmazása ---
        $masses = self::applyCollisionAvoidance($masses);
        /*
        $this->logDebug("applyCollisionAvoidance lefutott", [
            'after_count' => count($masses),
        ]);
*/


        foreach ($years as $year) {
            $globalStart = Carbon::create($year, 1, 1)->startOfDay();
            $globalEnd = Carbon::create($year, 12, 31)->endOfDay();

            foreach ($masses as $mass) {
                /*
                $this->logDebug("Mise feldolgozás indul", [
                    'mass_id' => $mass->id,
                    'title' => $mass->title,
                    'period_id' => $mass->period_id,
                ]);
*/
                if (empty($mass->period_id)) {
                    //$this->logDebug("Egyszeri mise", ['mass_id' => $mass->id]);
                } else if (empty($mass->rrule)) {
                    //$this->logDebug("Nincs RRULE", ['mass_id' => $mass->id]);
                    continue;
                }
                $timezone = $churchTimezones[$mass->church_id] ?? 'Europe/Budapest';

                // ---- duration konvertálása percekre ----
                $durationMinutes = 0;
                if (!empty($mass->duration)) {
                    if (is_string($mass->duration)) {
                        $decoded = json_decode($mass->duration, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $mass->duration = $decoded;
                        }
                    }
                    if (is_array($mass->duration)) {
                        $days = (int)($mass->duration['days'] ?? 0);
                        $hours = (int)($mass->duration['hours'] ?? 0);
                        $minutes = (int)($mass->duration['minutes'] ?? 0);
                        $durationMinutes = $days * 24 * 60 + $hours * 60 + $minutes;
                    }
                }

                // --- ha nincs period_id: egyszeri esemény ---
                if (empty($mass->period_id)) {
                    $startDate = Carbon::parse($mass->start_date ?? now())->setTimezone($timezone);
                    if ($startDate->between($globalStart, $globalEnd)) {
                        $instancesByChurch[$mass->church_id][] = [
                            'church_id' => $mass->church_id,
                            'mass_id' => $mass->id,
                            'start_date' => $startDate->copy()->setTimezone('UTC')->format('c'),
                            'start_minutes' => $startDate->copy()->setTimezone('UTC')->hour * 60 + $startDate->copy()->setTimezone('UTC')->minute,
                            'title' => $mass->title,
                            'types' => $mass->types,
                            'rite' => $mass->rite,
                            'duration_minutes' => $durationMinutes,
                            'lang' => $mass->lang,
                            'comment' => $mass->comment,
                        ];
                    }
                    continue;
                }

                if (empty($mass->rrule)) {
                    continue;
                }

                // --- RRULE feldolgozás ---
                $rrule = $mass->rrule;
                if (is_string($rrule)) {
                    $decoded = json_decode($rrule, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $rrule = $decoded;
                    }
                }
                if (!is_array($rrule) || empty($rrule)) {
                    continue;
                }

                
                // --- kizárt dátumokkal  ---
                $excludedDatesRaw = $mass->exdate ?? [];
                if (is_string($excludedDatesRaw)) {
                    $decoded = json_decode($excludedDatesRaw, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $excludedDatesRaw = $decoded;
                    }
                }
                                                                    
                // --- kizárt periódusok ---
                $excludedPeriods = $mass->experiod ?? [];
                if (is_string($excludedPeriods)) {
                    $decoded = json_decode($excludedPeriods, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $excludedPeriods = $decoded;
                    }
                }
                $excludedPeriods = is_array($excludedPeriods) ? $excludedPeriods : [];

                // --- az adott miséhez való legeneráltperiódusok betöltése ---
                $periods = CalGeneratedPeriod::where('period_id', $mass->period_id)
                    ->where('start_date', '<=', $globalEnd->toDateString())
                    ->where('end_date', '>', $globalStart->toDateString())
                    ->get();

                foreach ($periods as $generatedPeriod) {
                    $start = Carbon::parse($generatedPeriod->start_date)->startOfDay()->setTimezone($timezone);
                    $end = Carbon::parse($generatedPeriod->end_date)->subDay()->endOfDay()->setTimezone($timezone);

                    if ($start->lt($globalStart)) $start = (clone $globalStart)->setTimezone($timezone);
                    if ($end->gt($globalEnd))     $end   = (clone $globalEnd)->setTimezone($timezone);
                    if ($start->gt($end)) continue;

                    // Exdate feldolgozása: csak az adott időszakba eső dátumok legyenek exdate-ben
                    $rrule['exdate'] = [];
                    foreach($excludedDatesRaw as $exDateString) {
                        $exDate = Carbon::parse($exDateString)->setTimezone($timezone);
                        if($exDate->between($start,$end)) {
                            $rrule['exdate'][] = $exDate;
                        }
                    }
                    $rrule['exdate'] = collect(is_array($excludedDatesRaw) ? $excludedDatesRaw : [])
                    ->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();

                    // Experiod feldolgozása: csak az adott időszakba eső periódusok érdeklnek
                    // Aztán a beleeső napokat áttesszük exDate-be
                    foreach($excludedPeriods as $exPeriodString) {
                        $exGeneratedPeriods = CalGeneratedPeriod::where('period_id', $exPeriodString)
                                            ->where('start_date', '<=', $end->toDateString())
                                            ->where('end_date', '>', $start->toDateString())
                                            ->get();
                        //Nagyon nagyon furcsa lenne, ha kettő is lenne belőle, de ugye....                                            
                        foreach($exGeneratedPeriods as $exGeneratedPeriod) {
                            
                            // ExGeneratedPeriod intervallum (igazítva napokra, ugyanabban a timezone-ban mint $start/$end)
                            $exStart = Carbon::parse($exGeneratedPeriod->start_date)->startOfDay()->setTimezone($timezone);
                            $exEnd   = Carbon::parse($exGeneratedPeriod->end_date)->subDay()->endOfDay()->setTimezone($timezone);

                            // Ha nincs átfedés, kihagyjuk
                            if ($exEnd->lt($start) || $exStart->gt($end)) {
                                continue;
                            }

                            // Átfedés kezdete és vége
                            $overlapStart = $exStart->lt($start) ? $start->copy()->startOfDay() : $exStart->copy()->startOfDay();
                            $overlapEnd   = $exEnd->gt($end)   ? $end->copy()->endOfDay()   : $exEnd->copy()->endOfDay();

                            // Az átfedő napokat hozzáadjuk exdate-hez (YYYY-MM-DD formátumban)
                            for ($d = $overlapStart->copy(); $d->lte($overlapEnd); $d->addDay()) {
                                $rrule['exdate'][] = $d->toDateString();
                            }
                        }                    
                    }
                    // Duplikátumok eltávolítása                    
                    $rrule['exdate'] = array_values(array_unique($rrule['exdate']));
                    sort($rrule['exdate']);

                    $recurrences = self::generateDatesFromRrule($rrule, $start, $end);
                   /* $this->logDebug("Recurrence generálás", [
                        'mass_id' => $mass->id,
                        'count' => count($recurrences),
                    ]); */

                    foreach ($recurrences as $date) {
                        /*$this->logDebug("Generált dátum", [
                            'mass_id' => $mass->id,
                            'date' => $date->toIso8601String(),
                        ]); */                        
                       
                        $startUTC = $date->copy()->setTimezone('UTC');
                        $instancesByChurch[$mass->church_id][] = [
                            'church_id' => $mass->church_id,
                            'mass_id' => $mass->id,
                            'start_date' => $startUTC->format('c'),
                            'start_minutes' => $startUTC->hour * 60 + $startUTC->minute,
                            'title' => $mass->title,
                            'types' => $mass->types,
                            'rite' => $mass->rite,
                            'duration_minutes' => $durationMinutes,
                            'lang' => $mass->lang,
                            'comment' => $mass->comment,
                        ];
                    }
                }
            }
        }

        // Rendezés start_date szerint (növekvő)
        foreach($instancesByChurch as $churchId => &$recurrences) {            
            usort($recurrences, function($a, $b) {
                return $a['start_date'] <=> $b['start_date'];
            });
        }

        return $instancesByChurch;
    }

    /**
     
     */
    static function generateMassPeriodInstancesForYears($masses, array $churchTimezones, array $years): array
    {
        $massPeriods = [];

        /*
        $this::logDebug("generateMassInstancesForYears indul", [
            'mass_count' => count($masses),
            'years'      => $years,
        ]);
*/
        if (empty($masses) || empty($years)) {
            //$this->logDebug("Nincs mise vagy év");
            return $massPeriods;
        }

        // --- 0) Ütközés elkerülés alkalmazása ---
        $masses = self::applyCollisionAvoidance($masses);
        /*
        $this->logDebug("applyCollisionAvoidance lefutott", [
            'after_count' => count($masses),
        ]);
*/


        foreach ($years as $year) {
            $globalStart = Carbon::create($year, 1, 1)->startOfDay();
            $globalEnd = Carbon::create($year, 12, 31)->endOfDay();

            foreach ($masses as $mass) {
                /*
                $this->logDebug("Mise feldolgozás indul", [
                    'mass_id' => $mass->id,
                    'title' => $mass->title,
                    'period_id' => $mass->period_id,
                ]);
*/
                if (empty($mass->period_id)) {
                    //$this->logDebug("Egyszeri mise", ['mass_id' => $mass->id]);
                } else if (empty($mass->rrule)) {
                    //$this->logDebug("Nincs RRULE", ['mass_id' => $mass->id]);
                    continue;
                }
                $timezone = $churchTimezones[$mass->church_id] ?? 'Europe/Budapest';

                // ---- duration konvertálása percekre ----
                $durationMinutes = 0;
                if (!empty($mass->duration)) {
                    if (is_string($mass->duration)) {
                        $decoded = json_decode($mass->duration, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $mass->duration = $decoded;
                        }
                    }
                    if (is_array($mass->duration)) {
                        $days = (int)($mass->duration['days'] ?? 0);
                        $hours = (int)($mass->duration['hours'] ?? 0);
                        $minutes = (int)($mass->duration['minutes'] ?? 0);
                        $durationMinutes = $days * 24 * 60 + $hours * 60 + $minutes;
                    }
                }

                // --- ha nincs period_id: egyszeri esemény ---
                if (empty($mass->period_id)) {                   
                    continue;
                }

                if (empty($mass->rrule)) {
                    continue;
                }

                // --- RRULE feldolgozás ---
                $rrule = $mass->rrule;
                if (is_string($rrule)) {
                    $decoded = json_decode($rrule, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $rrule = $decoded;
                    }
                }
                if (!is_array($rrule) || empty($rrule)) {
                    continue;
                }

                
                // --- kizárt dátumokkal  ---
                $excludedDatesRaw = $mass->exdate ?? [];
                if (is_string($excludedDatesRaw)) {
                    $decoded = json_decode($excludedDatesRaw, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $excludedDatesRaw = $decoded;
                    }
                }
                                                                    
                // --- kizárt periódusok ---
                $excludedPeriods = $mass->experiod ?? [];
                if (is_string($excludedPeriods)) {
                    $decoded = json_decode($excludedPeriods, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $excludedPeriods = $decoded;
                    }
                }
                $excludedPeriods = is_array($excludedPeriods) ? $excludedPeriods : [];

                // --- az adott miséhez való legeneráltperiódusok betöltése ---
                $periods = CalGeneratedPeriod::where('period_id', $mass->period_id)
                    ->where('start_date', '<=', $globalEnd->toDateString())
                    ->where('end_date', '>', $globalStart->toDateString())
                    ->get();

                foreach ($periods as $generatedPeriod) {
                    $start = Carbon::parse($generatedPeriod->start_date)->startOfDay()->setTimezone($timezone);
                    $end = Carbon::parse($generatedPeriod->end_date)->subDay()->endOfDay()->setTimezone($timezone);

                    if ($start->lt($globalStart)) $start = (clone $globalStart)->setTimezone($timezone);
                    if ($end->gt($globalEnd))     $end   = (clone $globalEnd)->setTimezone($timezone);
                    if ($start->gt($end)) continue;

                    // Exdate feldolgozása: csak az adott időszakba eső dátumok legyenek exdate-ben
                    $rrule['exdate'] = [];
                    foreach($excludedDatesRaw as $exDateString) {
                        $exDate = Carbon::parse($exDateString)->setTimezone($timezone);
                        if($exDate->between($start,$end)) {
                            $rrule['exdate'][] = $exDate;
                        }
                    }
                    $rrule['exdate'] = collect(is_array($excludedDatesRaw) ? $excludedDatesRaw : [])
                    ->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();

                    // Experiod feldolgozása: csak az adott időszakba eső periódusok érdeklnek
                    // Aztán a beleeső napokat áttesszük exDate-be
                    foreach($excludedPeriods as $exPeriodString) {
                        $exGeneratedPeriods = CalGeneratedPeriod::where('period_id', $exPeriodString)
                                            ->where('start_date', '<=', $end->toDateString())
                                            ->where('end_date', '>', $start->toDateString())
                                            ->get();
                        //Nagyon nagyon furcsa lenne, ha kettő is lenne belőle, de ugye....                                            
                        foreach($exGeneratedPeriods as $exGeneratedPeriod) {
                            
                            // ExGeneratedPeriod intervallum (igazítva napokra, ugyanabban a timezone-ban mint $start/$end)
                            $exStart = Carbon::parse($exGeneratedPeriod->start_date)->startOfDay()->setTimezone($timezone);
                            $exEnd   = Carbon::parse($exGeneratedPeriod->end_date)->subDay()->endOfDay()->setTimezone($timezone);

                            // Ha nincs átfedés, kihagyjuk
                            if ($exEnd->lt($start) || $exStart->gt($end)) {
                                continue;
                            }

                            // Átfedés kezdete és vége
                            $overlapStart = $exStart->lt($start) ? $start->copy()->startOfDay() : $exStart->copy()->startOfDay();
                            $overlapEnd   = $exEnd->gt($end)   ? $end->copy()->endOfDay()   : $exEnd->copy()->endOfDay();

                            // Az átfedő napokat hozzáadjuk exdate-hez (YYYY-MM-DD formátumban)
                            for ($d = $overlapStart->copy(); $d->lte($overlapEnd); $d->addDay()) {
                                $rrule['exdate'][] = $d->toDateString();
                            }
                        }                    
                    }
                    // Duplikátumok eltávolítása                    
                    if(count($rrule['exdate']) > 0) {
                        $rrule['exdate'] = array_values(array_unique($rrule['exdate']));
                        sort($rrule['exdate']);
                    } else {
                        unset($rrule['exdate']);
                    }
                    
                    // RRULE dstart & until
                    $tz = $start->getTimezone()->getName();
                    $origDtStart = $rrule['dtstart'] instanceof \DateTimeInterface
                        ? Carbon::instance($rrule['dtstart'])->setTimezone($tz)
                        : Carbon::parse($rrule['dtstart'], $tz)->setTimezone($tz);
                    $hh = $origDtStart->hour;
                    $mm = $origDtStart->minute;
                    $ss = $origDtStart->second;
                    $alignedDtStart = $start->copy()->setTime($hh, $mm, $ss);
                    $effectiveDtStart = $alignedDtStart;
                    $rrule['dtstart'] = $effectiveDtStart->toIso8601String();
                    $rrule['until']   = $end->toIso8601String();
                    

                    $massPeriods[] = [
                        'mass_id' => $mass->id,
                        'period_id' => $mass->period_id,
                        'generated_period_id' => $generatedPeriod->id,
                        'color' => $generatedPeriod->color,
                        'church_id' => $mass->church_id,
                        'start_date' => $start->toDateString(),
                        'end_date' => $end->toDateString(),
                        'rite' => $mass->rite,
                        'types' => $mass->types,
                        'title' => $mass->title,
                        'duration_minutes' => $durationMinutes,
                        'lang' => $mass->lang,
                        'comment' => $mass->comment,
                        'rrule' => $rrule,
                    ];

                }
            }
        }
        
        return $massPeriods;
    }

    static private function applyCollisionAvoidance(array $masses): array
    {
        // Kevés CalPeriod van, és minden misénél kell, ezért inkább előre egyszer töltjük be mindet.
        $calPeriods = CalPeriod::all()->keyBy('id');        
        // Aránylag kevés (kb 100) CalGeneratedPeriod van, ezért ezeket is betöltjük egyszerre.
        $calGeneratedPeriods = CalGeneratedPeriod::all()->groupBy('period_id');
        
        // Amikor nagyon sok misét kell egyszerre kezelni, akkor végtelenbe lelassulunk,
        // ezért inkább csak templomonként nézzük meg
        $massesByChurch = [];
        foreach($masses as $mass) {
            if(!isset($massesByChurch[$mass->church_id])) {
                $massesByChurch[$mass->church_id] = [];
            }
            $massesByChurch[$mass->church_id][] = $mass;
        }

        $results = [];
        foreach($massesByChurch as $masses) {
            $massesWithoutCollision = [];
            $noPeriodMasses = [];

            foreach ($masses as $mass) {
                if (empty($mass->period_id) or !isset($calPeriods[$mass->period_id])) {
                    $noPeriodMasses[] = $mass;
                    continue;
                }
                $weight = $calPeriods[$mass->period_id]->weight;
                $massesWithoutCollision[$weight][] = $mass;
            }
            
            foreach ($massesWithoutCollision as $currentWeight => $currentMasses) {
                if ($currentWeight > 1) {
                    $lowerPeriodMasses = [];

                    foreach (range(0, $currentWeight - 1) as $lowerWeight) {
                        if (isset($massesWithoutCollision[$lowerWeight])) {
                            $lowerPeriodMasses = array_merge($lowerPeriodMasses, $massesWithoutCollision[$lowerWeight]);
                        }
                    }

                    foreach ($lowerPeriodMasses as $lowerMass) {
                        foreach ($currentMasses as $higherMass) {
                            $mPeriodExists = $calGeneratedPeriods[$lowerMass->period_id] ?? false;
                            if ($mPeriodExists) {
                                $experiod = $lowerMass->experiod ?? [];
                                if (!in_array($higherMass->period_id, $experiod)) {
                                    $experiod[] = $higherMass->period_id;
                                    $lowerMass->experiod = $experiod; // csak a tömbben frissítjük
                                }
                            }
                        }
                    } 
                }
            }
            
            foreach ($massesWithoutCollision as $group) {
                $results = array_merge($results, $group);
            }

            $results = array_merge($results, $noPeriodMasses);
        }
        return $results;
    }

      /**
     * rrule alapján generál dátumokat adott időintervallumban.
     *
     * @param array $rrule
     * @param Carbon $start
     * @param Carbon $end
     * @return Carbon[]
     */
    static function generateDatesFromRrule(array $rrule, Carbon $start, Carbon $end): array
    {
        /*
        $this->logDebug("generateDatesFromRrule hívva", [
            'rrule_in' => $rrule,
            'window_start' => $start->toIso8601String(),
            'window_end'   => $end->toIso8601String(),
        ]);
        */
        $tz = $start->getTimezone()->getName();
        $origDtStart = $rrule['dtstart'] instanceof \DateTimeInterface
            ? Carbon::instance($rrule['dtstart'])->setTimezone($tz)
            : Carbon::parse($rrule['dtstart'], $tz)->setTimezone($tz);

        $hh = $origDtStart->hour;
        $mm = $origDtStart->minute;
        $ss = $origDtStart->second;


        $alignedDtStart = $start->copy()->setTime($hh, $mm, $ss);
        /*
        $this->logDebug("dtstart igazítva, idő megőrizve", [
            'orig_dtstart'   => $origDtStart->toIso8601String(),
            'aligned_dtstart'=> $alignedDtStart->toIso8601String(),
        ]);
        */
        $effectiveDtStart = $alignedDtStart;

        $rrule['dtstart'] = $effectiveDtStart;
        $rrule['until']   = $end->toIso8601String();
        /*
        $this->logDebug("RRULE normalizálva időmegőrzéssel", [
            'dtstart' => $rrule['dtstart'] instanceof Carbon ? $rrule['dtstart']->toIso8601String() : (string)$rrule['dtstart'],
            'until'   => $rrule['until'],
            'freq'    => $rrule['freq'] ?? null,
            'byweekday' => $rrule['byweekday'] ?? null,
        ]);
        */
        
        //$simpleRRule = new \SimpleRRule($rrule, fn($msg, $ctx = []) => $this->logDebug($msg, $ctx));
        $simpleRRule = new \SimpleRRule($rrule);
        $occurrences = $simpleRRule->getOccurrences();

        $occurrences = array_values(array_filter(
            $occurrences,
            fn($dt) => $dt->between($start, $end)
        ));
        /*
        $this->logDebug("generateDatesFromRrule eredmény", [
            'count' => count($occurrences),
            'first'=> isset($occurrences[0]) ? $occurrences[0]->toIso8601String() : null,
            'last' => !empty($occurrences) ? end($occurrences)->toIso8601String() : null,
        ]);
        */
        return array_map(fn($dt) => Carbon::instance($dt), $occurrences);
    }



}
