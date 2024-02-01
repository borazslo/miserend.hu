<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Model;

class KeywordShortcut extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = ['id', 'osmtag_id'];
}
