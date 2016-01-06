<?php

namespace Html;

class Map extends Html {

    public function __construct() {
        $this->setTitle("OSM Térkép");

        if (isset($_REQUEST['lat']) AND is_numeric($_REQUEST['lat']))
            $this->lat = $_REQUEST['lat'];
        else
            $this->lat = 47.5;
        if (isset($_REQUEST['lon']) AND is_numeric($_REQUEST['lon']))
            $this->lon = $_REQUEST['lon'];
        else
            $this->lon = 19.05;
        if (isset($_REQUEST['zoom']) AND is_numeric($_REQUEST['zoom']))
            $this->zoom = $_REQUEST['zoom'];
        else
            $this->zoom = 12;
    }

}
