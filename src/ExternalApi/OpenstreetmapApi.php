<?php

namespace App\ExternalApi;

# https://wiki.openstreetmap.org/wiki/API_v0.6

class OpenstreetmapApi extends \App\ExternalApi\ExternalApi {

		
    public $name = 'openstreetmap';    
	public $format = 'xml';
	public $testQuery = 'user/gpx_files';
	
	
	function __construct() {
		global $config;	
		if($config['openstreetmap'] == false) throw new Exception("OpenStreetMap API is disabled or undefined!");
		$this->apiUrl = $config['openstreetmap']['apiurl']."/api/0.6/"; //dev and prod is different
		$this->userpwd = $config['openstreetmap']['user:pwd']; //dev and prod is different
		$this->curl_setopt(CURLOPT_USERPWD, $this->userpwd);

	}
	
	function buildQuery() {      
        $this->rawQuery = $this->query;        
    }
	
	function prepareNewChangeset() {
		$changeset = new \SimpleXMLElement("<osm></osm>");
		$changeset->addChild('changeset');
		$tag = $changeset->changeset->addChild('tag');
		$tag->addAttribute('k','created_by');
		$tag->addAttribute('v','borazslo');
		$tag = $changeset->changeset->addChild('tag');
		$tag->addAttribute('k','comment');
		$tag->addAttribute('v','Changes made based on miserend.hu\'s users\' experiences.');
		
		//echo $changeset->asXML();
		return $changeset;		
	}
   

}
