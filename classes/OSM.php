<?php

use Illuminate\Database\Capsule\Manager as DB;

class OSM {

    public $type;
    public $id;

    public function getByChurchId($id) {
        $result = DB::table('templomok')
                ->join('osm', 'osm.tid', '=', 'templomok.id')
                ->select('osm.id as osm_id', 'osm.type as osm_type')
                ->where('templomok.id', "=", $id)
                ->limit(1)
                ->get();
        if (!count($result)) {
            throw new Exception("There is no OSM data for church with tid = '$id'");
        }
        $this->type = $result[0]->osm_type;
        $this->id = $result[0]->osm_id;
        $this->getById($this->type, $this->id);
    }

    public function getById($type, $id) {
        $this->type = $type;
        $this->id = $id;

        $tags = DB::table("osm_tags")->select("*")
                ->where("type", "=", $this->type)
                ->where("id", "=", $this->id)
                ->get();
        $this->tags = array();
        foreach ($tags as $tag) {
            $tag = (array) $tag;
            if (in_array($tag['name'], array('lat', 'lon'))) {
                $this->$tag['name'] = $tag['value'];
            } else {
                $this->tags[$tag['name']] = $tag['value'];
            }
        }
    }

}
