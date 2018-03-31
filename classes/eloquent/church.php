<?php

namespace Eloquent;

use Illuminate\Database\Capsule\Manager as DB;

class Church extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'templomok';
    protected $appends = array('responsible','fullName');

    public function photos() {
        return $this->hasMany('\Eloquent\Photo')->ordered();
    }

    public function keywordshortcuts() {
        return $this->hasMany('\Eloquent\KeywordShortcut');
    }

    public function remarks() {
        return $this->hasMany('\Eloquent\Remark')->orderBy('created_at', 'DESC');
    }

    public function neighbours() {
        return $this->hasMany('\Eloquent\Distance', 'church_from', 'id')
                ->join('templomok', 'templomok.id', '=', 'church_to')
                ->where('templomok.ok', 'i')
                ->orderBy('distance', 'ASC');
    }

    public function closestNeighbour() {
        return $this->neighbours()->take(1);
    }

    public function neighbourWithinDistance($distance = 10000) {
        return $this->neighbours()->where("distance", "<=", $distance);
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
        return $query->whereHas('osms', function($query) use ($bbox) {
                    $query->whereBetween('lat', [$bbox['latMin'], $bbox['latMax']])
                            ->whereBetween('lon', [$bbox['lonMin'], $bbox['lonMax']]);
                });
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
     * denomination
     * responsible
     * writeAccess
     * jelzes
     * fullName
     * remarksSatus
     * location
     */
    public function getDenominationAttribute($value) {
        return  in_array($this->egyhazmegye,[34,17,18]) ? 'greek_catholic' : 'roman_catholic';
    }
    
    public function getResponsibleAttribute($value) {
        return array($this->letrehozta);
    }

    public function getWriteAccessAttribute($value) {
        global $user;
        return $this->McheckWriteAccess($user);
    }
    
    public function getJelzesAttribute() {
            $jelzes = $this->remarksStatus['html'];

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
        } else {
            $location->access = $this->megkozelites;
            $location->address = $this->cim;
        }

        /* Address addr:steet, addr:housenumber */
        $tags = collect(\Eloquent\OSMTag::where('osmtype',$this->osmtype)
                ->where('osmid',$this->osmid)
                ->whereIn('name',['addr:street','addr:housenumber'])
                ->orderBy('name','DESC')
                ->get())->keyBy('name');
        if(count($tags) > 0) {
            $location->address = '';
            foreach($tags as $tag) {
                $location->address .= $tag->value." ";
            }
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

    function McheckReadAccess($user) {
        if ($this->ok == 'i')
            return true;
        if ($this->letrehozta == $user->username)
            return true;
        if ($user->checkRole('miserend'))
            return true;
        return false;
    }

    function McheckWriteAccess($user) {
        if ($user->checkRole('miserend'))
            return true;
        if ($this->letrehozta == $user->username)
            return true;
        if (!is_array($user->responsible))
            return false;
        if (in_array($this->id, $user->responsible['church']))
            return true;
        if (in_array($this->MgetDioceseId(), $user->responsible['diocese']))
            return true;

        return false;
    }

    function MgetDioceseId() {
        return $this->religious_administration->diocese->id;
    }

    public function boundaries()
    {
        return $this->belongsToMany('Eloquent\Boundary', 'lookup_boundary_church')
                ->withTimestamps();
    }
    
    function MdownloadOSMBoundaries() {
        $overpass = new \ExternalApi\OverpassApi();
        $overpass->downloadEnclosingBoundaries($this->lat, $this->lon);
        
        /* Elementjük az osmtags táblába az összes adatot. */
        $overpass->saveElement(); 

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
        Distance::where('church_to', $this->id)->delete();
        Distance::where('church_from', $this->id)->delete();
        $this->remarks()->delete();
        $this->photos()->delete();
        parent::delete();
    }

  
}
