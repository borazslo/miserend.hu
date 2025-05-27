<?php

namespace Html;
use Illuminate\Database\Capsule\Manager as DB;

class Map extends Html {

    public function __construct() {
        $this->setTitle("OSM Térkép");

        if (isset($_REQUEST['tid']) AND is_numeric($_REQUEST['tid'])) {
            $church = \Eloquent\Church::find($_REQUEST['tid']);
            
            $this->location = $church->location;
            $this->church_id = $_REQUEST['tid'];   
        }
        
        if(isset($_REQUEST['map'])) {
            $parts = explode('/',$_REQUEST['map']);
            foreach($parts as $part) {
                if(!is_numeric($part)) return;
            }
            
            if(count($parts) == 3) {
                $this->center = [
                    'zoom' => $parts[0],
                    'lat' => $parts[1],
                    'lon' => $parts[2]
                ];
            }
            
            if(count($parts) == 2) {
                $this->center = [
                    'lat' => $parts[0],
                    'lon' => $parts[1]
                ];
            }
            
        }
        
        if(isset($_REQUEST['boundary'])) {
            $this->boundary = $_REQUEST['boundary'];
        }
        
        $data = $this->getGeoJsonDioceses();                
        $this->dioceseslayer = [];
        $this->dioceseslayer['geoJson'] = json_encode($data);        
    }
    
    
    
    static function getGeoJsonDioceses() {
        
            $cacheTime = '1 week';

			//Az általunk rögzített egyházmegyék osm azonosítói
			// TODO: Sajna mindet veszi, pedig azt írjuk ki, hogy római katolikus egyházmegyék. Upsz.
            $results = DB::table('egyhazmegye')
                ->where('nev', 'not like', '%(gk)%')
                ->whereNotNull('osm_relation')
                ->select("osm_relation")
                ->pluck('osm_relation')
				->toArray();
			
			//És letöltjük ezeknek a területeknek a határait
			// No nem minden alkalommal, hiszen létezik minden externalapi-hoz cache. Itt is van.
            $geoJsons = [];
            foreach($results as $osmid) {
                $nominatim = new \ExternalApi\NominatimApi();
                $geoJsons[] = json_encode($nominatim->OSM2GeoJson('R', $osmid));                
            }

            if(count($geoJsons) < 1) $json = "{}";
            else $json = "[".implode(',',$geoJsons)."]";

            $cacheDir = PATH . 'fajlok/tmp/'; // Vigyázz! Egyezzen: geoJsonDiocesesFromCache();
            $cacheFilePath = $cacheDir . 'GeojsonDioceses';  // Vigyázz! Egyezzen: geoJsonDiocesesFromCache();
            if (!file_put_contents($cacheFilePath,$json)) {
                throw new \Exception("We could not save the cacheFile to " . $cacheFilePath);
            }
            return json_decode($json);
            
    }

}
