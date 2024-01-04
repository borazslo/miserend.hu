<?php

namespace App\ExternalApi;

class KozossegekApi extends \App\ExternalApi\ExternalApi {

    public $name = 'kozossegek';
    public $apiUrl = "https://kozossegek.hu/api/v1/" ;    
	public $cache = "1 week"; //false or any time in strtotime() format
	public $testQuery = 'miserend/1168';
    
    function buildQuery() {
        global $config;
        $this->rawQuery = $this->query;        
    }
	
}

