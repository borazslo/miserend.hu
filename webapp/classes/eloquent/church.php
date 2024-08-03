<?php

namespace Eloquent;

use Illuminate\Database\Capsule\Manager as DB;

/*
 ALTER TABLE `miserend`.`templomok` 
 ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;
 */

class Church extends \Illuminate\Database\Eloquent\Model {

    use \Illuminate\Database\Eloquent\SoftDeletes;
    
    protected $table = 'templomok';
    protected $appends = array('fullName','location','links');

	protected $attributesCache = null;
	
	
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
				$church->loadAttributes();
			} 
		}

        return $church;
        }   
    public function photos() {
        return $this->hasMany('\Eloquent\Photo')->ordered();
    }

    public function keywordshortcuts() {
        return $this->hasMany('\Eloquent\KeywordShortcut');
    }

    public function remarks() {
        return $this->hasMany('\Eloquent\Remark')->orderBy('created_at', 'DESC');
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
     * liturgiatv
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
     */
    public function getLiturgiatvAttribute($value) {
        $litapi = new \ExternalApi\LiturgiatvApi();
        $datas = $litapi->getByChurch($this->id); 
        foreach($datas as $key => $data) {
            if(!isset($data->duration)) $data->duration = 60;
            
            //https://github.com/molnarm/zsolozsma#API
            $minutesToStart  = ( strtotime($data->date." ".$data->time) - time() ) / 60 ;
            if( $minutesToStart > 15 ) {
                $datas[$key]->state = 1;
                $datas[$key]->iconstate = 'disabled';
            } elseif ($minutesToStart > 0 ) {
                $datas[$key]->state = 2;
                $datas[$key]->iconstate = 'higlight';
            } elseif ($minutesToStart > -15 ) {
                $datas[$key]->state = 3;
                $datas[$key]->iconstate = 'live';
            } elseif ( $minutesToStart > 0 - $data->duration - 15 )  {
                $datas[$key]->state = 4;
                $datas[$key]->iconstate = 'higlight';
            } else {
                unset($datas[$key]);
            }            
        }
                                                
        return $datas;
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
                $countMasses = DB::table('misek')->where('tid', $this->id)->where('torles', '0000-00-00 00:00:00')->count();
                if ($countMasses < 1) {
                    $jelzes.=' <i class="fa fa-lightbulb-o fa-lg" title="Nincs hozzá mise!" style="color:#FDEE00"></i> ';
                }
            }

            if ($this->ok == 'n')
                $jelzes.=" <i class='fa fa-ban fa-lg' title='Nem engedélyezett!' style='color:red'></i> ";
            elseif ($this->ok == 'f')
                $jelzes.=" <img src=/img/ora.gif title='Feltöltött/módosított templom, áttekintésre vár!' align=absmiddle> ";

            if($this->ok == 'i' AND $this->miseaktiv == 1) {
                $updatedTime = strtotime($this->frissites);
                if($updatedTime < strtotime("-10 years")) {
                    $jelzes.=" <i class='fa fa-exclamation-triangle fa-lg' title='Több mint 10 éves adatok!' style='color:red'></i> ";
                } elseif ($updatedTime < strtotime("-5 year")) {
                    $jelzes.=" <i class='fa fa-exclamation fa-lg' title='Több mint öt éves adatok!' style='color:red'></i> ";
                } 
            }
            if($this->lat <= 0 OR $this->lon <= 0)
                $jelzes .= '<span class="glyphicon glyphicon glyphicon-map-marker" aria-hidden="true" style="color:red" title="Nincsen koordináta!"></span>';
            if($this->osmid == '' OR $this->osmtype == '')
                $jelzes .= '<span class="glyphicon glyphicon glyphicon-map-marker" aria-hidden="true" style="color:grey" title="OSM adat hiányzik még"></span>';
            return $jelzes;
    }

      public function getRemarksiconAttribute() {
			
		 $allapotok = \Eloquent\Remark::where('church_id',$this->id)->groupBy('allapot')->pluck('allapot')->toArray();            
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
	
    function getFullNameAttribute($value) {
        $return = $this->nev;
        if (!empty($this->ismertnev)) {
            $return .= ' (' . $this->ismertnev . ')';
        } else {
            $return .= ' (' . $this->varos . ')';
        }
        return $return;
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
		if($this->megkozelites != '')
			$location->access = $this->megkozelites;
		
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
		if(isset($api->jsonData->data) > 0 )
			return $api->jsonData->data;
		else
			return false;			
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
                
        DB::table('misek')->where('tid',$this->tid)->delete();
        
        $this->remarks()->delete();
        $this->photos()->delete();

        parent::delete();
    }

  
}
