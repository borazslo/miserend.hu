<?php
namespace Eloquent;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


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
}
