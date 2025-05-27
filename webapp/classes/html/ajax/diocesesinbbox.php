<?php

namespace Html\Ajax;

class DiocesesInBBox extends Ajax {

    public function __construct() {
               
        //echo json_encode(['roman_catholic' => \Html\Map::getGeoJsonDioceses()]);
        //return;

        $bbox = explode(';',$_REQUEST['bbox']);
        if(count($bbox) != 4 ) return ;
        foreach($bbox as $int) {
            if(!is_numeric($int)) return;
        }
              
        echo json_encode([
            'roman_catholic' => $this->getDioceses($bbox, 'roman_catholic'),
            'greekcatholic' => $this->getDioceses($bbox, 'greek_catholic')
        ]);
            
        exit;
            
    }
     
    function getDioceses($bbox, $rite) {

        // Csak az érintett egyházmegyék azonosítóit kérjük le, hogy gyorsabb legyen a lekérdezés
        // Mert ugyan itt is van cache, de minden térképmozdulatnál történik valami
        $overpass = new \ExternalApi\OverpassApi();
        $filter = "['type'='boundary']['boundary'='religious_administration']['religion'='christian']['denomination'='".$rite."']['admin_level'='6']";
        $filter .= "(".$bbox[0].",".$bbox[1].",".$bbox[2].",".$bbox[3].")";
        $overpass->buildSimpleQuery($filter,"ids");
        $overpass->run();
        //print_r($overpass);

        if (!isset($overpass->jsonData->elements)) {
             return [];
        }

        // Az egyházmegyék geoJSON adatait lekérjük.
        // Ez sok kérésnek tűnik, de mivel komoly cache van ezért gyorsan fog menni.
        $dioceses = [];
        
        foreach ($overpass->jsonData->elements as $element) {            
                $types = [
                    'node' => 'N',
                    'way' => 'W',
                    'relation' => 'R'   
                ];

                $nominatim = new \ExternalApi\NominatimApi();        
                $diocese = $nominatim->OSM2GeoJson($types[$element->type], $element->id);                
                if ($diocese == false ) continue;
                $diocese->id = $element->type."/" .$element->id;
                $dioceses[] = $diocese;
                
        
        }

        return $dioceses;

    }

}