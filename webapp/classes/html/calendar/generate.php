<?php
namespace Html\Calendar;

use Carbon\Carbon;
use ExternalApi\ElasticsearchApi;
use Html\Calendar\Model\CalGeneratedPeriod;
use Html\Calendar\Model\CalMass;
use Html\Calendar\Model\CalPeriod;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

class Generate extends \Html\Calendar\CalendarApi {

    protected $elastic;

    public function __construct($path) {

        $this->elastic = new ElasticsearchApi();        
        if (!$this->elastic->isexistsIndex('mass_index')) {
            $this->createMassIndex();            
        }

        $this->tids = !empty($_GET['tids']) ? (is_array($_GET['tids']) ? $_GET['tids'] : [$_GET['tids']]) : [];
        if (empty($this->tids)) {
            $this->sendJsonError('Nincs templom ID megadva.', 400);
            exit;
        }

        $this->years = !empty($_GET['years']) ? (is_array($_GET['years']) ? $_GET['years'] : [$_GET['years']]) : [];
        if (empty($this->years)) {
            $this->sendJsonError('Nincs év megadva.', 400);
            exit;
        }


        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                http_response_code(200);
                exit();

            case 'GET':
                $this->lang       = $_GET['lang'] ?? null;
                $this->notLang    = $_GET['not_lang'] ?? null;
                $this->type       = $_GET['type'] ?? null;
                $this->notType    = $_GET['not_type'] ?? null;
                $this->rite       = $_GET['rite'] ?? null;
                $this->notRite    = $_GET['not_rite'] ?? null;
                $this->title      = $_GET['title'] ?? null;
                $this->notTitle   = $_GET['not_title'] ?? null;
                $this->comment    = $_GET['comment'] ?? null;
                $this->notComment = $_GET['not_comment'] ?? null;

                $this->durationMin      = $_GET['duration_min'] ?? null;
                $this->durationMax      = $_GET['duration_max'] ?? null;
                $this->dateMin          = $_GET['date_min'] ?? null;
                $this->dateMax          = $_GET['date_max'] ?? null;
                $this->startMinutesMin  = $_GET['start_minutes_min'] ?? null;
                $this->startMinutesMax  = $_GET['start_minutes_max'] ?? null;
                $timezone = $_GET['timezone'] ?? 'UTC';

                $query = ["bool" => ["must" => [], "must_not" => []]];

                // templom ID-k
                $query['bool']['must'][] = [
                    "terms" => ["church_id" => array_map('intval', $this->tids)]
                ];

                // évszűrés
                if (!empty($this->years)) {
                    $query['bool']['must'][] = [
                        "range" => [
                            "start_date" => [
                                "gte" => min($this->years) . "-01-01T00:00:00",
                                "lte" => max($this->years) . "-12-31T23:59:59"
                            ]
                        ]
                    ];
                }

                // nyelv
                if ($this->lang)      $query['bool']['must'][]     = ["term" => ["lang" => $this->lang]];
                if ($this->notLang)   $query['bool']['must_not'][] = ["term" => ["lang" => $this->notLang]];

                // típus
                if ($this->type)      $query['bool']['must'][]     = ["term" => ["types" => $this->type]];
                if ($this->notType)   $query['bool']['must_not'][] = ["term" => ["types" => $this->notType]];

                // rítus
                if ($this->rite)      $query['bool']['must'][]     = ["term" => ["rite" => $this->rite]];
                if ($this->notRite)   $query['bool']['must_not'][] = ["term" => ["rite" => $this->notRite]];

                if ($this->title) {
                    $query['bool']['must'][] = [
                        "wildcard" => [
                            "title" => "*" . strtolower($this->title) . "*"
                        ]
                    ];
                }

                if ($this->notTitle) {
                    $query['bool']['must_not'][] = [
                        "wildcard" => [
                            "title" => "*" . strtolower($this->notTitle) . "*"
                        ]
                    ];
                }

                if ($this->comment) {
                    $query['bool']['must'][] = [
                        "wildcard" => [
                            "comment" => "*" . strtolower($this->comment) . "*"
                        ]
                    ];
                }

                if ($this->notComment) {
                    $query['bool']['must_not'][] = [
                        "wildcard" => [
                            "comment" => "*" . strtolower($this->notComment) . "*"
                        ]
                    ];
                }
                // duration range (percekben)
                if ($this->durationMin !== null || $this->durationMax !== null) {
                    $range = [];
                    if ($this->durationMin !== null) $range['gte'] = (int)$this->durationMin;
                    if ($this->durationMax !== null) $range['lte'] = (int)$this->durationMax;

                    $query['bool']['must'][] = [
                        "range" => ["duration_minutes" => $range]
                    ];
                }

                // dátum napokra szűrés (range a start_date-re)
                if ($this->dateMin !== null || $this->dateMax !== null) {
                    $range = [];
                    if ($this->dateMin !== null) $range['gte'] = $this->dateMin . "T00:00:00";
                    if ($this->dateMax !== null) $range['lte'] = $this->dateMax . "T23:59:59";

                    $query['bool']['must'][] = [
                        "range" => ["start_date" => $range]
                    ];
                }

                if ($this->startMinutesMin !== null || $this->startMinutesMax !== null) {
                    $range = [];
                    if ($this->startMinutesMin !== null) $range['gte'] = (int)$this->startMinutesMin;
                    if ($this->startMinutesMax !== null) $range['lte'] = (int)$this->startMinutesMax;

                    $query['bool']['must'][] = [
                        "range" => ["start_minutes" => $range]
                    ];
                }

                // build query
                $esQuery = [
                    "query" => $query,
                    "size"  => 10000
                ];

                $this->elastic->curl_setopt(CURLOPT_CUSTOMREQUEST, "GET");
                $this->elastic->buildQuery('mass_index/_search', json_encode($esQuery));
                $this->elastic->run();

                $result = [];
                if (isset($this->elastic->jsonData->hits->hits)) {
                    foreach ($this->elastic->jsonData->hits->hits as $hit) {
                        $churchId = $hit->_source->church_id;
                        if (!isset($result[$churchId])) $result[$churchId] = [];

                        $source = $hit->_source;

                        $dateUtc = Carbon::parse($source->start_date)->setTimezone('UTC');

                        if ($timezone !== 'UTC') {
                            $dateLocal = $dateUtc->copy()->setTimezone($timezone);
                            $source->start_date = $dateLocal->format('c');
                            $source->start_minutes = $dateLocal->hour * 60 + $dateLocal->minute;
                        } else {
                            $source->start_date = $dateUtc->format('c');
                            $source->start_minutes = $dateUtc->hour * 60 + $dateUtc->minute;
                        }

                        $result[$churchId][] = $source;
                    }
                }

                echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                break;


            case 'PUT':
                $years = is_array($this->years) ? $this->years : [$this->years];

                foreach ($this->tids as $tid) {
                    $this->elastic->curl_setopt(CURLOPT_CUSTOMREQUEST, "POST");
                    $this->elastic->buildQuery('mass_index/_delete_by_query', json_encode([
                        "query" => [
                            "term" => ["church_id" => (int)$tid]
                        ]
                    ]));
                    $this->elastic->run();
                }

                $allMasses = [];
                $churches = [];
                foreach ($this->tids as $tid) {
                    $masses = $this->getByChurchId($tid);
                    if (!empty($masses)) {
                        $allMasses = array_merge($allMasses, $masses);
                    }
                    $church = \Eloquent\Church::find($tid);
                    $churchTimezones[$tid] = $church->time_zone ?? 'Europe/Budapest';
                    $churches[$tid] = $church;
                }

                $debug = [];
                $debug[] = "Talált misék száma: " . count($allMasses);

                $massesByChurch = $this->generateMassInstancesForYears($allMasses,$churchTimezones, $years);

                foreach ($massesByChurch as $churchId => $massInstances) {
                    $debug[] = "Templom ID $churchId, generált miseidőpontok: " . count($massInstances);

                    // Debug: az első 5 generált mise megjelenítése
                    $first5 = array_slice($massInstances, 0, 10);
                    foreach ($massInstances as $mi) {
                        $debug[] = "  - Mass ID {$mi['mass_id']}, start: {$mi['start_date']}, title: {$mi['title']}";
                    }
                    $churchData = $churches[$churchId]->toElasticArray();
                    $bulkInsert = [];
                    foreach ($massInstances as $massData) {
                        $bulkInsert[] = [
                            'index' => [
                                '_index' => 'mass_index',
                                '_id' => uniqid()
                            ]
                        ];
                        $massData['church'] = $churchData;
                        $bulkInsert[] = $massData;
                    }

                    if (!empty($bulkInsert)) {
                        $elasticResult = $this->elastic->putBulk($bulkInsert);
                    }
                }

                echo json_encode([
                    'success' => true,
                    'debug'   => array_merge($debug, $this->debugLog)
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                break;
        }}

    private array $debugLog = [];

    private function logDebug(string $msg, array $ctx = []): void {
        $line = $msg;
        if (!empty($ctx)) {
            $line .= " | " . json_encode($ctx, JSON_UNESCAPED_UNICODE);
        }
        $this->debugLog[] = $line;
    }


    public function getByChurchId(int $churchId) {
        return CalMass::where('church_id', $churchId)->get()->all();
    }

    private function sendJsonError($message, $code): void {
        http_response_code($code);
        echo json_encode([
            'error' => true,
            'message' => $message,
            'code' => $code,
        ]);
    }

    private function convertRangeToUtc(?int $min, ?int $max, Carbon $date): array
    {
        $range = [];

        if ($min !== null) {
            $local = $date->copy()->setTime(floor($min / 60), $min % 60);
            $utc   = $local->copy()->setTimezone('UTC');
            $range['gte'] = $utc->hour * 60 + $utc->minute;
        }

        if ($max !== null) {
            $local = $date->copy()->setTime(floor($max / 60), $max % 60);
            $utc   = $local->copy()->setTimezone('UTC');
            $range['lte'] = $utc->hour * 60 + $utc->minute;
        }

        return $range;
    }

    /**
     * rrule alapján generál dátumokat adott időintervallumban.
     *
     * @param array $rrule
     * @param Carbon $start
     * @param Carbon $end
     * @return Carbon[]
     */
    function generateDatesFromRrule(array $rrule, Carbon $start, Carbon $end): array
    {
        $this->logDebug("generateDatesFromRrule hívva", [
            'rrule_in' => $rrule,
            'window_start' => $start->toIso8601String(),
            'window_end'   => $end->toIso8601String(),
        ]);

        $tz = $start->getTimezone()->getName();
        $origDtStart = $rrule['dtstart'] instanceof \DateTimeInterface
            ? Carbon::instance($rrule['dtstart'])->setTimezone($tz)
            : Carbon::parse($rrule['dtstart'], $tz)->setTimezone($tz);

        $hh = $origDtStart->hour;
        $mm = $origDtStart->minute;
        $ss = $origDtStart->second;


        $alignedDtStart = $start->copy()->setTime($hh, $mm, $ss);
        $this->logDebug("dtstart igazítva, idő megőrizve", [
            'orig_dtstart'   => $origDtStart->toIso8601String(),
            'aligned_dtstart'=> $alignedDtStart->toIso8601String(),
        ]);
        $effectiveDtStart = $alignedDtStart;

        $rrule['dtstart'] = $effectiveDtStart;
        $rrule['until']   = $end->toIso8601String();

        $this->logDebug("RRULE normalizálva időmegőrzéssel", [
            'dtstart' => $rrule['dtstart'] instanceof Carbon ? $rrule['dtstart']->toIso8601String() : (string)$rrule['dtstart'],
            'until'   => $rrule['until'],
            'freq'    => $rrule['freq'] ?? null,
            'byweekday' => $rrule['byweekday'] ?? null,
        ]);

        $simpleRRule = new SimpleRRule($rrule, fn($msg, $ctx = []) => $this->logDebug($msg, $ctx));
        $occurrences = $simpleRRule->getOccurrences();

        $occurrences = array_values(array_filter(
            $occurrences,
            fn($dt) => $dt->between($start, $end)
        ));

        $this->logDebug("generateDatesFromRrule eredmény", [
            'count' => count($occurrences),
            'first'=> isset($occurrences[0]) ? $occurrences[0]->toIso8601String() : null,
            'last' => !empty($occurrences) ? end($occurrences)->toIso8601String() : null,
        ]);

        return array_map(fn($dt) => Carbon::instance($dt), $occurrences);
    }



    private function applyCollisionAvoidance(array $masses): array
    {
        $massesWithoutCollision = [];
        $noPeriodMasses = [];

        foreach ($masses as $mass) {
            if (empty($mass->period_id)) {
                $noPeriodMasses[] = $mass;
                continue;
            }

            $weight = CalPeriod::where('id', $mass->period_id)->value('weight');
            if ($weight === null) {
                $noPeriodMasses[] = $mass;
                continue;
            }

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
                        $mPeriodExists = CalGeneratedPeriod::where('period_id', $lowerMass->period_id)->exists();
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

        $result = [];
        foreach ($massesWithoutCollision as $group) {
            $result = array_merge($result, $group);
        }
        return array_merge($result, $noPeriodMasses);
    }


    /**
     * Generálja a miseidőpontokat adott évekre,
     * és templomonként csoportosítva visszaadja őket.
     *
     * @param array $masses
     * @param array $years
     * @return array [templom_id => miseidőpontok tömbje]
     */
    function generateMassInstancesForYears($masses, array $churchTimezones, array $years): array
    {
        $instancesByChurch = [];

        $this->logDebug("generateMassInstancesForYears indul", [
            'mass_count' => count($masses),
            'years'      => $years,
        ]);

        if (empty($masses) || empty($years)) {
            $this->logDebug("Nincs mise vagy év");
            return $instancesByChurch;
        }

        // --- 0) Ütközés elkerülés alkalmazása ---
        $masses = $this->applyCollisionAvoidance($masses);
        $this->logDebug("applyCollisionAvoidance lefutott", [
            'after_count' => count($masses),
        ]);



        foreach ($years as $year) {
            $globalStart = Carbon::create($year, 1, 1)->startOfDay();
            $globalEnd = Carbon::create($year, 12, 31)->endOfDay();

            foreach ($masses as $mass) {
                $this->logDebug("Mise feldolgozás indul", [
                    'mass_id' => $mass->id,
                    'title' => $mass->title,
                    'period_id' => $mass->period_id,
                ]);

                if (empty($mass->period_id)) {
                    $this->logDebug("Egyszeri mise", ['mass_id' => $mass->id]);
                } else if (empty($mass->rrule)) {
                    $this->logDebug("Nincs RRULE", ['mass_id' => $mass->id]);
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

                // --- kizárt dátumok ---
                $excludedDatesRaw = $mass->exdate ?? [];
                if (is_string($excludedDatesRaw)) {
                    $decoded = json_decode($excludedDatesRaw, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $excludedDatesRaw = $decoded;
                    }
                }
                $excludedDates = collect(is_array($excludedDatesRaw) ? $excludedDatesRaw : [])
                    ->map(fn($d) => Carbon::parse($d)->toDateString());

                // --- kizárt periódusok ---
                $excludedPeriods = $mass->experiod ?? [];
                if (is_string($excludedPeriods)) {
                    $decoded = json_decode($excludedPeriods, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $excludedPeriods = $decoded;
                    }
                }
                $excludedPeriods = is_array($excludedPeriods) ? $excludedPeriods : [];

                // --- periódusok betöltése ---
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

                    $recurrences = $this->generateDatesFromRrule($rrule, $start, $end);
                    $this->logDebug("Recurrence generálás", [
                        'mass_id' => $mass->id,
                        'count' => count($recurrences),
                    ]);

                    foreach ($recurrences as $date) {
                        $this->logDebug("Generált dátum", [
                            'mass_id' => $mass->id,
                            'date' => $date->toIso8601String(),
                        ]);
                        if ($excludedDates->contains($date->toDateString())) continue;

                        $insideExcluded = false;
                        foreach ($excludedPeriods as $exPid) {
                            $exP = CalGeneratedPeriod::where('period_id', $exPid)->first();
                            if ($exP) {
                                $exStart = Carbon::parse($exP->start_date)->startOfDay();
                                $exEnd = Carbon::parse($exP->end_date)->endOfDay();
                                if ($date->between($exStart, $exEnd)) {
                                    $insideExcluded = true;
                                    break;
                                }
                            }
                        }
                        if ($insideExcluded) continue;

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

        return $instancesByChurch;
    }

    public function createMassIndex(): void
    {
        $massFilePath = '../docker/elasticsearch/mappings/mass.json';
        if (!file_exists($massFilePath)) {
            throw new \Exception("File not found: " . $massFilePath);
        }
        $massData = file_get_contents($massFilePath);
        $churchFilePath = '../docker/elasticsearch/mappings/church.json';
        if (!file_exists($churchFilePath)) {
            throw new \Exception("File not found: " . $churchFilePath);
        }
        $churchData = file_get_contents($churchFilePath);
        $data = json_decode($massData, true);
        $data['settings'] = json_decode($churchData, true)['settings'];
        $data['mappings']['properties']['church'] = json_decode($churchData, true)['mappings'];

        if (!$this->elastic->putIndex('mass_index', $data)) {				
            throw new \Exception("Failed to create index: mass_index");
        }                
    }







}
