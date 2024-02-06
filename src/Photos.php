<?php

namespace App;

class Photos
{

    /**
     * A méret nélküli képeknek próbál méretet találni.
     */
    public function cron()
    {
        $photos = \App\Model\Photo::
        whereNull('width')
            ->orWhereNull('height')
            ->orWhere('width', 0)
            ->orWhere('height', 0)
            ->get();

        foreach ($photos as $photo) {
            $photo->updateSize();
        }
    }

}
