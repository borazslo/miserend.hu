<?php

# http://wiki.openstreetmap.org/wiki/Overpass_API#Introduction

class OverpassApi {

    public $apiUrl = "http://overpass-api.de/api/interpreter";
    public $cache = "1 week"; //false or any time in strtotime() format
    public $cacheDir = 'fajlok/tmp/';
    public $queryTimeout = 30;
    public $query;

    function run() {
        $this->runQuery();
    }

    function buildQuery() {
        $this->rawQuery = "[out:json][timeout:" . $this->queryTimeout . "];";
        $this->rawQuery .= $this->query;
    }

    function runQuery() {
        if (!$this->rawQuery) {
            $this->buildQuery();
        }

        if ($this->cache) {
            $this->loadCacheFilePath();
            $this->tryToLoadFromCache();
        }

        if (!$this->rawData) {
            $this->downloadData();
        }

        if ($this->cache) {
            $this->saveToCache();
        }
    }

    function tryToLoadFromCache() {
        if (file_exists($this->cacheFilePath)) {
            if (filemtime($this->cacheFilePath) > strtotime("-" . $this->cache)) {
                $this->rawData = file_get_contents($this->cacheFilePath);
                if (!$this->jsonData = json_decode($this->rawData)) {
                    throw new Exception("Overpass API return data is not a valid JSON! \n(" . $query . ")");
                } else {
                    return true;
                }
            } else {
                unlink($this->cacheFilePath);
                return false;
            }
        } else {
            return false;
        }
    }

    function saveToCache() {
        if (!file_put_contents($this->cacheFilePath, $this->rawData)) {
            throw new Exception("We could not save the cacheFile to " . $this->cacheFilePath);
        }
    }

    function downloadData() {
        $this->rawData = @file_get_contents($this->apiUrl . "?data=" . urlencode($this->rawQuery));
        if (!$this->jsonData = json_decode($this->rawData)) {
            throw new Exception("Overpass API return data is not a valid JSON! \n(" . $this->rawQuery . ")");
        }
    }

    function loadCacheFilePath() {
        $this->cacheFilePath = $this->cacheDir . "overpass_" . md5($this->query) . ".json";
    }

    function buildEnclosingBoundariesQuery($lat, $lon) {
        $this->queryFilter = "['type'='boundary']";
        $this->buildEnclosingFeaturesQuery($lat, $lon);
    }

    function buildEnclosingFeaturesQuery($lat, $lon) {
        $this->query = "is_in(" . $lat . "," . $lon . ")->.a;"
                . "node" . $this->queryFilter . "(pivot.a);out tags;"
                . "way" . $this->queryFilter . "(pivot.a);out tags;"
                . "relation" . $this->queryFilter . "(pivot.a);"
                . "out tagsb;";
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

            $newOSM->tags()->delete();
            foreach ($element->tags as $name => $value) {
                $tag = \Eloquent\OSMTag::firstOrNew(['name' => $name, 'value' => $value]);
                $newOSM->tags()->save($tag);
            }
        }
    }

}
