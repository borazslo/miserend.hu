<?php

namespace Eloquent;

class Photo extends \Illuminate\Database\Eloquent\Model {

    protected $urlToPhotos = '/kepek/templomok';
    protected $pathToPhotos = '/kepek/templomok';
    protected $appends = array('url', 'smallUrl');

    public function church() {
        return $this->belongsTo('\Eloquent\Church');
    }

    public function scopeOrdered($query) {

        return $query->orderBy('flag')
                        ->orderByRaw("CASE WHEN height/width > 1 THEN 1 ELSE 0 END desc")
                        ->orderBy("weight")
                        ->orderBy("id");
    }

    public function scopeBig($query) {
        return $query->where(function ($query) {
                    $query->where('width', '>', 100)
                            ->where('height', '>=', 600);
                });
    }

    public function scopeVertical($query) {
        return $query->where('height', '>', 'width');
    }

    public function getUrlAttribute($value) {
        return $this->urlToPhotos . "/" . $this->church_id . "/" . $this->filename;
    }

    public function getSmallUrlAttribute($value) {
        return $this->urlToPhotos . "/" . $this->church_id . "/kicsi/" . $this->filename;
    }

    public function delete() {
        if ($this->attributes['filename']) {
            $file = $this->pathToPhotos . "/" . $this->church_id . "/" . $this->filename;
            if (file_exists($file)) {
                unset($file);
            }
            $file = $this->pathToPhotos . "/" . $this->church_id . "/kicsi/" . $this->filename;
            if (file_exists($file)) {
                unset($file);
            }
        }
        parent::delete();
    }

}
