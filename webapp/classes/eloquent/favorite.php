<?php

namespace Eloquent;

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
		return \Eloquent\Church::where('ok','i')->find($this->tid);
	}

	public function getLiAttribute($value) {
		$return = "<a class='link' href='/templom/" . $this->tid . "'>" . $this->church->fullName .'</a>';
        		
		return $return;
	}

}