<?php
namespace Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalPeriodYear extends CalModel
{
    protected $table = 'cal_period_years';

    protected $fillable = [
        'period_id', 'start_year', 'start_date', 'end_date'
    ];

    protected $dates = ['start_date', 'end_date'];

    public function period(): BelongsTo
    {
        return $this->belongsTo(CalPeriod::class);
    }
}
