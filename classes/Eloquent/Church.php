<?php

namespace Eloquent;

class Church extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'templomok';
    protected $appends = array('fullName', 'responsible', 'lon', 'osm');

    public function getResponsibleAttribute($value) {
        return array($this->letrehozta);
    }

    public function photos() {
        return $this->hasMany('\Eloquent\Photo')
                        ->orderBy('flag')
                        ->orderByRaw("CASE WHEN height/width > 1 THEN 1 ELSE 0 END desc")
                        ->orderBy("id");
    }
    
    public function osms() {
        return $this->belongsToMany('\Eloquent\OSM', 'lookup_church_osm', 'church_id', 'osm_id');
    }
    
    public function getOsmAttribute() {
        if ($this->osms->first()->enclosing AND count($this->osms->first()->enclosing->toArray()) < 1) {
            $overpass = new \OverpassApi();
            $overpass->updateEnclosing($this->osms->first());
            $this->load(osms);
        }
        return $this->osms->first();
    }

    public function remarks() {
        return $this->hasMany('\Eloquent\Remark')->orderBy('created_at', 'DESC');
    }

    public function neighbours() {
        return $this->hasMany('\Eloquent\Distance', 'from', 'id')->orderBy('distance', 'ASC');
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

    function getLonAttribute($value) {
        return $this->lng;
    }

    public function delete() {
        $this->neighbours()->delete();
        Distance::where('to', $this->id)->delete();
        $this->remarks()->delete();
        $this->photos()->delete();
        parent::delete();
    }

    public function MgetLocation() {        
        $this->location = new \Location();
        $this->location->getByChurchId($this->id);
        $this->osm->religiousAdministration;
        $this->location->country = $this->osm->country;
        $this->location->county = $this->osm->county;
        $this->location->city = $this->osm->city;
        
        if (isset($this->osm) AND $this->osm != '') {
            $this->location->lat = $this->osm->lat;
            $this->location->lon = $this->osm->lon;
            $this->location->osm = $this->osm->osmtype . "/" . $this->osm->osmid;
        } else {
            $this->location->lon = $this->location->lng;
            unset($this->location->lng);
        }
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
        if ($this->letrehozta == $user->username)
            return true;
        if (in_array($this->id, $user->responsible['church']))
            return true;
        if (in_array($this->MgetDioceseId(), $user->responsible['diocese']))
            return true;
        if ($user->checkRole('miserend'))
            return true;
        return false;
    }

    function MgetDioceseId() {
        return $this->religious_administration->diocese->id;
    }

}
