<?php

namespace Eloquent;


class Attribute extends \Illuminate\Database\Eloquent\Model 
{
 
	// Disable automatic timestamps
    public $timestamps = false;
	
    protected $fillable = ['church_id', 'key', 'value','fromOSM'];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}