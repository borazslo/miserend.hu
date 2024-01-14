<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ChurchLink extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = ['church_id', 'href', 'title'];
    protected $appends = ['type', 'icon', 'html'];

    protected $icons = [
        'instagram' => 'fab fa-instagram',
        'facebook' => 'fab fa-facebook-square',
    ];

    public function getHrefAttribute($value)
    {
        if (!preg_match('/^http(s|):\/\//i', $value)) {
            return 'http://'.$value;
        } else {
            return $value;
        }
    }

    public function getTypeAttribute($value)
    {
        $link = trim(preg_replace('/^http(s|):\/\//i', '', $this->href), '/');
        $parts = explode('/', $link);
        if (preg_match('/('.implode('|', array_keys($this->icons)).')/', $parts[0], $match)) {
            return $match[1];
        }
    }

    public function getIconAttribute($value)
    {
        if (isset($this->icons[$this->type])) {
            return $this->icons[$this->type];
        } else {
            return 'fa fa-globe';
        }
    }

    public function getHtmlAttribute($value)
    {
        return '<i class="'.$this->icon.'"></i> <a href="'.$this->href.'" title="'.$this->type.'">'.$this->title.'</a>';
    }

    public function getTitleAttribute($value)
    {
        if (!$value) {
            $link = trim(preg_replace('/^http(s|):\/\//i', '', $this->href), '/');
            $link = preg_replace('/^www\./i', '', $link);
            if ($this->type) {
                $parts = explode('/', $link);
                unset($parts[0]);

                return implode('/', $parts);
            } else {
                return $link;
            }
        } else {
            return $value;
        }
    }

    public function getChurchAttribute($value)
    {
        return Church::find($this->church_id);
    }
}
