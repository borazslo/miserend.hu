<?php

namespace Eloquent;

use Illuminate\Database\Capsule\Manager as DB;

class Church extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'templomok';
    protected $appends = array('fullName', 'responsible');

    public function getResponsibleAttribute($value) {
        return array($this->letrehozta);
    }

    public function getWriteAccessAttribute($value) {
        global $user;
        return $this->McheckWriteAccess($user);
    }

    public function photos() {
        return $this->hasMany('\Eloquent\Photo')->ordered();
    }

    public function osms() {
        return $this->belongsToMany('\Eloquent\OSM', 'lookup_church_osm', 'church_id', 'osm_id');
    }

    public function keywordshortcuts() {
        return $this->hasMany('\Eloquent\KeywordShortcut');
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
            return $jelzes;
    }
    
    public function getOsmAttribute() {
        if ($this->osms->first() AND $this->osms->first()->enclosing AND count($this->osms->first()->enclosing->toArray()) < 1) {
            $overpass = new \ExternalApi\OverpassApi();
            $overpass->updateEnclosing($this->osms->first());
            $this->load('osms');
        }
        return $this->osms->first();
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

    public function delete() {
        #$this->neighbours()->delete();
        Distance::where('church_to', $this->id)->delete();
        Distance::where('church_from', $this->id)->delete();
        $this->remarks()->delete();
        $this->photos()->delete();
        parent::delete();
    }

    function getLocationAttribute($value) {
        $location = new \stdClass();

        $location->lat = $this->lat;
        $location->lon = $this->lon;
        if ($this->orszag > 0) {
            $location->country = DB::table('orszagok')->where('id', $this->orszag)->pluck('nev')[0];
        }
        if ($this->megye > 0) {
            $location->county = DB::table('megye')->where('id', $this->megye)->pluck('megyenev')[0];
        }
        $location->city = $this->varos;
        $location->address = $this->cim;
        $location->access = $this->megkozelites;

        if ($this->cim == '') {
            $location->address = $this->geoaddress;
        }
        /*
          if($this->osm) {
          #printr($this->osm->toArray());
          $location->lat = $this->osm->lat;
          $location->lon = $this->osm->lon;
          $location->osm = $this->osm->osmtype . "/" . $this->osm->osmid;
          }
         * 
         */
        return $location;
    }

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

    public function scopeWhereHasTag($query, $name, $value) {
        if (is_array($name)) {
            $nameOperator = $name[0];
            $nameValue = $name[1];
        } else {
            $nameValue = $name;
            $nameOperator = '=';
        }
        if (is_array($value)) {
            $valueOperator = $value[0];
            $valueValue = $value[1];
        } else {
            $valueValue = $value;
            $valueOperator = '=';
        }
        return $query->whereHas('tags', function ($query) use ( $nameOperator, $nameValue, $valueOperator, $valueValue ) {
                    $query->where('name', $nameOperator, $nameValue)->where('value', $valueOperator, $valueValue);
                });
    }

}
