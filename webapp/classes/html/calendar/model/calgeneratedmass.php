<?php
namespace Html\Calendar\Model;

use Illuminate\Database\Eloquent\Model;

class CalGeneratedMass extends Model
{
protected $table = 'cal_generated_masses';

protected $fillable = [
'church_id',
'mass_id',
'start_date',
'title',
'types',
'rite',
'duration',
'lang',
'comment',
];

protected $casts = [
'church_id' => 'integer',
'mass_id' => 'integer',
'start_date' => 'datetime',
'title' => 'string',
'types' => 'array',    // JSON → array
'rite' => 'string',
'duration' => 'array', // JSON → array with keys: hours, minutes
'lang' => 'string',
'comment' => 'string',
];
}

