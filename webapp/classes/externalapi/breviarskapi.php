<?php

namespace ExternalApi;

class breviarskApi extends \ExternalApi\ExternalApi {

    public $name = 'breviarsk';
    public $apiUrl = "https://breviar.kbs.sk/cgi-bin/l.cgi?qt=pxml&" ;    
	public $format = 'xml';
	public $cache = "1 day"; //false or any time in strtotime() format
	public $testQuery = 'd=25&m=1&r=1985j=hu';
    
    function buildQuery() {
        global $config;
        $this->rawQuery = $this->query;        
    }
	
	function liturgicalAlert($date = false) {
		if($date == false)
			$date = date('Y-m-d');
		
		$this->query = "d=" . date('d',strtotime($date)) . "&m=" . date('m',strtotime($date)) . "&r=" . date('Y',strtotime($date)) . "&j=hu";						
		$this->run();
		
		if(!isset($this->xmlData->CalendarDay)) {
			throw new \Exception("There is no 'CalendarDay' data from breviar.kbs.sk");
		}
		$celebration = $this->xmlData->CalendarDay->Celebration;
				
		if ($celebration->LiturgicalCelebrationLevel <= 2 || ($celebration->LiturgicalCelebrationLevel <= 3 && date('N', strtotime($date)) != 7)) {

            $text = "Ma van <strong>" . $celebration->LiturgicalCelebrationName . "</strong>";
            if (preg_match("/ünnep$/i", $celebration->LiturgicalCelebrationType))
                $text .= " " . $celebration->LiturgicalCelebrationType . "e";
			
			// A parancsolt ünnepek
			$parancsoltunnep = false;
			$specialDates = [
				'01-01',
				'01-06',
				'08-15',
				'11-01',
				'12-25'
			];
			if (in_array(date('m-d', strtotime($date)), $specialDates)) {
				$parancsoltunnep = true;
			}

			global $twig;
			return $twig->render('alert_liturgicalday.html', 
				array(
					'text' => $text,
					'name' => $celebration->LiturgicalCelebrationName,
					'type' => $celebration->LiturgicalCelebrationType, 
					'parancsoltunnep' => $parancsoltunnep
				));
            
        }
		return false;
	}
}

