<?php
namespace Html\Calendar\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CalSuggestionPackage extends CalModel
{
    protected $table = 'cal_suggestion_packages';

    protected array $excludeFromArray = ['updated_at'];

    protected $fillable = [
        'church_id',
        'sender_name',
        'sender_email',
        'sender_user_id',
        'state',
    ];

    public function suggestions(): HasMany
    {
        return $this->hasMany(CalSuggestion::class, 'package_id');
    }
}
