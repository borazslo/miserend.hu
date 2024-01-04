<?php

namespace App\Html;
use Illuminate\Database\Capsule\Manager as DB;

class Map extends Html {

    public function __construct() {
        $this->setTitle("OSM Térkép");

        if (isset($_REQUEST['tid']) AND is_numeric($_REQUEST['tid'])) {
            $church = \App\Model\Church::find($_REQUEST['tid']);
            
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
        if(!$jsonData = Map::geoJsonDiocesesFromCache()) {
            $cacheTime = '1 week';

            $results = DB::table('egyhazmegye')
                ->whereNotNull('osm_relation')
                ->select("osm_relation")
                ->pluck('osm_relation'); 
            $osms = \App\Model\OSM::where('osmtype','relation')->whereIn('osmid',$results->toArray())->where('updated_at','<',date('Y-m-d H:i:s',strtotime('-'.$cacheTime)));
            $osmids = $osms->pluck('osmid')->toArray();
            //Ha még nem tároljuk az osm adatait VAGY már régen akkor itt az ideje
            $diff = array_diff ($results->toArray(), array()); //$osmids);
            if(count($diff) > 0 ) {
                foreach($diff as $d) {              
                    $overpass = new \App\ExternalApi\OverpassApi();
                    $overpass->query = "relation(id:".$d.");out tags qt center;";
                    $overpass->buildQuery();
                    $overpass->run();
                    $overpass->saveElement();                    

                    $element = $overpass->jsonData->elements[0];                    
                    
                    $osm = \App\Model\OSM::updateOrCreate([
                        'osmid' => $element->id,
                        'osmtype' => $element->type],
                        ['lat' => $element->lat,
                        'lon' => 'ss'.$element->lon                         
                    ])->touch();

                    $osmids[] = $d;
                }                
            }                        
            $geoJsons = [];
            foreach($osmids as $osmid) {
                $nominatim = new \App\ExternalApi\NominatimApi();
                $geoJsons[] = json_encode($nominatim->OSM2GeoJson('R', $osmid));                
            }

            if(count($geoJsons) < 1) $json = "{}";
            else $json = "[".implode(',',$geoJsons)."]";

            $cacheDir = PROJECT_ROOT.'/var/tmp/'; // Vigyázz! Egyezzen: geoJsonDiocesesFromCache();
            $cacheFilePath = $cacheDir . 'GeojsonDioceses';  // Vigyázz! Egyezzen: geoJsonDiocesesFromCache();
            if (!file_put_contents($cacheFilePath,$json)) {
                throw new \Exception("We could not save the cacheFile to " . $cacheFilePath);
            }
            return json_decode($json);
        } else {
            return $jsonData;
        }      
    }
        
        
    static function geoJsonDiocesesFromCache() {  
        $cacheDir = PROJECT_ROOT . '/var/tmp/';
        $cacheFilePath = $cacheDir . 'GeojsonDioceses';
        $cacheTime = '1 sec'; // Ez hiába rövid, ha az externalApi cache-e hosszú
        if (file_exists($cacheFilePath)) {
            if (filemtime($cacheFilePath) > strtotime("-" . $cacheTime)) {
                $rawData = file_get_contents($cacheFilePath);
                if (!$jsonData = json_decode($rawData)) {
                    throw new \Exception("Saved Geojsondioceses is not a valid JSON!");
                } else {
                    return $jsonData;
                }
            } else {
                unlink($cacheFilePath);
                return false;
            }
        } else {
            return false;
        }           
    }

}
