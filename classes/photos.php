<?php

class Photos {

    /**
     * A méret nélküli képeknek próbál méretet találni.
     */
    public function cron() {
        $photos = \Eloquent\Photo::
            whereNull('width')
            ->orWhereNull('height')
            ->orWhere('width',0)
            ->orWhere('height',0)
            ->limit(2)
            ->get();
        
        foreach($photos as $photo) {
            $photo->updateSize();                                          
        }  
    }
    
}
