<?php

namespace Html\Ajax;

class OSMOsszeKapcsol extends Ajax {

    public function __construct() {
        $this->content = osm_kapcsol_ment($_POST['oid'], $_POST['tid']);
    }

}
