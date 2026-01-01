<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class NearBy extends Api {

	public $title = 'Közeli templomok és misék';
    public $format = 'json'; //or text	
    public $requiredVersion = ['>=',4]; // API v4-től érhető el

	public $fields = [
		'lat' => [
			'required' => true, 
			'validation' => [
				'float'=> [ 
					'minimum' => -90, 
					'maximum' => 90 
				]
			], 
			'description' => 'a szélességi fok',
			'example' => 47.497913
		],
		'lon' => [
			'required' => true, 
			'validation' => [
				'float' => [ 
					'minimum' => -180, 
					'maximum' => 180 
				]
			], 
			'description' => 'a hosszúsági fok',
			'example' => 19.040236
		],
		'limit' => [
			'validation' => [
				'integer' => [ 
					'minimum' => 1, 
					'maximum' => 100 
				]
			],
			'description' =>  'az egyszerre megmutantandó válaszok száma',
			'default' => 10
		],
		'whenMass' => [
			'validation' => [
				'enum' => ['today', 'tomorrow', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
				['date' => []]]				
			],
			'description' =>  'csak az adott napi misék megjelenítése',
			'default' => false
		],
		'response_length' => [
			'validation' => [
				'enum' => ['minimal', 'medium','full']
			],
			'description' =>  'az egy templomra vonatkozó válaszok részletessége',
			'default' => 'minimal'
		]
	];
		
	
    public function docs() {

        $docs = [];
         
        $docs['description'] = <<<HTML
            <p>Adott koordinátákhoz legközelebbi templomok listáját adja vissza az adott napi misékkel együtt.</p>
        HTML;

        $docs['response'] = <<<HTML
        <ul>
            <li>„error”: <strong>0</strong>, ha nincs hiba. <strong>1</strong>, ha van valami hiba.</li>
            <li>„templomok”: A közeli templomok listája. Mindegyik egy <em>templom</em> adattömb, ahogy az egy-egy templom lekérésénél láttuk.</li>
        </ul>
        HTML;

        return $docs;
    }


    public function run() {
        parent::run();
		
        $this->getInputJson();
		$limit = isset($this->input['limit']) ? $this->input['limit'] : 10;
		
		$this->return['templomok'] = \Eloquent\Church::select()
				->addSelect(DB::raw("ST_distance_sphere( ST_GeomFromText('POINT ( ".$this->input['lat']." ".$this->input['lon']." )', 4326), ST_GeomFromText(CONCAT('POINT ( ',lat,' ', lon, ')'), 4326) ) as distance"))
                ->where('ok','i')
				->where('lat','<>','')
                ->orderBy('distance', 'ASC')
				->limit($limit)
                ->get()->map->toAPIArray( 
					isset($this->input['response_length']) ? $this->input['response_length'] : (  $this->fields['response_length']['default'] ? $this->fields['response_length']['default'] : false ), 
					isset($this->input["whenMass"]) ? $this->input["whenMass"] : (  $this->fields['whenMass']['default'] ? $this->fields['whenMass']['default'] : false ));
				
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
				if ($templom['tavolsag'] < 100 && strtotime($mise['idopont']) < time() + 80 * 60 && strtotime($mise['idopont']) > time() - 15 * 60) {
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
