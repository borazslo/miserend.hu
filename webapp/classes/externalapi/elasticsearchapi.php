<?php

namespace ExternalApi;

class ElasticsearchApi extends \ExternalApi\ExternalApi {

    public $name = 'elasticsearch';    
	public $format = 'json';
	public $apiUrl = "http://elasticsearch:9200/";

	public $testQuery = '_cluster/health/churches?pretty';
	public $cache = false; // Az Elasticsearch-nek meg van a saját cache-je. Arra hagyatkozunk
	
	public $q; // Ez a solr keresőben a query, nem pedig az API-ban a query
			
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
	
	function isexistsIndex($name) {
		$this->curl_setopt(CURLOPT_CUSTOMREQUEST ,"GET");		
		$this->buildQuery("_cat/indices/".$name."?format=json");		
		$this->run();	
		
		if($this->responseCode == 404) {
			return false;
		}

		if($this->responseCode != 200) {
			throw new \Exception("Could not get indices!\n".$this->error);
		}
		if($this->jsonData == []) {
			return false;
		}
		return true;
	}

	function checkIndex($name) {
		$this->curl_setopt(CURLOPT_CUSTOMREQUEST ,"GET");		
		$this->buildQuery("_cat/indices/".$name."?format=json");		
		$this->run();	
		if($this->responseCode != 200) {
			throw new \Exception("Could not get indices!\n".$this->error);
		}
		if($this->jsonData == []) {
			throw new \Exception("No indices found!\n".$this->error);
		}
		if(count($this->jsonData) != 1) {
			throw new \Exception("There should be exactly one index found!\n".$this->error);
		}
		if($this->jsonData[0]->status != 'open') {
			throw new \Exception("Index is not open!\n".$this->error);
		}
		if($this->jsonData[0]->health == 'red') {
			throw new \Exception("Index health is red!\n".$this->error);
		}
		return $this->jsonData[0];


	}

	function putIndex($name, $data) {	
		$this->curl_setopt(CURLOPT_CUSTOMREQUEST ,"PUT");		
		$this->buildQuery($name, json_encode($data));
		$this->run();

		if($this->responseCode != 200)
			return false;
		if(!isset($this->jsonData->acknowledged) OR $this->jsonData->acknowledged != 1)
			return false;
		
		return true;
	}

	function truncateIndex($name) {

		$this->curl_setopt(CURLOPT_CUSTOMREQUEST ,"POST");		
		$this->buildQuery($name."/_delete_by_query", json_encode(['query'=>['match_all'=>[]]]));
		if($this->responseCode != 200)
			throw new \Exception("Could not truncate index!\n".$this->error);
		
		return true;
	}

	function deleteIndex($name) {
		
		$this->curl_setopt(CURLOPT_CUSTOMREQUEST ,"DELETE");		
		$this->buildQuery($name);
		$this->run();

		if($this->responseCode != 200)
			return false;
		if(!isset($this->jsonData->acknowledged) OR $this->jsonData->acknowledged != 1)
			return false;
		
		return true;
	}
	
	function putBulk($data) {	
		$this->curl_setopt(CURLOPT_CUSTOMREQUEST ,"PUT");		

		if(is_array($data)) {
			$bulkData = [];
			foreach($data as $item) {
				if(is_array($item))
					$bulkData[] = json_encode($item);
				else
					$bulkData[] = $item;				
			}							
			$data = implode("\n", $bulkData)."\n";
		}

		$this->buildQuery('_bulk', $data);
		$this->run();

		if($this->responseCode != 200)
			return false;

		if(isset($this->jsonData->errors) AND $this->jsonData->errors != "")
			return false;
		
		return true;
	}
	
	function random($params = []) {
		// Defaults
		$default_params = [
			'size'=>10
		];		
		$data = $default_params;		

		$data = [
			"query" => [
				"function_score" => [
					"query" => ["match_all" => new \stdClass()],
					"boost" => "5",
					"random_score" => new \stdClass(),
					"boost_mode" => "multiply"
				]
			]
		];

		foreach($params as $key => $value) {
			$data[$key] = $value;
		}

		$this->curl_setopt(CURLOPT_CUSTOMREQUEST ,"GET");		
		$this->buildQuery('churches/_search', json_encode($data));		
		$this->run();

		if($this->responseCode != 200) {
			throw new \Exception("Could not search churches!\n".$this->error);
		}

		return $this->jsonData->hits;
	}

	function search($keyword, $params = [] ) {
		// Defaults
		$default_params = [
			'from'=>0,
			'size'=>10
		];		
		$data = $default_params;
		foreach($params as $key => $value) {
			$data[$key] = $value;
		}
		
		// Build the query
		$data = [
				'from' => $data['from'],
				'size' => $data['size'],
				'query' => ['bool' => ['should' => []]]];

		if (preg_match('/\bid:(\d+)\b/i', $keyword, $matches)) {
			$id = $matches[1];
			$keyword = trim(str_replace($matches[0], '', $keyword));
			$data['query'] = [
				'bool' => [
					'must' => [
						['term' => ['id' => $id]]
					],
					'should' => [
						[
							'multi_match' => [
								'query' => $keyword,
								'fields' => ['id^100','names^4', 'alternative_names^2', 'varos^40']
							]
						]
					]
				]
			];
		} else {
			/*
			$data['query'] = [
				'multi_match' => [
					'query' => $keyword,
					'fields' => ['id^100','names^4', 'alternative_names^2', 'varos^2']
				]
			];
			*/

			$data['query']['bool']['should'][] = [
				"match" => [
					"varos" => [
						"query" => $keyword,
						"operator" => "or",
						"boost" => 64
					]
				]
			];

			$data['query']['bool']['should'][] = ['term'=>['names'=>[ 'value' => $keyword, 'boost'=>32 ]]];			
			$data['query']['bool']['should'][] = ['term'=>['varos'=>[ 'value' => $keyword, 'boost'=>18 ]]];
			$data['query']['bool']['should'][] = ['term'=>['alternative_names'=>[ 'value' => $keyword, 'boost'=>7 ]]];
			$data['query']['bool']['should'][] = ['match'=>['names'=>[ 'query' => $keyword, 'boost'=>30 ]]];
			$data['query']['bool']['should'][] = ['match'=>['varos'=>[ 'query' => $keyword, 'boost'=>15 ]]];
			$data['query']['bool']['should'][] = ['match'=>['alternative_names'=>[ 'query' => $keyword, 'boost'=>5 ]]];
			$data['query']['bool']['should'][] = ['wildcard'=>['names'=>[ 'value' => '*'.$keyword.'*', 'boost'=>28 ]]];			
			$data['query']['bool']['should'][] = ['wildcard'=>['varos'=>[ 'value' => '*'.$keyword.'*', 'boost'=>12 ]]];
			$data['query']['bool']['should'][] = ['wildcard'=>['alternative_names'=>[ 'value' => '*'.$keyword.'*', 'boost'=>4 ]]];
								
		}

		

		$this->curl_setopt(CURLOPT_CUSTOMREQUEST ,"GET");		
		$this->buildQuery('churches/_search', json_encode($data));		
		$this->run();

		if($this->responseCode != 200) {
			throw new \Exception("Could not search churches!\n".print_r($this->jsonData->error,true));
		}

		return $this->jsonData->hits;
		
		
	}
	
	// Rendszeresen feltöltjük a keresőbe az adatbázisunkat, mert az jó.
	static function updateChurches() {

		
		$elastic = new \ExternalApi\ElasticsearchApi();
		
		$elastic->deleteIndex('churches'); // Először töröljük az indexet, ha létezik. Ez nem baj, mert a putIndex úgyis létrehozza újra.
		// Megnézzük, hogy létezik-e már a churches index és ha nem, akkor létrehozzuk
		if(!$elastic->isexistsIndex('churches')) {
						
			$filePath = '../docker/elasticsearch/mappings/church.json';
			if (!file_exists($filePath)) {
				throw new \Exception("File not found: " . $filePath);
			}
			$data = file_get_contents($filePath);
			if (!$elastic->putIndex('churches', json_decode($data, true))) {				
				throw new \Exception("Failed to create index: churches");
			}
			
		}
		
		// Előkészítjük feltöltsére az adatokat
		$churches = \Eloquent\Church::where('ok', 'i')->limit(200000)->get()->map->toElasticArray()->toArray();
		
		// Truncate the index
		$elastic->truncateIndex('churches');

		// Feltöltjük az adatokat az indexbe. Ezzel új verzióval felülírja a régieket. De mondjuk nem üríti ki a régit.
		$bulkData = [];
		foreach($churches as $church) {
			$bulkData[] = json_encode([
				'index' => [
					'_index' => 'churches',
					'_id' => $church['id']
				]
			]);
			$bulkData[] = json_encode($church);
		}
		
		if(!$elastic->putBulk($bulkData)) {
			$errors = [];
			foreach($elastic->jsonData->items as $item ) {
				if(isset($item->index->error)) {					
					$errors[] = $item->index->error->type . ': ' . $item->index->error->reason . "\n";
				}
			}

			throw new \Exception("Could not update churches!\n" . implode("\n", $errors));
		}
		
	}
	
	
}
