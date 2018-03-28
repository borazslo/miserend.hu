<?php

namespace ExternalApi;

# http://wiki.openstreetmap.org/wiki/Overpass_API#Introduction

class OverpassApi extends \ExternalApi\ExternalApi {

    public $name = 'overpass';
    public $apiUrl = "http://overpass-api.de/api/interpreter";

    function buildQuery() {
        $this->rawQuery = "[out:json][timeout:" . $this->queryTimeout . "];";
        $this->rawQuery .= $this->query;
        $this->rawQuery = "?data=" . urlencode($this->rawQuery);
    }

    function buildEnclosingBoundariesQuery($lat, $lon) {
        $this->queryFilter = "['type'='boundary']";
        $this->buildEnclosingFeaturesQuery($lat, $lon);
    }

    function buildEnclosingFeaturesQuery($lat, $lon) {
        $this->query = "is_in(" . $lat . "," . $lon . ")->.a;"
                . "node" . $this->queryFilter . "(pivot.a);out bb center tags;"
                . "way" . $this->queryFilter . "(pivot.a);out bb center tags;"
                . "relation" . $this->queryFilter . "(pivot.a);out bb center tags;";
        $this->buildQuery();
    }

    function buildSimpleQuery($filter = false) {
        if ($filter) {
            $this->queryFilter = $filter;
        }
        $this->query = "("
                . "node" . $this->queryFilter . ";"
                . "way" . $this->queryFilter . ";"
                . "relation" . $this->queryFilter . ";);"
                . "out body qt center;";
        $this->buildQuery();
    }

    function downloadEnclosingBoundaries($lat, $lon) {
        $this->buildEnclosingBoundariesQuery($lat, $lon);
        $this->run();
    }

    function downloadUrlMiserend() {
        $this->buildSimpleQuery("['url:miserend']");
        $this->run();
    }

    function updateUrlMiserend() {
        $this->downloadUrlMiserend();
        $this->saveElement();
        $this->saveChurchOsmRelation();
    }

    function updateEnclosing(\Eloquent\OSM $osm) {
        $this->downloadEnclosingBoundaries($osm->lat, $osm->lon);
        $this->saveElement();
        $this->saveOsmEnclosingRelation($osm);
    }

    function saveChurchOsmRelation() {
        $osmElements = \Eloquent\Osm::whereHas('tags', function ($query) {
                    $query->where('name', 'url:miserend');
                });
        foreach ($osmElements->get() as $element) {
            $urlMiserend = $element->tags->where('name', 'url:miserend')->first()->value;
            preg_match('/miserend\.hu\/\?{0,1}templom(\/|=)([0-9]{1,5})/i', $urlMiserend, $match);
            if(isset($match[2])) {
                $element->churches()->detach([$match[2]]);
                $element->churches()->attach([$match[2]]);
            }
        }
    }

    function saveOsmEnclosingRelation(\Eloquent\OSM $osm) {
        foreach ($this->jsonData->elements as $element) {
            $osmElement = \Eloquent\OSM::where('osmid', $element->id)->where('osmtype', $element->type)->first();
            $sync[] = $osmElement->id;
        }
        $osm->enclosing()->sync($sync);
    }

    function saveElement() {
        if (!$this->jsonData->elements) {
            throw new Exception("Missing Json Elements from OverpassApi Query");
        }
        foreach ($this->jsonData->elements as $element) {
            if (isset($element->center->lat)) {
                $element->lat = $element->center->lat;
            }
            if (isset($element->center->lon)) {
                $element->lon = $element->center->lon;
            }

            $newOSM = \Eloquent\OSM::firstOrNew(array('osmid' => $element->id, 'osmtype' => $element->type));
            $newOSM->lat = $element->lat;
            $newOSM->lon = $element->lon;
            $newOSM->save();

            $now = time();
            foreach ($element->tags as $name => $value) {
                $tag = \Eloquent\OSMTag::firstOrNew(['osm_id' => $newOSM->id, 'name' => $name]);
                $tag->value = $value;
                $newOSM->tags()->save($tag);
            }
            $tags = \Eloquent\OSMTag::where('osm_id', $newOSM->id)->where('updated_at', "<", $now)->get();
            foreach ($tags as $tag) {
                $tag->delete();
            }
        }
    }

}
