<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Model;

class Distance extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['church_from', 'church_to'];

    public function getToAttribute($value)
    {
        return Church::find($this->church_to);
    }

    public function getFromAttribute($value)
    {
        return Church::find($this->church_from);
    }
}
