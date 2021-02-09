<?php

namespace ExternalApi;

class ExternalApi {

    public $cache = "1 week"; //false or any time in strtotime() format
    public $cacheDir = PATH . 'fajlok/tmp/';
    public $queryTimeout = 30;
    public $query;
    public $name = 'external';

    function run() {
        $this->runQuery();
    }

    function runQuery() {
        if (!isset($this->rawQuery)) {
            $this->buildQuery();
        }

        if ($this->cache) {
            $this->loadCacheFilePath();
            $this->tryToLoadFromCache();
        }

        if (!isset($this->rawData)) {
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
                    throw new \Exception("External API data has been loaded from cache but data is not a valid JSON!");
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
        $header = array("cache-control: no-cache","Content-Type: application/json");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->apiUrl . $this->rawQuery);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_HEADER  , false);  // we want headers
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
        curl_setopt($ch, CURLOPT_USERAGENT, "miserend.hu");

        $this->rawData = curl_exec($ch);
    
        $this->responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE );      
        switch ($this->responseCode) {
            case '200':
                if (!$this->jsonData = json_decode($this->rawData)) {            
                    throw new \Exception("External API return data is not a valid JSON!");
                }
                break;

            default:
                throw new \Exception("External API returned bad http response code: " . $this->responseCode. "\n<br>" . $this->rawData);
                break;
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
