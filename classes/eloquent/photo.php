<?php

namespace Eloquent;

class Photo extends \Illuminate\Database\Eloquent\Model {

    protected $urlToPhotos = '/kepek/templomok';
    protected $pathToPhotos = PATH . 'kepek/templomok';
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

    function uploadFile($inputFile) {
        if ($inputFile["error"] != UPLOAD_ERR_OK) {
            throw new \Exception("There is no correct \$_FILES['FileInput'].");
        }
        if (!isset($this->church_id)) {
            throw new \Exception("There is no `church_id` yet.");
        }
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            die('Valami gond van. Bocsánat.');
        }
        if ($inputFile["size"] > 5242880) {
            throw new \Exception("File size is too big!");
        }
        if (!in_array($inputFile['type'], ['image/jpeg'])) {
            throw new \Exception("Unsopported file type.");
        }
        $konyvtar = $this->pathToPhotos . "/" . $this->church_id;
        if (!is_dir("$konyvtar")) {
            echo PATH . $konyvtar;
            if (!mkdir("$konyvtar", 0775)) {
                throw new \Exception("Could not create the folder.");
            }
            if (!mkdir("$konyvtar/kicsi", 0775)) {
                throw new \Exception("Could not create the folder.");
            }
        }

        $File_Name = strtolower($inputFile['name']);
        $File_Ext = substr($File_Name, strrpos($File_Name, '.')); //get file extention
        $Random_Number = rand(0, 9999999999); //Random number to be added to name.
        $this->filename = $Random_Number . $File_Ext; //new file name

        if (!move_uploaded_file($inputFile['tmp_name'], $konyvtar . "/" . $this->filename)) {
            throw new \Exception("Could not move the file to its new place.");
        }

        $kimenet = $konyvtar . "/" . $this->filename;
        $kimenet1 = $konyvtar . "/kicsi/" . $this->filename;
        $info = getimagesize($kimenet);
        $this->width = $info[0];
        $this->height = $info[1];

        if ($this->width > 1200 or $this->height > 800)
            $this->kicsinyites($kimenet, $kimenet, 1200);
        $this->kicsinyites($kimenet, $kimenet1, 120);
    }

    static function kicsinyites($forras, $kimenet, $max) {

        if (!isset($max))
            $max = 120;# maximum size of 1 side of the picture.

        $src_img = ImagecreateFromJpeg($forras);

        $oh = imagesy($src_img);  # original height
        $ow = imagesx($src_img);  # original width

        $new_h = $oh;
        $new_w = $ow;

        if ($oh > $max || $ow > $max) {
            $r = $oh / $ow;
            $new_h = ($oh > $ow) ? $max : $max * $r;
            $new_w = $new_h / $r;
        }

        // note TrueColor does 256 and not.. 8
        $dst_img = ImageCreateTrueColor($new_w, $new_h);
        /* imageantialias($dst_img, true); */

        /* ImageCopyResized($dst_img, $src_img, 0,0,0,0, $new_w, $new_h, ImageSX($src_img), ImageSY($src_img)); */
        ImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $new_w, $new_h, ImageSX($src_img), ImageSY($src_img));
        ImageJpeg($dst_img, "$kimenet");
    }

}
