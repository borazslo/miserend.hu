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

	
	// Rendszeresen feltöltjük a keresőbe az adatbázisunkat, mert az jó.
	static function updateChurches(array $tids = []) {

		
		$elastic = new \ExternalApi\ElasticsearchApi();
		
		$elastic->deleteIndex('churches'); // Először töröljük az indexet, ha létezik. Ez nem baj, mert a putIndex úgyis létrehozza újra.
				
		
		if(!$elastic->isexistsIndex('churches')) {

			$data = file_get_contents(__DIR__ . '/../../fajlok/elasticsearch/mappings/church.json');
			$elastic->curl_setopt(CURLOPT_TIMEOUT, 3600);
			if (!$elastic->putIndex('churches', json_decode($data, true))) {
				throw new \Exception(
						"Failed to create index: churches\n" .
						"Response code: " . $elastic->responseCode . "\n" .
						"Error: " . print_r($elastic->error, true) . "\n" .
						"Response: " . print_r($elastic->jsonData, true) . "\n" .
						"Request: " . print_r($elastic->requestData, true) . "\n" .
						"\$elastic: " . print_r($elastic, true)
				);
			}

		}	
		
		// Előkészítjük feltöltsére az adatokat
		$churches = \Eloquent\Church::where('ok', 'i');
		if(!empty($tids)) {

			$churches = $churches->whereIn('id', $tids);
		}
		$churches = $churches->limit(200000)->get()->map->toElasticArray()->toArray();
		
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

	       // --- Output Elasticsearch stats and a sample doc ---
	       $stats = @file_get_contents('http://elasticsearch:9200/churches/_stats');
	       $statsData = $stats ? json_decode($stats, true) : null;
	       $count = $statsData['_all']['primaries']['docs']['count'] ?? 'N/A';
	       $size = $statsData['_all']['primaries']['store']['size_in_bytes'] ?? 'N/A';
	       $shards = $statsData['_shards']['successful'] ?? 'N/A';

	       echo "<blockquote>Index stats:<br>";
	       echo "Documents: <tt>" . htmlspecialchars($count) . "</tt><br>";
	       echo "Store size (bytes): <tt>" . htmlspecialchars($size) . "</tt><br>";
	       echo "Successful shards: <tt>" . htmlspecialchars($shards) . "</tt><br>";
	       echo "</blockquote>\n";

	       // Get a sample document
	       $sample = @file_get_contents('http://elasticsearch:9200/churches/_search?size=1');
	       $sampleData = $sample ? json_decode($sample, true) : null;
	       $doc = $sampleData['hits']['hits'][0]['_source'] ?? null;
	       if ($doc) {
		   echo "<blockquote>Sample document:<br><tt>" . htmlspecialchars(json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</tt></blockquote>\n";
	       } else {
		   echo "<blockquote>No sample document found.</blockquote>\n";
	       }
       }
	
	/*
	 * Frissíti az összes elasticsearch mise indexet az adatbázisból
	 * Ehhez legenerálja az összes miseidőpontot is
	 * !! TODO: Iszonyú overkill mindig mindent frissíteni. Optimalizálni kellene!!
	 */
	static function updateMasses($years = [], $tids = []) {
		$startTime = time();
		set_time_limit(3000); // Hosszabb idő kellhet a frissítéshez

		if (empty($years)) {
			$years = [date('Y') - 1, date('Y'), date('Y') + 1];
		}
		if( empty($tids)) {
			$tids = \Eloquent\Church::where('ok', 'i')->limit(8000)->pluck('id')->toArray();
		} 

		$chunksize = 100;
		if (is_array($tids) && count($tids) > $chunksize) {
			foreach (array_chunk($tids,  $chunksize) as $chunk) {
				static::updateMasses($years, $chunk);
			}
			return;
		}

		$elastic = new \ExternalApi\ElasticsearchApi();		
		// Delete existing masses for the given church IDs		
		$elastic->curl_setopt(CURLOPT_CUSTOMREQUEST, "POST");
		$elastic->buildQuery('mass_index/_delete_by_query', json_encode([
			"conflicts" => "proceed",
			"query" => [
				"terms" => ["church_id" => $tids]
			]
		]));
		$elastic->run();
		if(isset($elastic->error)) {			
			throw new \Exception("Could not delete existing masses!\n" . $elastic->error);
		}
		
		$churchTimezones = [];		
		$churches = \Eloquent\Church::whereIn('id', $tids)->get()->keyBy('id');
		foreach($churches as $church_id => $church) {
			$churches[$church_id] = $church->toElasticArray();
		}
		echo  "Talált templomok száma: " . count($churches)."<br>\n";

		$allMasses = \Eloquent\CalMass::whereIn('church_id', $tids)->get()->all();
		foreach ($churches as $id => $church) {
			$churchTimezones[$id] = $church->time_zone ?? 'Europe/Budapest';			
		}

		$debug = [];
		$debug[] = "Talált misék száma: " . count($allMasses);
		
		echo "Talált misék száma: " . count($allMasses)."<br>\n";
		
		$massPeriods = \Eloquent\CalMass::generateMassPeriodInstancesForYears($allMasses, $churchTimezones, $years);
		echo "Egyedi periódusokkal felpumpálva már ". count($massPeriods). " a szám.<br>\n";
		
		$countAllMasses = 0;
		foreach($massPeriods as $k => $mass) {
			$bulkInsert = [];

            $rrule = new \SimpleRRule($mass['rrule']);
            $occs = $rrule->getOccurrences();
			//printr($occs); exit;
			foreach($occs as $occ) {
				$bulkInsert[] = [
					'index' => [
						'_index' => 'mass_index',
						'_id' => uniqid()
					]
				];
				$bulkInsert[] = [
					'church_id' => $mass['church_id'],
					'mass_id' => $mass['mass_id'],
					'start_date' => $occ->copy()->setTimezone(new \DateTimeZone('UTC'))->format(\DateTime::ATOM),
					'start_minutes' => $occ->copy()->setTimezone(new \DateTimeZone('UTC'))->hour * 60 + $occ->copy()->setTimezone(new \DateTimeZone('UTC'))->minute,
					'title' => $mass['title'],
					'types' => $mass['types'],
					'rite' => $mass['rite'],
					'duration_minutes' => $mass['duration_minutes'],
					'lang' => $mass['lang'],
					'comment' => $mass['comment'],
					'church' => $churches[$mass['church_id']]
				];
				
			}			
			$countAllMasses += count($occs);
			if (!empty($bulkInsert)) {
				$elasticResult = $elastic->putBulk($bulkInsert);
				if (!$elasticResult) {
					
					if(isset($elastic->jsonData->errors)) {
						$elastic->error = '';
						$errItems = [];
						foreach($elastic->jsonData->items as $item ) {
							if(isset($item->index->error)) {					
								$errItems[] = $item->index->error->type . ': ' . $item->index->error->reason . "\n";
							}
						}
						$elastic->error .= "\n" . implode("\n", $errItems);
						
					}

					throw new \Exception("Could not insert mass data for church ID ".$mass['church_id']."!\n".$elastic->error);
				}
			}
		}

		echo "Nos hát szépen minden napra szét bontva így lett nekünk már ".$countAllMasses." misénk.<br/>";

		echo "Elkészült a frissítés " . (time() - $startTime) . " másodperc alatt azaz ".round((time() - $startTime)/60,2)." perc alatt.<br>\n";
		return $debug;
	}
	
}
