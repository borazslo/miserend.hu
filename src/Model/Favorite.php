<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'favorites';
    protected $appends = array('church','li');

	public function getChurchAttribute($value) {
		return \App\Model\Church::where('ok','i')->find($this->tid);
	}

	public function getLiAttribute($value) {
		$return = "<a class='link' href='/templom/" . $this->tid . "'>" . $this->church->nev;
        if ($this->church->ismertnev != '')
            $return .= " (" . $this->church->ismertnev . ")";
        $return .= "</a>, " . $this->church->varos;
		
		return $return;
	}

}