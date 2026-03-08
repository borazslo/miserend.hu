<?php

namespace Eloquent;

class ExternalCalendar extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'external_calendars';
    
    protected $fillable = ['church_id', 'name', 'url', 'active', 'last_import_at'];
    
    protected $dates = ['last_import_at', 'created_at', 'updated_at'];
    
    /**
     * Relationship: External calendar belongs to a church
     */
    public function church() {
        return $this->belongsTo(Church::class, 'church_id');
    }
    
    /**
     * Scope: Get active external calendars
     */
    public function scopeActive($query) {
        return $query->where('active', 1);
    }
    
    /**
     * Scope: Get external calendars for a specific church
     */
    public function scopeForChurch($query, $churchId) {
        return $query->where('church_id', $churchId);
    }
}
