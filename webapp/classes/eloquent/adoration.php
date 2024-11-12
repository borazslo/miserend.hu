<?php


namespace Eloquent;



class Adoration extends  \Illuminate\Database\Eloquent\Model 
{
    protected $table = 'szentsegimadasok';

    protected $fillable = [
        'church_id',
        // Add other fillable fields here
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}
?>