<?php

namespace ExternalApi;

use Illuminate\Database\Capsule\Manager as DB;
        
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
        try {
        
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
            
            
        } catch(\Exception $e){
            global $config;
            $this->jsonData = [];
            $this->error = \Html\Html::printExceptionVerbose($e,true);
            if($config['debug'] > 1) echo $this->error;
            elseif($config['debug'] > 0) addMessage($this->error,'warning');
            return false;
        }
        return true;
    }

    function tryToLoadFromCache() {
        if (file_exists($this->cacheFilePath)) {
            $this->cacheFileTime = date('Y-m-d H:i:s',filemtime($this->cacheFilePath));
            if (filemtime($this->cacheFilePath) > strtotime("-" . $this->cache)) {
                $this->rawData = file_get_contents($this->cacheFilePath);
                $this->jsonData = json_decode($this->rawData);
                if ($this->jsonData === null) {
                    throw new \Exception("External API data has been loaded from cache but data is not a valid JSON!\n".$this->rawData);
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
        
        $this->saveStat();
        
        switch ($this->responseCode) {
            case '200':
                $this->jsonData = json_decode($this->rawData);
                if ($this->jsonData === null ) {            
                    throw new \Exception("External API return data is not a valid JSON!");
                }
                break;
               
            case '404':
                $this->Response404();
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
       
    function saveStat() {
        
        $query = DB::table('stats_externalapi')->where('url',$this->apiUrl.$this->rawQuery)->where('date',date('Y-m-d'));
        if($current = $query->first()) {   
            if($current->rawdata != $this->rawData ) $diff = $current->diff + 1; else $diff = $current->diff;
            $echo = $query->update([
                        'name' => $this->name,
                        'url' => $this->apiUrl.$this->rawQuery,                    
                        'date' => date('Y-m-d'),                
                        'responsecode' => $this->responseCode,
                        'rawdata' => $this->rawData,
                        'count'=> $current->count + 1,
                        'diff'=> $diff
            ]);
        } else {
            DB::table('stats_externalapi')->insert(
                [
                    'name' => $this->name,
                    'url' => $this->apiUrl.$this->rawQuery,                    
                    'date' => date('Y-m-d'),                
                    'responsecode' => $this->responseCode,
                    'rawdata' => $this->rawData,
                    'count'=> 1,
                    'diff' => 1
                ]
            );
        }
    }
    
    function Response404() {
        throw new \Exception("External API returned 404 = Not Found.");
    }
}
