<?php

namespace ExternalApi;

https://github.com/szentjozsefhackathon/napi-lelki-batyu

class NapilelkibatyuApi extends \ExternalApi\ExternalApi {

    public $name = 'napilelkibatyu';
    public $apiUrl = "https://szentjozsefhackathon.github.io/napi-lelki-batyu/" ;    
	public $format = 'json';
	public $cache = "1 week"; //false or any time in strtotime() format
	public $testQuery = '';
    
    function buildQuery() {
        global $config;
        $this->rawQuery = $this->query;        
    }
	
	function liturgicalAlert($date = false) {
		if($date == false)
			$date = date('Y-m-d');
				
		$nextDay = date('Y-m-d', strtotime($date . ' +1 day'));

		$this->query = date('Y').".json";						
		$this->run();
		
		if(!isset($this->jsonData->$date)) {
			throw new \Exception("There is no data for date '$date' from napilelkibatyu.");
		}

		$isDateSunday = date('N', strtotime($date)) == 7;
		$levelOfDate = $this->jsonData->$date->celebration[0]->level;
		// use the last celebration entry instead of the first (index 0)
		$celebrations = $this->jsonData->$date->celebration;
		if (is_array($celebrations) && count($celebrations) > 0) {
			$lastCelebration = end($celebrations);
			$nameOfDate = isset($lastCelebration->name) ? $lastCelebration->name : '';
		} else {
			$nameOfDate = '';
		}
		
		$isNextDaySunday = date('N', strtotime($nextDay)) == 7;
		$levelOfNextDay = $this->jsonData->$nextDay->celebration[0]->level;
		$nameOfNextDay = $this->jsonData->$nextDay->celebration[0]->name;

		//echo "Date: $date, Level: $levelOfDate, Is Sunday: " . ($isDateSunday ? 'Yes' : 'No') . ", Next Day: $nextDay, Level: $levelOfNextDay, Is Next Day Sunday: " . ($isNextDaySunday ? 'Yes' : 'No'). "<br/>\n";

		if($levelOfDate <= 4 or $levelOfNextDay <= 4) {
			global $twig;
			global $_honapok;
			return $twig->render('alert_liturgicalday.html', 
				array(
					'honapok' => $_honapok,
					'date' => [
						'date' => $date,
						'name' => $nameOfDate,
						'level' => $levelOfDate,
						'isSunday' => $isDateSunday
					],
					'nextDay' => [
						'date' => $nextDay,
						'name' => $nameOfNextDay,
						'level' => $levelOfNextDay,
						'isSunday' => $isNextDaySunday
					],
				));
	}
		return false;
	}
}

