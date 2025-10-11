<?php
namespace Html\Calendar\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalSuggestion extends CalModel
{
    protected $table = 'cal_suggestions';

    protected $casts = [
        'changes' => 'array',
    ];

    protected $fillable = [
        'package_id',
        'period_id',
        'mass_id',
        'mass_state',
        'changes',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(CalSuggestionPackage::class, 'package_id');
    }
}
