<?php
namespace Eloquent;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * @property string $name
 * @property int $weight
 * @property string $start_month_day
 * @property string $end_month_day
 * @property int $start_period_id
 * @property int $end_period_id
 * @property bool $all_inclusive
 * @property bool $multi_day
 * @property string $color
 */
class CalPeriod extends CalModel
{
    protected $table = 'cal_periods';

    protected $fillable = [
        'name', 'weight', 'start_month_day', 'end_month_day', 'start_period_id', 'end_period_id', 'all_inclusive', 'multi_day', 'color'
    ];

    protected $casts = [
        'name' => 'string',
        'weight' => 'integer',
        'start_month_day' => 'string',
        'end_month_day' => 'string',
        'start_period_id' => 'integer',
        'end_period_id' => 'integer',
        'all_inclusive' => 'boolean',
        'multi_day' => 'boolean',
        'color' => 'string',
    ];

    public function generatedPeriods(): HasMany
    {
        return $this->hasMany(CalGeneratedPeriod::class, 'period_id');
    }

    public function startPeriod(): BelongsTo
    {
        return $this->belongsTo(CalPeriod::class, 'start_period_id');
    }

    public function endPeriod(): BelongsTo
    {
        return $this->belongsTo(CalPeriod::class, 'end_period_id');
    }

    static public function generateCalGeneratedPeriods(int $year): void
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
