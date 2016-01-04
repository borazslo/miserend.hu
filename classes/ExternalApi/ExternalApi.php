<?php

namespace ExternalApi;

class ExternalApi {

    public $cache = "1 week"; //false or any time in strtotime() format
    public $cacheDir = 'fajlok/tmp/';
    public $queryTimeout = 30;
    public $query;
    public $name = 'external';

    function run() {
        $this->runQuery();
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
                    throw new \Exception("Overpass API return data is not a valid JSON! \n(" . $query . ")");
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
            throw new \Exception("We could not save the cacheFile to " . $this->cacheFilePath);
        }
    }

    function downloadData() {
        $this->rawData = @file_get_contents($this->apiUrl . $this->rawQuery);
        if (!$this->jsonData = json_decode($this->rawData)) {
            throw new \Exception("External API return data is not a valid JSON! \n(" . $this->rawQuery . ")");
        }
    }

    function clearOldCache() {
        $this->cache;
        $this->cacheDir;
        $files = scandir($this->cacheDir);
        foreach ($files as $file) {
            if (preg_match('/^' . $this->name . '_(.*)\.json/i', $file)) {
                $filemtime = filemtime($this->cacheDir . $file);
                $deadline = strtotime('now -' . $this->cache);
                if ($filemtime < $deadline) {
                    unlink($this->cacheDir . $file);
                }
            }
        }
    }

    function loadCacheFilePath() {
        $this->cacheFilePath = $this->cacheDir . $this->name . "_" . md5($this->query) . ".json";
    }

}
