<?php
namespace Html\Calendar\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalSuggestionPackage extends CalModel
{
    protected $table = 'cal_suggestion_packages';

    protected array $excludeFromArray = ['updated_at'];
    
    

    protected $fillable = [
        'church_id',
        'sender_name',
        'sender_email',
        'sender_user_id',
        'sender_message',
        'state',
    ];

    public function suggestions(): HasMany
    {
        return $this->hasMany(CalSuggestion::class, 'package_id');
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(\Eloquent\Church::class, 'church_id');
    }

    /**
     * Legacy user accessor: returns an instance of \User for sender_user_id if available.
     * Usage: $pkg->senderUser or in Twig: sgpack.senderUser
     * Note: \User is not an Eloquent model, it is the legacy user class in classes/user.php
     *
     * @return \User|null
     */
    public function getSenderUserAttribute()
    {
        if (isset($this->sender_user_id) && $this->sender_user_id) {
            return new \User($this->sender_user_id);
        }
        return null;
    }

}
