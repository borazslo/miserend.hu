<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Collection extends Html {

    public function __construct() {
        parent::__construct();

        preg_match('/(node|way|relation):([0-9]{1,8})$/i', $this->input['q'], $match);
        $osm = \Eloquent\Boundary::where('osmtype',$match[1])
                ->where('osmid',$match[2])->first();
        $this->setTitle($osm->name);

        // Mivel a kirajzolás után magától középre teszi magát, ezért nem kell ez a felesleges kör.         
        $location = $osm->location();
        if( $location ) {
            $this->center = [
                'lat' => $location->lat,
                'lon' => $location->lon
            ];
        }        
        
        $this->boundary = $match[1].':'.$match[2];
        
        $churchIds = DB::table('boundaries')
                ->join('lookup_boundary_church','boundaries.id','=','lookup_boundary_church.boundary_id')
                ->where('boundaries.osmtype',$match[1])
                ->where('boundaries.osmid',$match[2])
                ->select('church_id')
                ->pluck('church_id');
        
        // FIXME for Issue #257
        $churches = \Eloquent\Church::whereIn('id',$churchIds)
                ->where('ok','i')
                ->orderBy('nev')
                ;

        $this->pagination->set($churches->count());
        $this->churches = $churches->skip($this->pagination->skip)->take($this->pagination->take)->get();
        foreach ($this->churches as &$church) {
            $church->photos;
        }
        
        $data = \Html\Map::getGeoJsonDioceses();                
        $this->dioceseslayer = [];
        $this->dioceseslayer['geoJson'] = json_encode($data);        
    }

}
