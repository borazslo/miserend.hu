<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Model;

class OSMTag extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'osmtags';
    protected $fillable = ['osmtype', 'osmid', 'name', 'value'];

    public function shortcut()
    {
        return $this->belongsTo(KeywordShortcut::class, 'id', 'osmtag_id');
    }

    public function delete()
    {
        $this->shortcut()->delete();
        parent::delete();
    }
}
