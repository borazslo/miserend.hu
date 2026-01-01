<?php
namespace Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CalGeneratedPeriod extends CalModel
{
    protected $table = 'cal_generated_periods';

    protected $fillable = [
        'period_id', 'name', 'weight', 'start_date', 'end_date', 'color'
    ];

    protected $dates = ['start_date', 'end_date'];

    public function period(): BelongsTo
    {
        return $this->belongsTo(CalPeriod::class);
    }


    public function getStartDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }

    public function getEndDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }
}
