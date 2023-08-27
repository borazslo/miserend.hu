<?php

namespace ExternalApi;

https://github.com/molnarm/zsolozsma#api

class LiturgiatvApi extends \ExternalApi\ExternalApi {

    public $name = 'liturgiatv';
    public $apiUrl = "https://liturgia.tv/" ;
    public $cache = "6 hours";

    function getByChurch($church_id) {
        
        $this->query = "miserend/".$church_id."/";
         
        $this->runQuery();
                 
        return $this->jsonData;        
    }

    function buildQuery() {
        $this->rawQuery = $this->query."?json";        
    }

    function Response404() {        
        $this->rawData = "[]";
        $this->saveToCache();
        $this->jsonData = json_decode($this->rawData);
    }
    
}

