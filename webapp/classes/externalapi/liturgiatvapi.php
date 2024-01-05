<?php

namespace ExternalApi;

https://github.com/molnarm/zsolozsma#api

class LiturgiatvApi extends \ExternalApi\ExternalApi {

    public $name = 'liturgiatv';
    public $apiUrl = "https://liturgia.tv/" ;
    public $cache = "6 hours";
	public $testQuery = ''; // Ez a minden templomot lekérő query
	public $strictFormat = false; // Ha létezik a templom, de nincs hozzá liturgia.tv, akkor is 404-et de szöveggel


    function getByChurch($church_id) {
        
        $this->query = "miserend/".$church_id."/";
         
        $this->runQuery();
                 
        return $this->jsonData;        
    }

    function buildQuery() {
        $this->rawQuery = $this->query."?json";        
    }
    
}

