<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Stat extends Html {

    public function __construct() {
        parent::__construct();
        $this->setTitle('Statisztika');
		$this->stats = [];
        
        global $user;
        /*
        if (!$user->loggedin) {
            addMessage("Hozzáférés megtagadva!", "danger");
            $this->redirect('/');
        }
        */ 

        /* 
         * Templomok frissítettsége + észrevételek: Elmúlt év
         */
		$stat = new \Jqplots('chart_templomaink');
		$stat->title = 'Aktív misézőhelyeink frissítettsége';
		$stat->labels = ['aktív misézőhelyek, amik akkor frissültek utoljára','beküldött észrevételek száma az adott időben'];
		$stat->axes['xaxis'] = [];
				
		
		$stat->data = [0 => [], 1 => [] ] ;
            
        $churches = \Eloquent\Church::where('ok', 'i')
				->where('miseaktiv',1)
                ->countByUpdatedYear()
                ->get();
        foreach($churches as $church) {
            $stat->data[0][] = [(int) $church->updated_year,$church->count_updated_year];
        }        
        $remarks = \Eloquent\Remark::countByCreatedYear()->get();
        foreach($remarks as $remark) {
            if($remark->created_year > 0)
                $stat->data[1][] = [(int) $remark->created_year,$remark->count_created_year];
        }       
        $stat->prepare_html();
		$stat->prepare_script();
		$this->stats[$stat->id] = (array) $stat;
		unset($stat);
		
		
        /* 
         * Templomok frissítettsége + észrevételek: Elmúlt év
         */
		$stat = new \Jqplots('chartN');
		$stat->title = 'Az elmúlt 12 hónap frissítései';
		$stat->labels = ['aktív misézőhelyek, amik akkor frissültek utoljára','beküldött észrevételek száma az adott időben'];
		
		
        $churches = \Eloquent\Church::where('ok', 'i')
				->where('miseaktiv',1)		
                ->countByUpdatedMonth()
                ->where('frissites', '>', date('Y-m-d', strtotime('-1 year')))
                ->get();
        $stat->data = [0=>[],1=>[]];
        foreach($churches as $church) {
            $stat->data[0][] = [$church->updated_month,$church->count_updated_month];
        }        
        $remarks= \Eloquent\Remark::countByCreatedMonth()
                ->where('created_at', '>', date('Y-m-d', strtotime('-1 year')))
                ->get();
        foreach($remarks as $remark) {
            $stat->data[1][] = [$remark->created_month,$remark->count_created_month];
        }
        $stat->prepare_html();
		$stat->prepare_script();
		$this->stats[$stat->id] = (array) $stat;
		unset($stat);
		
        
        /*
         * Templom karbantartók statisztikái
         */
        $this->s4 = ['data'=>[],'labels'=>[]];
        
        $data = \Eloquent\ChurchHolder::select('user_id',DB::raw('count(*) as count'))->groupBy('user_id')->orderBy('count')->get();
        
        foreach($data as $uid => $count ) {
            if(isset($tmp[$count->count]))
             $tmp[$count->count]++;
            else
                $tmp[$count->count] = 1;
        }   
		if($tmp)
			foreach($tmp as $k => $v)
				$this->s4['data'][] = [$k,$v];
        
		
		/*
		* Templomok ahol van Accessibility adat
		*/
		$accessibilityOSMTags = ['wheelchair', 'wheelchair:description','toilets:wheelchair','hearing_loop','disabled:description'];
		$results = DB::table('attributes')
			->select('attributes.*','templomok.id as church_id','attributes.key as name')
			->join('templomok',function($join)
                         {
                             $join->on('templomok.id', '=', 'attributes.church_id');
                         })
			->whereNotNull('templomok.id')
			->whereIn('attributes.key',$accessibilityOSMTags)
			->where('attributes.value','<>','')			
			->get();
			
		$this->accessibility['churches'] = [];
		foreach($accessibilityOSMTags as $tag) $this->accessibility['tags'][$tag] = [];
		foreach($results as $res) {			
			$this->accessibility['churches'][$res->church_id] = $res->church_id;
			if(isset($this->accessibility['tags'][$res->name][$res->value])) $this->accessibility['tags'][$res->name][$res->value]++;
			else $this->accessibility['tags'][$res->name][$res->value] = 1;			
		}
		// Megjelenítendő: $this->accessibility 
		// printr($this->accessibility);
		
		
		/* 
         * Felhasználók regisztráltása és utolsó ténykedése
         */
        $stat  = [
            'labels' => ['utoljára aktív felhsználók','újonnan regisztrált (és aktivizált) felhasználók'],
            'data' => [0 => [], 1 => [] ] 
            ];
        
			
		 $results = DB::table('user')
                    ->select(DB::raw('COUNT(*) as count'),DB::raw("date_format(lastactive,'%Y') as lastactive_year"))					
					->groupBy('lastactive_year')
					->get();
		foreach($results as $result) {
			if($result->lastactive_year == 0) $stat['early'] = $result->count;
			else
			$stat['data'][0][] = [ (int) $result->lastactive_year, (int) $result->count];
		}
		 $results = DB::table('user')
                    ->select(DB::raw('COUNT(*) as count'),DB::raw("date_format(regdatum,'%Y') as regdatum_year"))
					->where('regdatum','<>','0000-00-00 00:00:00')
					->where('regdatum','>','2015-00-00 00:00:00')
					->where('lastlogin','<>','0000-00-00 00:00:00')
					->groupBy('regdatum_year')
					->get();
		foreach($results as $result) {		
			$stat['data'][1][] = [(int) $result->regdatum_year, (int) $result->count];
		}				
		$this->s2 = $stat;

			
        /* 
         * ExternalApi Stats 
         */
        $this->s5 = ['data'=>[],'labels'=>[]];
        $data = DB::table('stats_externalapi')->select('name','date',DB::raw('SUM(count) count'),DB::raw('CONCAT(name,date)  namedate'))->where('date','>',date('Y-m-d',strtotime('-1 month')))->groupBy('namedate')->orderBy('date','asc')->get();        
        $data = collect($data)->groupBy(['name'])->toArray(); //->transform(function($item, $k) {return $item->groupBy('name');})->toArray();
        $c = 0;        
        foreach($data as $apiname => $api) {
            $this->s5['labels'][$c] = $apiname;
            $this->s5['data'][$c] = [];
            foreach($api as $date) {
                $this->s5['data'][$c][] = [$date->date,(int) $date->count];
            }
            $c++;
        }        
        
        /*
		 * Magyarországi aktív templomok frissítettségének statisztikája
		*/
		// FIXME for Issue #257
		$stat = DB::table('templomok')
			->selectRaw("DATEDIFF(NOW(), frissites) DIV 365 as yearago")
			->selectRAW("count(*) as count");		
		$stat->where('orszag',12)->where('ok','i')->where('miseaktiv',1);
		foreach(['%isézőhely%','%ápolna','%özösségi%', '%imaház%', '%imaterem%'] as $notlike)
			$stat->where('nev','not like', $notlike);
		$stat->orderBy('frissites','DESC');
		
		$results = $stat->groupBy('yearago')->get();		
		$sum = 0; $maxYear = 0;
		
		if($results[0]->yearago > 0 ) {
			$results->prepend(new \stdClass() );
			$results[0]->yearago = 0;
			$results[0]->count = 0;
		}
		
		foreach($results as $result) {
			$sum += $result->count;
			if($maxYear < $result->yearago) $maxYear = $result->yearago;
		}
			
		$minColor = [255,0,0]; 
		$maxColor = [139,42,42];
		foreach($results as $k => $result) {
			$results[$k]->percent = round ( $result->count / ( $sum / 100 ) );
			
			$r = round( $minColor[0] + ( ( $maxColor[0] - $minColor[0] ) / ( $maxYear - 2 ) * ( $result->yearago - 2 ) ) );
			$g = round ( $minColor[1] + ( ( $maxColor[1] - $minColor[1] ) / ( $maxYear - 2 ) * ( $result->yearago - 2 ) ) );
			$b = round ( $minColor[2] + ( ( $maxColor[2] - $minColor[2] ) / ( $maxYear - 2 ) * ( $result->yearago - 2 ) ) );
			
			if($result->yearago == 0 ) $results[$k]->rgb = [0,255,0];
			elseif($result->yearago == 1 ) $results[$k]->rgb = [255,255,0];
			else $results[$k]->rgb = [$r,$g,$b];
		}
		
		$this->magyar = $results;
		
		
		/* OSM tag variácók */
		$attributes = DB::table('attributes')
			->select('attributes.*','templomok.osmtype', 'templomok.osmid')
			->join('templomok','templomok.id', '=', 'attributes.church_id')
			->where('fromOSM',1)
			->orderBy('key')
			->orderBy('value')
			->orderBy('church_id')
			->get();
			
		$osmtags = [];
		foreach($attributes as $item) {
			if(!isset($osmtags[$item->key])) {
				$osmtags[$item->key] = [
					'count' => 0,
					'dist' => 0,
					'name' => $item->key,
					'overpassturbo' => "http://overpass-turbo.eu/?Q=". urlencode('	[out:json][timeout:25];nwr["url:miserend"]["'.$item->key.'"];out geom;')."&R",
					'values' => []
				];
			}
	
			$osmtags[$item->key]['count']++;
			
			if(!array_key_exists($item->value, $osmtags[$item->key]['values']) ) {
				$osmtags[$item->key]['values'][$item->value] = [
					'value' =>  $item->value,
					'overpassturbo' => "http://overpass-turbo.eu/?Q=". urlencode('	[out:json][timeout:25];nwr["url:miserend"]["'.$item->key.'"="'.$item->value.'"];out geom;')."&R",
					'churches' => []
					
				];
				$osmtags[$item->key]['dist']++;
			}
			$osmtags[$item->key]['values'][$item->value]['churches'][] = $item;	
				
			
		}
		//printr($osmtags);
		
		$this->osmtags = $osmtags;
				
		/* nearBy.log  */
		$this->stats['nearbylog'] = [];
		$this->stats['massrightnowlog'] = [];
		$filePath = '../nearby.log';
		if (file_exists($filePath)) {
			$file = fopen($filePath, 'r');
			if ($file) {
				while (($line = fgetcsv($file)) !== false) {
					if (count($line) > 1) {
						$this->stats['nearbylog'][] = [ $line[1], $line[2] ];
						if (isset($line[4]) && $line[4] === 'true') {
							$this->stats['massrightnowlog'][] = [ $line[1], $line[2] ];
						}
					}
				}
				fclose($file);
			} else { 
				error_log("Failed to open file: $filePath");
			}
		} else { 
			error_log("File does not exist: $filePath");
		}

	}
		
}