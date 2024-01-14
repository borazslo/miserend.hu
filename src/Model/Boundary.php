<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

class Boundary extends \Illuminate\Database\Eloquent\Model
{
    // protected $table = 'osmtags';
    protected $fillable = ['osmtype', 'osmid', 'boundary', 'denomination', 'admin_level', 'name'];
    protected $appends = ['url'];

    public function getUrlAttribute($value)
    {
        return 'https://www.openstreetmap.org/'.$this->osmtype.'/'.$this->osmid;
    }

    public function location()
    {
        $location = OSM::where('osmtype', $this->osmtype)
                    ->where('osmid', $this->osmid)
                    ->first();

        return $location;
    }
}
