<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class NearBy extends Api {

    public $format = 'json'; //or text
	public $requiredFields = array('lat','lon');
        
    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'nearby' is not available under v4.");
        }
    }

    public function validateInput() {
		if (!is_numeric($this->input['lat']) OR $this->input['lat'] > 90 OR $this->input['lat'] < -90 ) {
            throw new \Exception("JSON input 'lat' should be float between -90 and 90.");
        }
		if (!is_numeric($this->input['lon']) OR $this->input['lon'] > 90 OR $this->input['lon'] < -180 ) {
            throw new \Exception("JSON input 'lon' should be float between -180 and 90.");
        }		
	}

    public function run() {
        parent::run();
		
        $this->getInputJson();
		
		$churches = \Eloquent\Church::select()
				->addSelect(DB::raw("ST_distance_sphere( ST_GeomFromText('POINT ( ".$this->input['lat']." ".$this->input['lon']." )', 4326), ST_GeomFromText(CONCAT('POINT ( ',lat,' ', lon, ')'), 4326) ) as distance"))
                ->where('ok','i')
				->where('lat','<>','')
                ->orderBy('distance', 'ASC')
				->limit(10)
                ->get();
				
//		printr($churches);		
		foreach($churches as $church) {
			$masses = searchMasses(['templom'=>$church->id, 'mikor' => date('Y-m-d')] );
			$misek = [];
			
			if(isset($masses['churches'][$church->id])) {
				foreach($masses['churches'][$church->id]['masses'] as $key => $mise) {
					$misek[$key]['idopont'] = date('Y-m-d')." ".$mise['ido'];
					$info = trim($mise['milyen']." ".$mise['megjegyzes']." ".$mise['nyelv']);
					if($info != '') $misek[$key]['informacio'] = $info;
			}	
				
			}
			$this->return['templomok'][] = [
				'id' => $church->id,
				'nev' => $church->nev,
				'ismertnev' => $church->ismertnev,
				'varos' => $church->varos,
				'tavolsag' => (int) $church->distance,
				'misek' => $misek,
				'lat' => $church->lat,
				'lon' => $church->lon
			];
		}
        //$this->return['lat'] = $this->input['lat'];

		$hasNearbyChurch = false;
		foreach ($this->return['templomok'] as $templom) {
			if ($templom['tavolsag'] < 50) {
				$hasNearbyChurch = true;
				break;
			}
		}

		$hasMassRightNow = false;
		foreach ($this->return['templomok'] as $templom) {
			foreach ($templom['misek'] as $mise) {
				if (strtotime($mise['idopont']) < time() + 80 * 60 && strtotime($mise['idopont']) > time() - 15 * 60) {
					$hasMassRightNow = true;
					break 2;
				}
			}
		}

		$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? str_replace(',', ';', $_SERVER['HTTP_USER_AGENT']) : 'Unknown';

		$logFile = '../nearby.log';
		if (file_exists($logFile)) {			
			file_put_contents($logFile, date('Y-m-d H:i:s') . "," . $this->input['lat'] . "," . $this->input['lon'] . "," . ($hasNearbyChurch ? 'true' : 'false') . "," . ($hasMassRightNow ? 'true' : 'false') . "," . $userAgent . PHP_EOL, FILE_APPEND);
		}
				
        return;
    }
    
	public static function cleanOldLogs() {
		$logFile = '../nearby.log';
		if (!file_exists($logFile)) {
			return;
		}

		$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		if (empty($lines)) {
			return;
		}

		$oneMonthAgo = strtotime('-1 month');
		$firstLineTimestamp = strtotime(explode(',', $lines[0])[0]);

		if ($firstLineTimestamp < $oneMonthAgo) {
			$newLines = array_filter($lines, function($line) use ($oneMonthAgo) {
				$timestamp = strtotime(explode(',', $line)[0]);
				return $timestamp >= $oneMonthAgo;
			});

			file_put_contents($logFile, implode(PHP_EOL, $newLines) . PHP_EOL);
		}
	}
  
	static function getLogFileInfo() {
		$logFile = '../nearby.log';
		if (!file_exists($logFile)) {
			return [
				'line_count' => 'N/A',
				'file_size' => 'file does not exist'
			];
		}

		$lineCount = count(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$fileSize = filesize($logFile);

		return [
			'line_count' => $lineCount,
			'file_size' => $fileSize
		];
	}
}
