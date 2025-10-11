<?php

namespace Html\Calendar\Model;

class CalMass extends CalModel
{
    protected $table = 'cal_masses';

    protected $fillable = [
        'church_id',
        'period_id',
        'title',
        'types',
        'rite',
        'start_date',
        'duration',
        'rrule',
        'experiod',
        'exdate',
        'lang',
        'comment',
    ];

    protected $casts = [
        'church_id' => 'integer',
        'period_id' => 'integer',
        'title' => 'string',
        'types' => 'array',     // JSON stringből PHP tömb
        'rite' => 'string',
        'start_date' => 'string',
        'duration' => 'array',     // JSON
        'rrule' => 'array',     // JSON
        'experiod' => 'array',     // JSON
        'exdate' => 'array',     // JSON
        'lang' => 'string',
        'comment' => 'string',
    ];

    protected $primaryKey = 'id';
    protected $keyType = 'int';
}
