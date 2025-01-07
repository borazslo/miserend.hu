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
								'fields' => ['id^100','nev^4', 'ismertnev^2', 'varos^40']
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
					'fields' => ['id^100','nev^4', 'ismertnev^2', 'varos^2']
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

			$data['query']['bool']['should'][] = ['term'=>['nev'=>[ 'value' => $keyword, 'boost'=>32 ]]];			
			$data['query']['bool']['should'][] = ['term'=>['varos'=>[ 'value' => $keyword, 'boost'=>18 ]]];
			$data['query']['bool']['should'][] = ['term'=>['ismertnev'=>[ 'value' => $keyword, 'boost'=>7 ]]];
			$data['query']['bool']['should'][] = ['match'=>['nev'=>[ 'query' => $keyword, 'boost'=>30 ]]];
			$data['query']['bool']['should'][] = ['match'=>['varos'=>[ 'query' => $keyword, 'boost'=>15 ]]];
			$data['query']['bool']['should'][] = ['match'=>['ismertnev'=>[ 'query' => $keyword, 'boost'=>5 ]]];
			$data['query']['bool']['should'][] = ['wildcard'=>['nev'=>[ 'value' => '*'.$keyword.'*', 'boost'=>28 ]]];			
			$data['query']['bool']['should'][] = ['wildcard'=>['varos'=>[ 'value' => '*'.$keyword.'*', 'boost'=>12 ]]];
			$data['query']['bool']['should'][] = ['wildcard'=>['ismertnev'=>[ 'value' => '*'.$keyword.'*', 'boost'=>4 ]]];
								
		}

		

		$this->curl_setopt(CURLOPT_CUSTOMREQUEST ,"GET");		
		$this->buildQuery('churches/_search', json_encode($data));		
		$this->run();

		if($this->responseCode != 200) {
			throw new \Exception("Could not search churches!\n".$this->error);
		}

		return $this->jsonData->hits;
		
		
	}
	
	// Rendszeresen feltöltjük a keresőbe az adatbázisunkat, mert az jó.
	static function updateChurches() {

		
		$elastic = new \ExternalApi\ElasticsearchApi();
		
		// Megnézzük, hogy létezik-e már a churches index és ha nem, akkor létrehozzuk
		if(!$elastic->isexistsIndex('churches')) {
						
			$filePath = '../docker/elasticsearch/mappings/church.json';
			if (!file_exists($filePath)) {
				throw new \Exception("File not found: " . $filePath);
			}
			$data = file_get_contents($filePath);
			if (!$elastic->putIndex('churches', json_decode($data, true))) {
				printr($elastic);
				throw new \Exception("Failed to create index: churches");
			}
			
		}

		// Előkészítjük feltöltsére az adatokat
		$churches = \Eloquent\Church::where('ok', 'i')->limit(20000)->get()->map->toAPIArray()->toArray();

		// Kiegészítjük Budapest kerületekkel
		$romai = ['0','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII','XIII','XIV','XV','XVI','XVII','XVIII','XIX','XX','XXI','XXII','XXIII'];
		foreach($churches as $c => $church) {
			preg_match('/^Budapest (.*?)\. kerület$/',$church['varos'],$match);
			if($match) {
				$churches[$c]['varos'] = [ $church['varos'], 'Budapest '.array_search($match[1], $romai).'. kerület', 'Budapest' ];
			}
		}

		foreach ($churches as $index => $church) {
			unset($churches[$index]['adoraciok']);
		}

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
		if(!$elastic->putBulk($bulkData))
			throw new \Exception("Could not update churches!\n".$elastic->error);
		



		echo "ok";
		//printr($elastic);
		exit;

		$query = json_encode([
			"query" => [
				"match_all" => (object)[]
			],
			"size" => 10
		]);

		$query = '{
				"query": {
					"function_score": {
					"query": {
						"multi_match": {
						"query": "jezsu",
						"fields": ["nev^4", "ismertnev^4", "varos^4"]
						}
					},
					"boost_mode": "sum"
					}
				}
				}';
		$query = '{
		
		

		}';


		//printr($elastic->generateSearchQuery('Hódmező*'));
		$query = json_encode($elastic->generateSearchQuery('Erzsébet'));

		$elastic->buildQuery("churches/_search?format=json", $query);
		$elastic->run();
		if($elastic->responseCode != 200) {
			throw new \Exception("Could not search churches!\n".$elastic->error);
		}	
		printr($elastic->jsonData);

		exit;

		
		


		
		/*

		if(!in_array('churches',$collections)) {
			if(!$solr->createCollection('churches')) {
				throw new \Exception("Could not create collection!\n".$solr->error);
			}
		}
		
		*/
		

		if($elastic->responseCode != 200) {
			throw new \Exception("Could not update churches!\n".$elastic->error);
		}		
		//printr($elastic->jsonData);
		
		$query = [
			"query" => [
				"match_all" => (object)[]
			],
			"size" => 10
		];
		$elastic->buildQuery("churches/_search?format=json", json_encode($query));
		$elastic->run();
		if($elastic->responseCode != 200) {
			throw new \Exception("Could not search churches!\n".$elastic->error);
		}	
		printr($elastic->jsonData);
		exit;

		echo json_encode($bulkData);


		
		/*
		foreach($churches as $c => $church) {			
			$fieldsToConvert = ['nev','ismertnev','varos','cim','megkozelites','plebania','megjegyzes','misemegj'];
			foreach($fieldsToConvert as $field) {				
				$fieldValues = $churches[$c][$field];
				if(!is_array($fieldValues)) {
					$fieldValues = [$fieldValues];
				}
				foreach($fieldValues as $fieldValue) {
					$fieldValues[] = $solr->convertHungarianChars($fieldValue);
				}				
				$churches[$c][$field] = $fieldValues;
			}
		}
		*/

		

		if(!$solr->truncateCollection('churches'))
			return false;
		if(!$solr->updateCollection('churches',json_encode($churches))) 
			return false;
		
		
		return true;
		
	}

	static function convertHungarianChars($input) {
		$transliterationMap = array(
			'Ő' => 'O', 'ő' => 'o', 'Ű' => 'U', 'ű' => 'u',
			'Á' => 'A', 'á' => 'a', 'É' => 'E', 'é' => 'e',
			'Í' => 'I', 'í' => 'i', 'Ó' => 'O', 'ó' => 'o',
			'Ö' => 'O', 'ö' => 'o', 'Ő' => 'O', 'ő' => 'o',
			'Ú' => 'U', 'ú' => 'u', 'Ü' => 'U', 'ü' => 'u',
			'Ñ' => 'N', 'ñ' => 'n', 'Ç' => 'C', 'ç' => 'c',
			'À' => 'A', 'à' => 'a', 'È' => 'E', 'è' => 'e',
			'Ì' => 'I', 'ì' => 'i', 'Ò' => 'O', 'ò' => 'o',
			'Ù' => 'U', 'ù' => 'u', 'ÿ' => 'y'
		);
	
		return strtr($input, $transliterationMap);
	}
	




	static function generateSearchQuery($term) {
		// Define the boosts for each case
		$boosts = [
			'nev' => [
				'match' => 32,
				'wildcard_prefix' => 30,
				'wildcard_suffix' => 28
			],
			'ismertnev' => [
				'match' => 16,
				'wildcard_prefix' => 8,
				'wildcard_suffix' => 6
			],
			'varos' => [
				'match' => 4,
				'wildcard_prefix' => 2,
				'wildcard_suffix' => 1
			]
		];
	
		// Build the query array
		$queryArray = [
			'query' => [
						'bool' => [
							'should' => []
						]
					]
		];
	
		// Loop through the fields and create the corresponding queries with boosts
		foreach ($boosts as $field => $values) {
			// Match query for the exact term
			$queryArray['query']['bool']['should'][] = [
				'match' => [
					$field => [
						'query' => $term,
						'boost' => $values['match']
					]
				]
			];
	
			// Wildcard query for "term*" (prefix match)
			$queryArray['query']['bool']['should'][] = [
				'wildcard' => [
					$field => [
						'value' => $term . '*',
						'boost' => $values['wildcard_prefix']
					]
				]
			];
	
			// Wildcard query for "*term" (suffix match)
			$queryArray['query']['bool']['should'][] = [
				'wildcard' => [
					$field => [
						'value' => '*' . $term,
						'boost' => $values['wildcard_suffix']
					]
				]
			];
		}
	
		return $queryArray;
	}
	
	static function createSearchQuery($term) {
		return [
			"query" => [
				"bool" => [
					"should" => [
						[
							"match" => [
								"nev" => [
									"query" => $term,
									"boost" => 32
								]
							]
						],
						[
							"wildcard" => [
								"nev" => [
									"value" => $term . "*",
									"boost" => 30
								]
							]
						],
						[
							"wildcard" => [
								"nev" => [
									"value" => "*" . $term,
									"boost" => 28
								]
							]
						],
						[
							"match" => [
								"ismertnev" => [
									"query" => $term,
									"boost" => 16
								]
							]
						],
						[
							"wildcard" => [
								"ismertnev" => [
									"value" => $term . "*",
									"boost" => 8
								]
							]
						],
						[
							"wildcard" => [
								"ismertnev" => [
									"value" => "*" . $term,
									"boost" => 6
								]
							]
						],
						[
							"match" => [
								"varos" => [
									"query" => $term,
									"boost" => 4
								]
							]
						],
						[
							"wildcard" => [
								"varos" => [
									"value" => $term . "*",
									"boost" => 2
								]
							]
						],
						[
							"wildcard" => [
								"varos" => [
									"value" => "*" . $term,
									"boost" => 1
								]
							]
						]
					]
				]
			]
		];
	}
	
	

	
}
