<?php

namespace html\calendar;

use Carbon\Carbon;

use Eloquent\CalPeriod;
use Eloquent\CalPeriodYear;
use Eloquent\CalGeneratedPeriod;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

class Periods extends \Html\Calendar\CalendarApi {

    private array $years;

    public function __construct($path) {
        global $user;

        $this->years = [
            Carbon::now()->year - 1,
            Carbon::now()->year,
            Carbon::now()->year + 1
        ];

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                http_response_code(200);
                exit();
            case 'GET':
                //ha szerkeszteni is akarjuk, lekérjük az időszak éveket +- 1 évre
                if (!empty($path[0]) && $path[0] === 'edit') {

                    if (!$user->checkRole('miserend')) {
                        $this->sendJsonError('Nincs jogosultságod megnézni a periódusok listáját.', 405);
                        exit();
                    }

                    $existing = CalPeriodYear::whereIn('start_year', $this->years)
                        ->get()
                        ->map(fn($py) => $py->period_id . '-' . $py->start_year)
                        ->toArray();

                    // 2. Lekérjük azokat a periodusokat, amik nem kapcsolódnak másikhoz, és nem fixek minden évben
                    $independentPeriods = CalPeriod::whereNull('start_period_id')
                        ->whereNull('end_period_id')
                        ->whereNull('start_month_day')
                        ->whereNull('end_month_day')
                        ->get();

                    $now = Carbon::now();
                    $toInsert = [];

                    foreach ($this->years as $year) {
                        foreach ($independentPeriods as $period) {
                            $key = $period->id . '-' . $year;
                            if (!in_array($key, $existing)) {
                                $toInsert[] = [
                                    'period_id' => $period->id,
                                    'start_year' => $year,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            }
                        }
                    }

                    if (!empty($toInsert)) {
                        CalPeriodYear::insert($toInsert);
                    }

                    $periodsYear = CalPeriodYear::whereIn('start_year', $this->years)->get();

                    echo json_encode($periodsYear->toArray());
                } else {
                    //ha csak simán lekérjük a periódusokat
                    $periods = CalPeriod::all();
                    $generatedPeriods = CalGeneratedPeriod::all();

                    $result = [
                        'periods' => $periods->toArray(),
                        'generatedPeriods' => $generatedPeriods->toArray()
                    ];

                    echo json_encode($result);
                }
                break;
            case 'POST':
                if (!$user->checkRole('miserend')) {
                    $this->sendJsonError('Nincs jogosultságod megnézni a periódusok listáját.', 405);
                    exit();
                }

                if (!empty($path[0]) && $path[0] === 'generate') {
                    //legeneráljuk az időszakokat
                    $this->generatePeriods();
                } else {
                    //időszak éveket mentünk
                    $this->savePeriodYears();
                }

                break;

            default:
                $this->sendJsonError('Method not allowed', 405);
                exit();
        }
    }

    private function generatePeriods(): void {
        foreach ($this->years as $year) {
            try {
                $this->generateCalGeneratedPeriods($year);
            } catch (\Exception $e) {
                $this->sendJsonError("$year: " . $e->getMessage(), 422);
                return;
            }
        }

        echo json_encode([
            'message' => "Sikeresen generált évek: " . implode(", ", $this->years)
        ]);
    }

    private function savePeriodYears(): void {
        $input = json_decode(file_get_contents('php://input'), true);

        if ($input === null) {
            $this->sendJsonError('Invalid JSON data received.', 400);
        }

        $now = Carbon::now();
        $upsert = [];

        foreach ($input as $index => $item) {
            if (!isset($item['periodId'], $item['startYear'])) {
                $this->sendJsonError("Missing required fields at index $index", 422);
            }

            // Ha minden rendben van, összegyűjtjük a beszúrandó adatokat
            $upsert[] = [
                'id' => $item['id'],
                'period_id' => $item['periodId'],
                'start_year' => $item['startYear'],
                'start_date' => $item['startDate'] ?? null,
                'end_date' => $item['endDate'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($upsert)) {
            CalPeriodYear::upsert($upsert, ['id'],
                ['period_id', 'start_year', 'start_date', 'end_date', 'created_at', 'updated_at']);
        }

        echo json_encode([
            'message' => 'Sikeres mentés! Ne felejts el generálni se! :)',
            'count' => count($upsert),
        ]);
    }

    private function sendJsonError($message, $code): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $message,
            'code' => $code,
        ]);
    }

    private function generateCalGeneratedPeriods(int $year): void
    {
        CalGeneratedPeriod::whereYear('start_date', $year)->delete();

        $generated = [];

        $years = CalPeriodYear::whereIn('start_year', [$year, $year + 1])->get()->groupBy('start_year')->map->keyBy('period_id');

        // 1. Ha fix a periódus minden évben
        $periodsWithMonthDays = CalPeriod::whereNotNull('start_month_day')->get();

        foreach ($periodsWithMonthDays as $period) {
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', "$year-" . $period->start_month_day);
            } catch (\Exception) {
                throw new \Exception("Hibás start_month_day formátum: {$period->start_month_day}");
            }

            if ($period->end_month_day) {
                try {
                    $endDate = Carbon::createFromFormat('Y-m-d', "$year-" . $period->end_month_day);
                } catch (\Exception) {
                    throw new \Exception("Hibás end_month_day formátum: {$period->end_month_day}");
                }
            } else {
                $endDate = (clone $startDate)->addDay();
            }

            if ($endDate->equalTo($startDate) || $period->all_inclusive) {
                $endDate->addDay();
            }

            $generated[] = [
                'period_id' => $period->id,
                'name' => $period->name,
                'weight' => $period->weight,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'color' => $period->color,
            ];
        }

        // 2. Ha nem fix minden évben, és nem másik időszaktól függenek
        $periodsWithYearData = CalPeriod::whereNull('start_month_day')
            ->whereNull('start_period_id')
            ->whereNull('end_period_id')
            ->get();

        foreach ($periodsWithYearData as $period) {
            $yearData = $years[$year][$period->id] ?? null;

            if (!$yearData) {
                throw new \Exception("Hiányzó CalPeriodYear adat (period_id: {$period->id}, év: $year)");
            }

            try {
                $startDate = Carbon::parse($yearData->start_date);
            } catch (\Exception) {
                throw new \Exception("Érvénytelen start_date a CalPeriodYear-ben (period_id: {$period->id})");
            }

            if ($yearData->end_date) {
                $endDate = Carbon::parse($yearData->end_date);

                // Ha end_date < start_date → nézzük a következő évet
                if ($endDate->lt($startDate)) {
                    $nextYearData = $years[$year + 1][$period->id] ?? null;
                    if (!$nextYearData || !$nextYearData->end_date) {
                        continue; // skip, ha nincs értelmes end_date
                    }
                    $endDate = Carbon::parse($nextYearData->end_date);
                }
            } else {
                $endDate = (clone $startDate)->addDay();
            }

            if ($endDate->equalTo($startDate)) {
                $endDate->addDay();
            }

            $generated[] = [
                'period_id' => $period->id,
                'name' => $period->name,
                'weight' => $period->weight,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'color' => $period->color,
            ];
        }

        // 3. Ha másik időszakoktól függ
        $linkedPeriods = CalPeriod::whereNotNull('start_period_id')
            ->whereNotNull('end_period_id')
            ->get();

        $allPeriods = CalPeriod::all()->keyBy('id');

        foreach ($linkedPeriods as $period) {
            $startSource = $allPeriods[$period->start_period_id] ?? null;
            $endSource = $allPeriods[$period->end_period_id] ?? null;

            if (!$startSource || !$endSource) {
                throw new \Exception("Hiányzó start/end period for CalPeriod {$period->id}");
            }

            // START
            if ($startSource->start_month_day) {
                $startDate = Carbon::createFromFormat('Y-m-d', "$year-" . $startSource->start_month_day);
            } else {
                $startYearData = $years[$year][$startSource->id] ?? null;
                if (!$startYearData) {
                    continue;
                    //throw new \Exception("Hiányzó CalPeriodYear adat start_period ({$startSource->id})");
                }
                $startDate = Carbon::parse($startYearData->start_date);
            }

            //Ha nincs kezdő dátum, akkor kihagyjuk
            if (!$startDate) {
                continue;
            }

            // END
            if ($endSource->start_month_day) {
                //Ha a vég referencia fix dátummal rendelkezik, akkor az lesz a vég dátum
                $endDate = Carbon::createFromFormat('Y-m-d', "$year-" . $endSource->start_month_day);

                //Ha éven átívelő az időszak, akkor az end < start, ebben az esetben növeljük az évet
                if ($endDate->lt($startDate)) {
                    $endDate->addYear();
                }
            } else {
                $endYearData = $years[$year][$endSource->id] ?? null;

                if ($endYearData && $endYearData->start_date && !(Carbon::parse($endYearData->start_date))->lt($startDate)) {
                    $endDate = Carbon::parse($endYearData->start_date);
                } else {
                    $nextYearData = $years[$year + 1][$endSource->id] ?? null;
                    if (!$nextYearData || !$nextYearData->start_date) {
                        continue;
                    }
                    $endDate = Carbon::parse($nextYearData->start_date);
                }
            }

            //Ha nincs végdátum, akkor kihagyjuk
            if (!$endDate) {
                continue;
            }

            if ($period->all_inclusive) {
                $endDate->addDay();
            }

            if ($endDate->equalTo($startDate)) {
                $endDate->addDay();
            }

            $generated[] = [
                'period_id' => $period->id,
                'name' => $period->name,
                'weight' => $period->weight,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'color' => $period->color,
            ];
        }

        // Tömeges beszúrás
        if (!empty($generated)) {
            foreach (array_chunk($generated, 1000) as $chunk) {
                CalGeneratedPeriod::insert($chunk);
            }
        }
    }
}
