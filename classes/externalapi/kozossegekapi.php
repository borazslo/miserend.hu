<?php

namespace ExternalApi;

class KozossegekApi extends \ExternalApi\ExternalApi {

    public $name = 'kozossegek';
    public $apiUrl = "https://kozossegek.hu/api/v1/" ;    
	public $cache = "1 week"; //false or any time in strtotime() format
    
    function buildQuery() {
        global $config;
        $this->rawQuery = $this->query;        
    }

}

