<?php

namespace Eloquent;

use Illuminate\Database\Capsule\Manager as DB;
use ExternalApi\ElasticsearchApi;

/*
 ALTER TABLE `miserend`.`templomok` 
 ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;
 */

class Church extends \Illuminate\Database\Eloquent\Model {

    use \Illuminate\Database\Eloquent\SoftDeletes;
    
    protected $table = 'templomok';
    protected $appends = array('names', 'alternative_names', 'fullName','location','links');
    protected $fillable = [
        'nev', 'cim', 'orszag', 'megye', 'varos', 'plebania', 'pleb_eml', 'leiras',
        'lat', 'lon', 'miseaktiv', 'ok', 'frissites', 'misemegj','osmid','osmtype',
        'accessibility'
    ];
	protected $attributesCache = null;
	
    // TODO FIXME #174 sémakisimítás. Mindegyikben engedélyezni kéne a null-t vagy default érték
    protected $attributes = [        
        'plebania' => '',
        'leiras' => '',
        'megjegyzes' => '',
        'misemegj' => '',
        'bucsu' => '',
        'kontakt' => '',
        'kontaktmail' => '',
        'adminmegj' => '',
        'log' => '',
        'osmid' => false,
        'osmtype' => false,
        'lat' => 0.0,
        'lon' => 0.0,
        'nev'   => '',
        'frissites' => false,
        'ok' => 'n', // n: nem engedélyezett, i: engedélyezett, f: feltöltött, áttekintésre vár
    ];

    public function adorations()
    {
        return $this->hasMany(Adoration::class);
    }
    
    public function getConfessionsAttribute()
    {
        // Get all confessions related to this church
        $lastConfessionData = \Eloquent\Confession::where('church_id', $this->id)->orderBy('timestamp', 'desc')->limit(1)->get();
        // Ha sosem kaptunk még ilyen adatot, az azt jelenti, hogy a templomban nincs telepítve gyóntatási kapcsoló
        if ($lastConfessionData->isEmpty()) {
            return false;
        }
        
        $confession = $lastConfessionData->first();
        
        $toleranceSeconds = 10660; // tolerance window in seconds

        if ($confession->status === 'ON' && ( time() - strtotime($confession->timestamp) ) <= $toleranceSeconds) {
            $status = 'ON';
        } else {
            $status = 'OFF';
        }
        
        // Get all confession status changes for this church, ordered by timestamp DESC
        $startTime2 = strtotime('-40 days');
        $confessions = \Eloquent\Confession::where('church_id', $this->id)
            ->where('timestamp', '>=', date('Y-m-d H:i:s', $startTime2))
            ->orderBy('timestamp', 'asc')
            ->limit(50000)
            ->get(['status', 'timestamp', 'local_id','id']);

        $periods = [];
        $current = [];
        $currentIds = [];
        $lastTimeStamp = 0;
       
        foreach ($confessions as $conf) {   
            //echo "<br>-------------<br>NEXT: ".$conf->local_id." ".$conf->status."<br/>";
            //echo "CurrentTimestamp: " .strtotime($conf->timestamp). " - " . date('Y-m-d H:i:s', strtotime($conf->timestamp)) . " = ".$conf->timestamp."<br>";
            //echo "LastTimeStamp: ". $lastTimeStamp ." - " . date('Y-m-d H:i:s', $lastTimeStamp) . "<br>";
            // Calculate the difference between current and last timestamp in minutes
            $diffMinutes = ($lastTimeStamp > 0) ? round((strtotime($conf->timestamp) - strtotime(date('Y-m-d H:i:s',$lastTimeStamp))) / 60, 2) : 0;
            //echo "Diff from last: {$diffMinutes} minutes<br/>";
            //echo "CurrentIds: ".count($currentIds)."<br/>";
            // Rendezze a $currentIds tömböt érték szerint, a kulcsokat megtartva
            asort($currentIds);
            foreach ($currentIds as $id => $starttime) {
                $startDate = date('Y-m-d H:i:s', $starttime);
                $currentTimeStamp = strtotime($conf->timestamp);
                $currentTimeStampDate = date('Y-m-d H:i:s', $currentTimeStamp);
                $diff = $currentTimeStamp - $starttime;
                $diffMinutes = round($diff / 60, 2);
                //echo "currentIds[$id]: $startDate, diff: {$diffMinutes} minutes, conf->timestamp: {$currentTimeStampDate}";
                if( $diff > $toleranceSeconds) {                  
                    unset($currentIds[$id]);
                    $current['end'] = $toleranceSeconds + $starttime;
                } else {
                    
                }
                //echo "<br/>";
            }
            

            if(count($currentIds) < 1 AND $current !== []) {                                        
                    $periods[] = [
                        'start' => date('Y-m-d H:i:s', $current['start']),
                        'end' => date('Y-m-d H:i:s', $current['end']),
                        'duration' => $current['end'] - $current['start']
                    ];
                    $current = [];
                    $currentIds = [];
                }
           
            

            //Ha OFF akkor biztosan vége valaminek, vagy csak békében tovább lépünk.
            if ($conf->status === 'OFF') {
                
                //Ha ezzel a local_id-vel van folyamatban, akkor azt lezárjuk.
                if(isset($currentIds[$conf->local_id])) {
                    $current['end'] = strtotime($conf->timestamp); //Ez még nem biztos, hogy a tényleges vég. Majd következő OFF-nál kiderül.
                    unset($currentIds[$conf->local_id]);
                }
                // Ha már egyetlen local_id-vel sincs folyamatban semmi, akkor az egész periódust lezárjuk.
                if(count($currentIds) < 1 AND $current !== []) {                    
                    $periods[] = [
                        'start' => isset($current['start']) ? date('Y-m-d H:i:s', $current['start'] ) : 'error',
                        'end' => date('Y-m-d H:i:s', $current['end']),
                        'duration' => $current['end'] - $current['start']
                    ];
                    $current = [];
                    $currentIds = [];
                }
            }
            
            
            // Ha rendesen ON, akkor elindítjuk vagy folytatjuk.
            if($conf->status == 'ON') {
                //Ha nincs, akkor elindítjuk
                if(!isset($currentIds[$conf->local_id])) {
                    $current['start'] = strtotime($conf->timestamp);                    
                    $currentIds[$conf->local_id] = strtotime($conf->timestamp);
                } else {
                    //Ha már folyamatban van, akkor a korrábbi start idővel folytatjuk.
                    $currentIds[$conf->local_id] = strtotime($conf->timestamp);
                }

            }
            //echo "SO: current start: " . (isset($current['start']) ? date('Y-m-d H:i:s', $current['start']) : 'n/a') . ", current end: " . (isset($current['end']) ? date('Y-m-d H:i:s', $current['end']) : 'n/a') . "<br/>";
            $lastTimeStamp = strtotime($conf->timestamp);
            
        }

        // Ha még van folyamatban, akkor azt beküldjük vég nélkül.
        if($current !== []) {
            $periods[] = [
                'start' => date('Y-m-d H:i:s',$current['start']),
                'duration' => time() - strtotime($current['start'])                
                ];
        }
       

        $periods = array_reverse($periods);
        
        //$periods = array_slice($periods, 0, 10);
        //printr($periods);
        return ['status' => $status, 'last_periods' => $periods];
    }

	public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }
	
	public function loadAttributes()
    {
        $attributes = $this->attributes()->get()->pluck('value', 'key')->toArray();
        foreach ($attributes as $key => $value) {
			if(!isset($this->$key))
				$this->setAttribute($key, $value);
			else {
				// throw new \Exception("The attribute '".$key."' has already existed.");
			}

        }
    }

	public function __call($method, $parameters)
    {
	
		$church = parent::__call($method, $parameters);
		
		if($church) {
			// Amikor leszóhívunk az adatbázisból egy templomot, akkor rögtön feltöltjük teljesen a tulajdonságaival
			if ($method == 'find') {			
				                
                // Minden OSM key->value betöltése
                $church->loadAttributes();
            }
        }

        return $church;
        }   
    public function photos() {
        return $this->hasMany('\Eloquent\Photo')->ordered();
    }

    public function getMassRRulesByPeriodAttribute()
    {
        $massRRules = $this->hasMany('\Eloquent\CalMass', 'church_id')->with('period')->get()->groupBy('period_id')->toArray();
        
        $RRulesByPeriods = [];
        foreach($massRRules as $periodId => $massRules) {
            $RRulesByPeriods[$periodId] = $massRules[0]['period'];
            $RRulesByPeriods[$periodId]['massrules'] = [];
            foreach($massRules as $k => $massRule) {
                if(!empty($massRule['rrule'])) {
                    $rrule = new \SimpleRRule($massRule['rrule']);                    
                    $massRule['rrule']['readable'] = $rrule->toText();
                    //TODO: Itt ez hiba, mert nem egy konrkét legenerált Periodban nézünk szét, hanem csak egy általánosban
                    // Vagyis a konkrét dátumokban keresés teljesen hülyeség.
                    // Nekünk amúgy is csak azért kell, hogy tudjuk milyen napon kezdődik. Azt meg megtudhatjuk máshogy is.
                    $occ = reset($rrule->getOccurrences());
                    if($occ) {
                        $massRule['start_date'] = $occ->toString();                                                
                    }
                    $RRulesByPeriods[$periodId]['massrules'][] = $massRule;
                }
            } 
                        
            // sort massrules by weekday of start_date (Sunday=0 .. Saturday=6), tie-breaker by datetime
            usort($RRulesByPeriods[$periodId]['massrules'], function($a, $b) {
                $wa = isset($a['start_date']) ? (int)date('w', strtotime($a['start_date'])) : 0;
                $wb = isset($b['start_date']) ? (int)date('w', strtotime($b['start_date'])) : 0;
                if ($wa === $wb) {
                    $ta = isset($a['start_date']) ? strtotime($a['start_date']) : 0;
                    $tb = isset($b['start_date']) ? strtotime($b['start_date']) : 0;
                    return $ta < $tb ? -1 : ($ta > $tb ? 1 : 0);
                }
                return $wa < $wb ? -1 : 1;
            });
            
        }

        return $RRulesByPeriods;
    }

    
    public function getGeneratedMassRRulesAttribute() {
        $masses = $this->hasMany('\Eloquent\CalMass', 'church_id');
        
        $massPeriods = \Eloquent\CalMass::generateMassPeriodInstancesForYears( $masses->get()->all(), [], [date('Y'),date('Y')+1]);
        foreach($massPeriods as $k => $mass) {
            $rrule = new \SimpleRRule($mass['rrule']);
            $occ = reset($rrule->getOccurrences());                        
            $massPeriods[$k]['start_date'] = $occ->toString();
            $massPeriods[$k]['readable_rrule'] = $rrule->toText();
        }
        return $massPeriods;
    }

    public function massrules() {
        return $this->hasMany('\Eloquent\CalMass', 'church_id');
    }
    
    public function getLanguagesAttribute() {
        // Grab the 'lang' column from related massrules, remove empty values, unique and return as array
        return $this->massrules()
                    ->pluck('lang')
                    ->filter(function($v) { return $v !== null && $v !== ''; })
                    ->unique()
                    ->values()
                    ->toArray();
    }

    public function keywordshortcuts() {
        return $this->hasMany('\Eloquent\KeywordShortcut');
    }

    public function remarks() {
        return $this->hasMany('\Eloquent\Remark')->orderBy('created_at', 'DESC');
    }

    public function suggestionPackages() {
        return $this->hasMany('\Eloquent\CalSuggestionPackage', 'church_id')->orderBy('created_at', 'DESC');
    }

    public function updateNeighbours() {
        //TODO: Does not work! 
        // "Call to undefined method Illuminate\Database\Query\Builder::MupdateChurch()"
        $distance = new Distance();        
        $distance->MupdateChurch($this);
    }
    
    public function neighbours() {
        return $this->where('templomok.id',$this->id)
                ->join('distances', function($join)
                    {
                      $join->on('distances.fromLon', '=', 'lon');
                      $join->on('distances.fromLat', '=', 'lat');

                    })
                  ->join('templomok as churchTo',function($join)
                    {
                      $join->on('distances.toLon', '=', 'churchTo.lon');
                      $join->on('distances.toLat', '=', 'churchTo.lat');
                    })
                ->select('distances.*','churchTo.*')    
                ->where('churchTo.ok', 'i')
                ->orderBy('distances.distance', 'ASC');

    }
    
    public function neighbourss() {
        return \Eloquent\Church::join('distances', function($join)
                    {
                      $join->on('distances.toLon', '=', 'lon');
                      $join->on('distances.toLat', '=', 'lat');

                    })
                    ->where('distances.fromLon',$this->lon)
                    ->where('distances.fromLat',$this->lat)
                    ->where('ok','i')
                            ->select('templomok.*','distances.distance')
                    ->orderBy('distances.distance', 'ASC');                                               
    }

    public function getNeighboursAttribute () {
        return $this->neighbourss()
                    ->limit(10)
                    ->get();
        exit;
        return $this->neighbours()->where("distance", "<=", $distance)->get();
    }    
    
    
    public function toAPIArray($length = "minimal", $whenMass = false)
    {

        if($length == 'elastic') {
            $elastic = true;
            $length = 'medium';
        } else {
            $elastic = false;
        }

        if($length == false) $length = "minimal";
        if ($whenMass == false ) $whenMass = date('Y-m-d');
        
        $misek = [];
        if(!$elastic) {
            $search = new \Search('masses');
            $search->day($whenMass);
            
            $search->tids([$this->id]);
            $masses = $search->getResults(0,10);
                    
            if(isset($masses)) {
                foreach($masses as $key => $mise) {
                    $misek[$key]['idopont'] = date('Y-m-d H:i:s', strtotime($mise->start_date));
                    $info = trim( t($mise->rite)." ".t($mise->title));
                    if( $this->orszag != 12 or $mise->lang != 'hu') {
                        $info .= ' ' . t('LANGUAGES.'.$mise->lang)." nyelven";
                    }
                    if (!empty($mise->types)) {                        
                        $translatedTypes = array_map(function($type) { return t($type); }, $mise->types);
                        $info .= ', ' . implode(', ', $translatedTypes);                        
                    }
                    if($mise->comment) $info .= ' (' . $mise->comment.')';
                    if($info != '') $misek[$key]['informacio'] = $info;
                }	            
            }
        } 

        $adorations = [];
        $results = $this->adorations()
				->where('date', '>=', date('Y-m-d'))
				->orderBy('date', 'ASC')
				->orderBy('starttime', 'ASC')
				->limit(5)
				->get()
				->toArray();
        foreach($results as $key => $adoration) {
            $adorations[$key]['kezdete'] = $adoration['date']." ".$adoration['starttime'];
            $adorations[$key]['vege'] = $adoration['date']." ".$adoration['endtime'];				
            $adorations[$key]['fajta'] = $adoration['type'];				
            if($adoration['info'] != '') $adorations[$key]['info'] =  $adoration['info'];
        }
        $this->loadAttributes();

        if($length == "minimal") {
            $return = [
                'id' => $this->id,
                'nev' => !empty($this->names) ? $this->names[0] : '',
                'frissitve' => date('Y-m-d H:i:s', strtotime($this->frissites)),
                'ismertnev' => !empty($this->alternative_names) ? $this->alternative_names[0] : '',
                'orszag' => ( DB::table('orszagok')->where('id', $this->orszag)->value('nev') ?: "" ),
                'varos' => $this->varos,
                'misek' => $misek,
                'adoraciok' => $adorations,
                'gyontatas' => $this->confessions ? $this->confessions['status'] : false,
                'koordinatak' => [ (float) $this->lat, (float) $this->lon ],
                'lat' => (float) $this->lat,
                'lon' =>(float) $this->lon,
                'tavolsag' => (int) $this->distance
            ];
            return $return;
        }

        $return = [
            'id' => $this->id,
            'names' => $this->names,
            'nev' => !empty($this->names) ? $this->names[0] : '',
            'ismertnev' => !empty($this->alternative_names) ? $this->alternative_names[0] : '',
            'alternative_names' => $this->alternative_names,
            'frissitve' => date('Y-m-d H:i:s', strtotime($this->frissites)),            
            'orszag' => ( DB::table('orszagok')->where('id', $this->orszag)->value('nev') ?: "" ),
            'egyhazmegye' => ( DB::table('egyhazmegye')->where('id', $this->egyhazmegye)->value('nev') ?: "" ),
            'megye' => ( DB::table('megye')->where('id', $this->megye)->value('megyenev') ?: "" ),
            'varos' => $this->varos,
            'cim' => $this->cim,
            'megkozelites' => '',
            'plebania' => str_replace('<br>', "\n", strip_tags($this->plebania, '<br>')),
            'leiras' => str_replace('<br>', "\n", strip_tags($this->leiras, '<br>')),
            'accessibility' => $this->accessibility,
            'email' => $this->pleb_eml,
            'links' => $this->links->pluck('href')->toArray(),
            'misek' => $misek,
            'nyelvek' => $this->languages,
            'miserend_megjegyzes' => str_replace('<br>', "\n", strip_tags($this->misemegj, '<br>')),
            'adoraciok' => $adorations,
            'gyontatas' => $this->confessions ? $this->confessions : false,
            'kozossegek' => array_map(function($kozosseg) {
                return [
                    'nev' => $kozosseg->name,
                    'link' => $kozosseg->link
                ];
            }, $this->kozossegek),
            'koordinatak' => [ (float) $this->lat, (float) $this->lon ],
            'lat' => (float) $this->lat,
            'lon' => (float) $this->lon,
            'tavolsag' => (int) $this->distance
        ];

        if($length == 'full') {
            $return = array_merge($return, [                
                'photos' => $this->photos->pluck('url')->toArray()                
            ]);

        }
        
        if($elastic) {

            // Kiegészítjük Budapest kerületekkel
            $romai = ['0','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII','XIII','XIV','XV','XVI','XVII','XVIII','XIX','XX','XXI','XXII','XXIII'];
            
            preg_match('/^Budapest (.*?)\. kerület$/',$return['varos'],$match);
            if($match) {
                $return['varos'] = [ $return['varos'], 'Budapest '.array_search($match[1], $romai).'. kerület', 'Budapest' ];
            }

            unset($return['adoraciok']);
            unset($return['miserend_deprecated']);
            if($return['gyontatas'] == null) {
                $return['gyontatas'] = [];
            }

            //görög
            if( isset($this->denomination) && $this->denomination == 'greek_catholic') {
                $return['gorog'] = 'true';
            } else {
                $return['gorog'] = 'false';
            }
        }

        return $return;
    }

    public function toElasticArray()
    {
        $church = $this->toAPIArray('elastic');           
        return $church;
    }   
    

    /* 
     * scopes
     *  boundaries() 
     *  inBBox()
     *  churchesAndMore
     *  countByUpdatedMonth
     *  countByUpdatedYear
     *  selectUpdatedMonth- ?
     *  selectUpdatedYear
     *  whereShortcutLike($keyword, $type)

     */
    function scopeBoundaries($query) {
        return $query->belongsToMany('Eloquent\Boundary', 'lookup_boundary_church')
                ->withTimestamps();
    } 
    
    function scopeInBBox($query, $bbox) {
        return $query->whereBetween('lat', [$bbox['latMin'], $bbox['latMax']])
                            ->whereBetween('lon', [$bbox['lonMin'], $bbox['lonMax']]);
    }

    function scopeChurchesAndMore($query) {
        // FIXME for Issue #257
        return $query->where('nev', 'NOT LIKE', '%kápolna%');
    }

    function scopeSelectUpdatedMonth($query) {
        return $query->addSelect(DB::raw('DATE_FORMAT(frissites,\'%Y-%m\') as updated_month'), DB::raw('COUNT(*) as count_updated_month'));
    }

    function scopeSelectUpdatedYear($query) {
        return $query->addSelect(DB::raw('DATE_FORMAT(frissites,\'%Y\') as updated_year'), DB::raw('COUNT(*) as count_updated_year'));
    }

    function scopeCountByUpdatedMonth($query) {
        return $query->selectUpdatedMonth()
                        ->groupBy('updated_month')->orderBy('updated_month');
    }

    function scopeCountByUpdatedYear($query) {
        return $query->selectUpdatedYear()
                        ->groupBy('updated_year')->orderBy('updated_year');
    }

    function scopeWhereShortcutLike($query, $keyword, $type) {
        return $query->whereHas('keywordshortcuts', function ($query) use ($keyword, $type) {
                    $query->where('type', $type)->where('value', 'like', $keyword);
                });
    }
    
    /*
     * getSomethingAttribute -> $this->something;
     * 
     * names
     * alternative_names
     * denomination
     * holders
     * links
     * readAcess (of current user)
     * writeAccess (of current user)
     * jelzes
     * fullName
     * remarksSatus
     * location
	 * kozossegek
     * accessibility
     */
    public function getNamesAttribute($value) {


        $attributes = $this->attributes()->get()->pluck('value', 'key')->toArray();
        
        // Collect all the possible names of the church
        $names = [];
        // Let's find the main / default name 
        if (isset($attributes['name:hu'])) {
            array_unshift($names, $attributes['name:hu']);
        } elseif (isset($attributes['name'])) {
            array_unshift($names, $attributes['name']);
        } else {
            if($this->nev == '') 
                $this->nev = '(Név nélküli misézőhely)';                        
            array_unshift($names, $this->nev);
        }
        // Let's find the other names
        foreach ($attributes as $key => $value) {
            if (preg_match('/^name(:.*)?$/', $key)) {
                $names[] = $value;
            }
        }
               
        return array_values(array_unique($names));
    }

    public function getAlternativeNamesAttribute($value) {
        $attributes = $this->attributes()->get()->pluck('value', 'key')->toArray();

       // Collect all alternative names of the church
       $alternativeNames = [];
       // Collect alternative names
       if (isset($attributes['official_name:hu'])) {
           array_unshift($alternativeNames, $attributes['official_name:hu']);
       } elseif (isset($attributes['alt_name:hu'])) {
           array_unshift($alternativeNames, $attributes['alt_name:hu']);
       } elseif (isset($attributes['old_name:hu'])) {
           array_unshift($alternativeNames, $attributes['old_name:hu']);
       } elseif (isset($attributes['official_name'])) {
           array_unshift($alternativeNames, $attributes['official_name']);
       } elseif (isset($attributes['alt_name'])) {
           array_unshift($alternativeNames, $attributes['alt_name']);
       } elseif (isset($attributes['old_name'])) {
           array_unshift($alternativeNames, $attributes['old_name']);
       }

       foreach ($attributes as $key => $value) {
           if (preg_match('/^(alt_|old_|official_)name(:.*)?$/', $key)) {
               $alternativeNames[] = $value;
           }
       }                
       return array_values(array_unique($alternativeNames));
       

    }

    public function getDenominationAttribute($value) {
        return  in_array($this->egyhazmegye,[34,17,18]) ? 'greek_catholic' : 'roman_catholic';
    }
    
    public function getHoldersAttribute($value) {
        $holders =  \Eloquent\ChurchHolder::where('church_id',$this->id)->orderBy('status')->orderBy('updated_at','desc')->get()->groupBy('status');
        return $holders;
    }
    
    public function getLinksAttribute($value) {
        $links =  $this->hasMany('\Eloquent\ChurchLink')->get();
        return $links;
    }
    
    public function getReadAccessAttribute($value) {
        global $user;
        return $this->checkReadAccess($user);
    }
    
    public function getWriteAccessAttribute($value) {
        global $user;
        return $this->checkWriteAccess($user);
    }
    
    public function getJelzesAttribute() {
            $jelzes = ""; //$this->remarksStatus['html'];

            if ($this->miseaktiv == 1) {                
                $calMassCount = \Eloquent\CalMass::where('church_id', $this->id)->count();
                if ($calMassCount < 1) {
                    $jelzes .= ' <i class="fa fa-lightbulb-o fa-lg" title="Nincs hozzá mise!" style="color:#FDEE00"></i> ';
                }
                
            }

            if ($this->ok == 'n')
                $jelzes.=" <i class='fa fa-ban fa-lg red' title='Nem engedélyezett!' ></i> ";
            elseif ($this->ok == 'f')
                $jelzes.=" <img src=/img/ora.gif title='Feltöltött/módosított templom, áttekintésre vár!' align=absmiddle> ";

            if($this->ok == 'i' AND $this->miseaktiv == 1) {
                $updatedTime = strtotime($this->frissites);
                if($updatedTime < strtotime("-10 years")) {
                    $jelzes.=" <i class='fa fa-exclamation-triangle fa-lg red' title='Több mint 10 éves adatok!' > </i> ";
                } elseif ($updatedTime < strtotime("-5 year")) {
                    $jelzes.=" <i class='fa fa-exclamation fa-lg red' title='Több mint öt éves adatok!'> </i> ";
                } 
            }
            if($this->lat <= 0 OR $this->lon <= 0)
                $jelzes .= '<span class="fa fa-map-marker" aria-hidden="true" style="color:red" title="Nincsen koordináta!"></span>';
            if($this->osmid == '' OR $this->osmtype == '')
                $jelzes .= '<span class="fa fa-map-marker" aria-hidden="true" style="color:grey" title="OSM adat hiányzik még"></span>';
            return $jelzes;
    }

    /* Észrevételekhez azaz Remarks-hez kapcsolódó attribútumok */
    public function getRemarksiconAttribute() {
        // Treat empty string allapot as 'j' for grouping
        $allapotok = $this->remarks->map(function($remark) {
            return ($remark->allapot === '' ? 'j' : $remark->allapot);
        })->unique()->toArray();
        //printr($allapotok);
        if (in_array('u', $allapotok))
            $remarksicon = "ICONS_REMARKS_NEW";
        elseif (in_array('f', $allapotok))
            $remarksicon = "ICONS_REMARKS_PROCESSING";
        elseif (count($allapotok) > 0)
            $remarksicon = "ICONS_REMARKS_ALLDONE";
        else
            $remarksicon = "ICONS_REMARKS_NO";
        return $remarksicon;
    }
	
    public function getRemarksStatusTextAttribute() {
        // Treat empty string allapot as 'j' for grouping
        $allapotok = $this->remarks->map(function($remark) {
            return ($remark->allapot === '' ? 'j' : $remark->allapot);
        })->unique()->toArray();
        if (in_array('u', $allapotok))
            $remarksStatusText = "Új észrevétel érkezett.";
        elseif (in_array('f', $allapotok))
            $remarksStatusText = "Van még feldolgozás alatt álló észrevétel.";
        elseif (count($allapotok) > 0)
            $remarksStatusText = "Minden észrevétel feldolgozva.";
        else
            $remarksStatusText = "Nem érkezett még észrevétel.";
        return $remarksStatusText;
    }

    function getRemarksStatusAttribute($value) {
        $return = false;
        $remark = $this->remarks()
                        ->select('allapot')
                        ->groupBy('allapot')
                        ->orderByRaw("FIND_IN_SET(allapot, 'u,f,j')")->first();

        if (!$remark) {
            $return['text'] = "Nincsenek észrevételek";
            $return['html'] = "<i class='fa fa-gift fa-lg' style='color:#D3D3D3'  title='" . $return['text'] . "'></i>";
            $return['mark'] = false;
        } else if ($remark->allapot == 'u') {
            $return['text'] = "Új észrevételt írtak hozzá!";
            $return['html'] = "<a href=\"javascript:OpenScrollWindow('/templom/$this->id/eszrevetelek',550,500);\"><img src=/img/csomag.gif title='" . $return['text'] . "' align=absmiddle border=0></a> ";
            $return['mark'] = 'u';
        } else if ($remark->allapot == 'f') {
            $return['text'] = "Észrevétel javítása folyamatban!";
            $return['html'] = "<a href=\"javascript:OpenScrollWindow('/templom/$this->id/eszrevetelek',550,500);\"><img src=/img/csomagf.gif title='" . $return['text'] . "' align=absmiddle border=0></a> ";
            $return['mark'] = 'f';
        } else if ($remark->allapot == 'j') {
            $return['text'] = "Észrevételek";
            $return['html'] = "<a href=\"javascript:OpenScrollWindow('/templom/$this->id/eszrevetelek',550,500);\"><img src=/img/csomag1.gif title='" . $return['text'] . "' align=absmiddle border=0></a> ";
            $return['mark'] = 'j';
        }
        return $return;
    }

    /* Javaslat csomagokhoz azaz suggestion_packages-hez kapcsolódó attribútumok */
    public function getHasPendingSuggestionPackageAttribute() {        
        $hasPendingSuggestionPackage = $this->suggestionPackages()
                        ->select('id')
                        ->where('state', 'PENDING')                        
                        ->first();                        
        if ($hasPendingSuggestionPackage) {
            return true;
        } 
        return false;
    }


    function getFullNameAttribute($value) {
        
        $return = $this->names[0];

        if (!empty($this->alternative_names)) {
            $return .= ' (' . $this->alternative_names[0] . ')';
        } else {
            $return .= ' (' . $this->varos . ')';
        }
        return $return;
    }


    
    function getLocationAttribute($value) {
        $location = new \stdClass();

        $location->lat = $this->lat;
        $location->lon = $this->lon;
        if($this->osmtype AND $this->osmid) {
            $location->osm = array(
                'type' => $this->osmtype, 
                'id'=>$this->osmid,
                'url' => 'https://www.openstreetmap.org/'.$this->osmtype.'/'.$this->osmid
                 );
				 
			$location->address = false;	 
			//if(isset($this->{'addr:postcode'}))
			//	 $location->address = $this->{'addr:postcode'}." ";
			//if(isset($this->{'addr:city'}))
			//	 $location->address .= $this->{'addr:city'}. " ";
			if(isset($this->{'addr:street'}))
				 $location->address .= $this->{'addr:street'}. " ";	 
			if(isset($this->{'addr:housenumber'}))
				 $location->address .= " ".$this->{'addr:housenumber'}.".";	 
				 
				 
        } else {            
            $location->address = $this->cim;
        }
				
        /* Adminisrative Boundaries(Country,County, City, District) */
        $boundaries = $this->boundaries()
                ->where('boundary','administrative')
                ->whereIn('admin_level',[2,6,8,9,10])
                ->orderBy('admin_level')              
                ->get()->toArray();

        if(array_key_exists(0, $boundaries)) $location->country = $boundaries[0];
        if(array_key_exists(1, $boundaries)) $location->county = $boundaries[1];
        if(array_key_exists(2, $boundaries)) $location->city = $boundaries[2];   
        if(array_key_exists(3, $boundaries)) $location->district = $boundaries[3];        
                
        return $location;
    }
	
	
	public function getKozossegekAttribute($value) {
		$api = new \ExternalApi\KozossegekApi();		
		$api->query = "miserend/".$this->id;
		$api->run();
		if(isset($api->jsonData->data) > 0 ) {
			foreach($api->jsonData->data as $key => $data) {
				if(isset($data->age_group) ) {
					$api->jsonData->data[$key]->age_group = array_filter( explode(", ", $data->age_group), 'strlen' );
				}
				if(isset($data->tags)) {
					$api->jsonData->data[$key]->tags = array_filter( explode(", ", $data->tags), 'strlen' ); 
				}
			}
			return $api->jsonData->data;
		}
		else
			return [];			
	}

    public function getAccessibilityAttribute($value) {
        $return = [];
        foreach(['wheelchair','toilets:wheelchair','wheelchair:description','hearing_loop','disabled:description'] as $k=>$accessibility) {			
			if(isset($this->$accessibility)) {			
					$return[$accessibility] = $this->$accessibility;
			}
		}
        return $return;
    }
	
    /*
     * What does 'M' mean?
     */
    public function MgetReligious_administration() {
        $this->religious_administration = new \stdClass();
        $this->religious_administration->diocese = new \Diocese();
        $this->religious_administration->diocese->getByChurchId($this->id);
        $this->religious_administration->deaconry = new \Deaconry();
        $this->religious_administration->deaconry->getByChurchId($this->id);
        $this->MgetParish();
    }

    function MgetParish() {
        if (!isset($this->religious_administration)) {
            $this->religious_administration = new \stdClass();
        }
        $parish = new \Parish();
        $parish->getByChurchId($this->id);
        $this->religious_administration->parish = $parish;
    }

    function checkReadAccess($_user) {
        $access = false;
        if ($this->ok == 'i')
            $access = true;
       
        if($this->checkWriteAccess($_user)) 
            $access = true;       
        
        global $user;                
        if($user->uid == $_user->uid) {
            $this->readAcess = $access;
        }         
        return $access;
    }

    function checkWriteAccess($_user) {
        $access = false;

        if ($_user->checkRole('miserend'))
            $access = true;        
        
        if(\Eloquent\ChurchHolder::where('church_id',$this->id)->where('user_id',$_user->uid)->where('status','allowed')->first())
            $access = true;
               
        if(DB::table('egyhazmegye')->where('id',$this->egyhazmegye)->where('felelos',$_user->username)->first())        
            $access = true;
        
        global $user;
        if($user->uid == $_user->uid) {
            $this->writeAcess = $access;
        }         
        return $access;
    }

    function MgetDioceseId() {
        if($this->religious_administratin)
            return $this->religious_administration->diocese->id;
        else 
            return false;
    }
	
    public function boundaries()
    {
        return $this->belongsToMany('Eloquent\Boundary', 'lookup_boundary_church')
                ->withTimestamps();
    }
    
    function MdownloadOSMBoundaries() {
        return;
        $overpass = new \ExternalApi\OverpassApi();
        $overpass->downloadEnclosingBoundaries($this->lat, $this->lon);
                
        //Detach all boundaries but those manually added (without OSM integration);                    
        $this->boundaries()->where('osmid','>','0')->detach();
        foreach($overpass->jsonData->elements as $element) {
            $boundary = \Eloquent\Boundary::firstOrNew(['osmtype' => $element->type, 'osmid' => $element->id]);
            
            $changed = false;
            foreach ( array('boundary','admin_level','name','alt_name','denomination') as $key ) {
                if(isset($element->tags->$key) AND $element->tags->$key != $boundary->$key ) {
                    $boundary->$key = $element->tags->$key;
                    $changed = true;
                }                
            }
            $changed ? $boundary->save() : false;

            $this->boundaries()->attach($boundary->id);               
        }
        $this->boundaries()->touch();
        $this->MmigrateBoundaries();        
        
        return;
    }
    
    
    /* 
     * A régi templomok.egyhazmegye/espereskerulet/orszag/megye/varos -ból csinál
     * boundary értéket, ha még nincs. Ill. összekapcsolást.
     */
    function MmigrateBoundaries() {
        global $_egyhazmegyek, $_espereskeruletek, $_orszagok, $_megyek, $_varosok;
                                           
        /* egyházmegye */
        $tmp = $this->boundaries()
                ->where('boundary','religious_administration')
                ->where('denomination','LIKE','%_catholic')
                ->where('admin_level',6)
                ->get()->toArray();        
        if($tmp == array()) {
            $boundary = \Eloquent\Boundary::firstOrNew(['boundary' => 'religious_administration', 'denomination' => $this->denomination, 'admin_level' => 6, 'name' => $_egyhazmegyek[$this->egyhazmegye]->nev]);
            $boundary->save(); 
            $this->boundaries()->attach($boundary->id);
        }
        
        /* espereskerület */
        $tmp = $this->boundaries()
                ->where('boundary','religious_administration')
                ->where('denomination','LIKE','%_catholic')
                ->where('admin_level',7)
                ->get()->toArray();
        if($tmp == array()) {
            $boundary = \Eloquent\Boundary::firstOrNew(['boundary' => 'religious_administration', 'denomination' => $this->denomination, 'admin_level' => 7, 'name' => $_espereskeruletek[$this->espereskerulet]->nev]);
            $boundary->save();
            $this->boundaries()->attach($boundary->id);            
        }
        
        /* ország */
        $tmp = $this->boundaries()
                ->where('boundary','administrative')
                ->where('admin_level',2)
                ->get()->toArray();
        if($tmp == array()) {
            $boundary = \Eloquent\Boundary::firstOrNew(['boundary' => 'administrative', 'admin_level' => 2, 'name' => $_orszagok[$this->orszag]->nev]);
            $boundary->save();
            $this->boundaries()->attach($boundary->id);            
        }
        
        /* megye */
        $tmp = $this->boundaries()
                ->where('boundary','administrative')
                ->where('admin_level',6)
                ->get()->toArray();
        if($tmp == array()) {
            if(isset($_megyek[$this->megye])) {
                $boundary = \Eloquent\Boundary::firstOrNew(['boundary' => 'administrative', 'admin_level' => 6, 'name' => $_megyek[$this->megye]->nev." megye"]);
                $boundary->save();
                $this->boundaries()->attach($boundary->id);            
            }
        }        

        /* város */
        $tmp = $this->boundaries()
                ->where('boundary','administrative')
                ->where('admin_level',8)
                ->get()->toArray();
        if($tmp == array()) {
            $boundary = \Eloquent\Boundary::firstOrNew(['boundary' => 'administrative', 'admin_level' => 8, 'name' => $this->varos]);
            $boundary->save();
            $this->boundaries()->attach($boundary->id);  
        }

    }

    public function delete() {

        #$this->neighbours()->delete();
        #Distance::where('church_to', $this->id)->delete(); fromLat, fromLon
        #Distance::where('church_from', $this->id)->delete(); toLat, toLon
        
        \Eloquent\ChurchHolder::where('church_id',$this->id)->delete();
        \Eloquent\Favorite::where('tid',$this->id)->delete();
        \Eloquent\ChurchLink::where('church_id',$this->id)->delete();
        
        //Nem elegáns:
        DB::table('lookup_boundary_church')->where('church_id',$this->id)->delete();
                        
        // Calendar dolgok törlése
        \Eloquent\CalMass::where('church_id', $this->id)->delete();
        \Eloquent\CalSuggestionPackage::where('church_id', $this->id)->delete();

        $this->remarks()->delete();
        $this->photos()->delete();

        parent::delete();
    }

  
    public function save(array $options = [])
    {
        // Másolat készítése a modellről
        $model = $this;

        // Végigmegyünk az attribútumokon
        foreach ($model->getAttributes() as $key => $value) {
            // Ha az attribútum nem szerepel az eredeti attribútumok között, eltávolítjuk
            if (!in_array($key, array_keys($model->getOriginal()))) {
                unset($model->$key);
            }
        }

        // Meghívjuk az eredeti save() metódust
        $return = parent::save($options);

        // Miután már elmentettük, akkor
        // Elasticsearch frissítése
        ElasticsearchApi::updateChurches([$this->id]);
    }
}
