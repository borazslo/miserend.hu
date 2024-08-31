<?php

namespace ExternalApi;

class SolrApi extends \ExternalApi\ExternalApi {

    public $name = 'solr';    
	public $format = 'json';
	public $apiUrl = "http://solr:8983/";

	public $testQuery = 'solr/admin/cores?action=STATUS';
	public $cache = false; // A Solr-nak meg van a saját cache-je. Arra hagyatkozunk
	
			
	function run() {					
		$this->curl_setopt(CURLOPT_HTTPHEADER ,['Content-Type: application/json']);		 	
		parent::run();
	}
	
	function buildQuery( $query = false, $data = false) {
		
		if($query != false) {
			$this->query = $query;
			$this->rawQuery = $this->query;			
		} else if ($this->query) {
			$this->rawQuery = $this->query;
		} else {
			throw new \Exception('We need query');
		}
			
		if($data != false) 
			$this->data = $data;
			
		if(isset($this->data)) {
			//$this->curl_setopt(CURLOPT_POST ,1);		 	
			//$this->curl_setopt(CURLOPT_CUSTOMREQUEST ,"PUT");		 	
			$this->curl_setopt(CURLOPT_POSTFIELDS,$this->data);			
		}
	
	}
	
	function createCollection($collection_name) {
		$url[] = "collections/";
		$data = [
		  "name" => $collection_name,
		  "numShards" => 1,
		  "replicationFactor" =>  1
		];
		
		$this->buildQuery("api/collections/",json_encode($data));
		$this->run();
			
	}
	
	function updateCollection($collection_name, $data) {
		$this->buildQuery("api/collections/".$collection_name."/update?commit=true",$data);
		$this->run();
		if(!isset($this->jsonData->responseHeader->status) OR $this->jsonData->responseHeader->status != 0)
			return false;
		return true;
	}
	
	function truncateCollection($collection_name) {

		$data = '{"delete": {"query": "*:*"}}';
		$this->buildQuery("solr/".$collection_name."/update/json?commit=true",$data);
		$this->run();

		if($this->updateCollection($collection_name, $data)) {
			return true;			
		} else
			return false;
	}
	
	function getCollections() {
		$this->buildQuery("api/collections/");
		$this->run();
		if(!isset($this->jsonData->collections))
			return false;
		
		return $this->jsonData->collections;		
	}
	
	function search($q, $params = [] ) {
		// Defaults
		$default_params = [
			'q' => $q,
			'defType' => 'edismax',
			'indent' => true,
			'fl' => 'id,nev,ismertnev,varos,fullName',
			'qf' => 'nev^8 ismertnev^4 varos^2 cim megkozelites plebania megjegyzes misemegj',
			'q.op' => 'AND'
		];
		
		$new_params = $default_params;
		foreach($params as $key => $value) {
			$new_params[$key] = $value;
		}
				
		$this->rawQuery = "solr/churches/select?".http_build_query($new_params);
		$this->run();
		
		// Valami miatt nem üzemel pl. a solr.
		if(isset($this->error)) {
			return false;
		} else {		
			return $this->jsonData->response;
		}
		
	}
	
	// Rendszeresen feltöltjük a keresőbe az adatbázisunkat, mert az jó.
	static function updateChurches() {
		$solr = new \ExternalApi\SolrApi();
		
		$collections = $solr->getCollections();
		if(!in_array('churches',$collections)) {
			$solr->createCollection('churches');
		}
		
		$churches = \Eloquent\Church::where('ok', 'i')->get()->toArray();
		
		$romai = ['0','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII','XIII','XIV','XV','XVI','XVII','XVIII','XIX','XX','XXI','XXII','XXIII'];
		foreach($churches as $c => $church) {
			preg_match('/^Budapest (.*?)\. kerület$/',$church['varos'],$match);
			if($match) {
				$churches[$c]['varos'] = [ $church['varos'], 'Budapest '.array_search($match[1], $romai).'. kerület' ];
			}
		}
		
		if(!$solr->truncateCollection('churches'))
			return false;
		if(!$solr->updateCollection('churches',json_encode($churches))) 
			return false;
		
		
		return true;
		
	}
	

	
}
