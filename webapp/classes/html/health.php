<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Health extends Html {

    public function __construct() {
        parent::__construct();
        $this->setTitle('Miserend.hu állapotáról');
		
		//General informations
		global $config;
		
		$this->infos = [
			['server', $_SERVER['SERVER_SOFTWARE']],
			['php verzió', phpversion()],
			['php extensions', implode(', ',get_loaded_extensions())],
			['environment', $config['env'] ],
			['debug', $config['debug']],
			['error_reporting', $config['error_reporting'] ],
			['mail/debug', $config['mail']['debug'] ]
		];
		
		
		$results = [];
		for($i=1;$i<=4;$i++) {		
			$tables = [];
			$sqlite = new \Api\Sqlite();
			$sqlite->version = $i;			
			
			if(!$tables = $sqlite->checkSqliteFile()) {
				$alert = 'danger';
			} else 
				$alert = 'success';
				
			if(file_exists($sqlite->sqliteFilePath)) {
				$filemtime = date ("Y-m-d H:i:s.", filemtime($sqlite->sqliteFilePath));
			} else {
				$alert = 'danger';
				$filemtime = false;
			}
								
			$tmp = " <a class=\"alert-".$alert."\" href=\"$sqlite->folder$sqlite->sqliteFileName\">".$sqlite->sqliteFileName."</a> ";
			if($filemtime) $tmp .= "(".$filemtime.") ";
			
			if($alert == "success") {	
				foreach($tables as $name => $count) {
					$tables[$name] = $name.": ".$count;
				}
				$tmp .= ": ".implode(', ',$tables);
			}
			
			$results[] = $tmp;
		}
		$this->infos[] = ["sqlite files",implode("<br/>",$results)];
		$results = [] ;

		
		// Health of CronJobs
		$this->cronjobs = \Eloquent\Cron::orderBy('deadline_at','DESC')->get()->toArray();
		
		// Health of ExternalApis
		$apisToTest = ['liturgiatvapi','kozossegekapi','mapquestapi','openinghapi','openstreetmapapi','overpassapi'];		
		foreach($apisToTest as $apiToTest) {
			$this->externalapis[$apiToTest] = ['name' => $apiToTest, 'stat' => 0];
			
			try {
			
				$className = "\ExternalApi\\".$apiToTest;
				
				if(!class_exists($className) )				
					throw new \Exception('Hiányzó osztály!');
				
				$externalapi = new $className();
				
				if(!method_exists($externalapi,'test')) 
					throw new \Exception('Hiányzik a tesztelő függvény!');
				
				
				$testresult = $externalapi->test();
				$this->externalapis[$apiToTest]['apiUrl'] = $externalapi->apiUrl ;
				$this->externalapis[$apiToTest]['testQuery'] = $externalapi->rawQuery;
				
				if($testresult !== true) 
					throw new \Exception($testresult);
				
				$this->externalapis[$apiToTest]['testresult'] = 'OK';
			}
			catch (\Exception $e) {
				$this->externalapis[$apiToTest]['testresult'] = $e->getMessage();
			}
			
		}
		
		$results = [];
        $results = DB::table('stats_externalapi')
			->select('name',DB::raw('SUM(count) count'))
			->where('date','>',date('Y-m-d',strtotime('-1 month')))
			->groupBy('name')->orderBy('date','asc')
			->get();        
		foreach($results as $result) {			
			if(array_key_exists($result->name."api", $this->externalapis))
				 $this->externalapis[$result->name."api"]['stat'] = $result->count; 							 								 
		}
       		
		return;
			
    }
}