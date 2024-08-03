<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Josm extends Html {

    public function __construct($path) {

        if (isset($_REQUEST['update'])) {
            set_time_limit('300');
            $cache = new \ExternalApi\OverpassApi();
            $cache->cache = '1 sec';
            $cache->clearOldCache();

            $job = \Eloquent\Cron::where('class','\OSM')->where('function','checkUrlMiserend')->first();
            $job->run();                       
        }

        $this->setTitle('OSM összeköttetés');
        $this->template = 'josm.twig';        
        
        $this->cron = \Eloquent\Cron::where('class','\OSM')
                ->where('function','checkUrlMiserend')->first();
              

       $overpass = new \ExternalApi\OverpassApi();
       $overpass->downloadUrlMiserend();
        if (!$overpass->jsonData->elements) {
            throw new Exception("Missing Json Elements from OverpassApi Query");
        }

		$urlmiserends = [];
		foreach($overpass->jsonData->elements as $element) {
			preg_match("/(\/|=)([0-9]{1,})(\/|)$/i",$element->tags->{'url:miserend'},$match);
			if(!isset($match[2])) {
				// Ezt igazából megvizsgálja a checkOsmElements tehát nem kell ide.
			} else {
				if(!isset($urlmiserends[$match[2]])) $urlmiserends[$match[2]] = [];
				$urlmiserends[$match[2]][] = $element;		
			}
		}
		
		$this->multipleOSMids = [];
		foreach ($urlmiserends as $church_id => $id) {
			if(count($id) > 1) {
				$this->multipleOSMids[] = [
					'church' => \Eloquent\Church::find($church_id),
					'OSMids' => $id
				];
			}
		}		
		
        list($goodIDs, $this->osmWBadChurch) = $this->checkOsmElements($overpass->jsonData->elements);
        $this->countOsmData = count($overpass->jsonData->elements);
        
        $this->churchesWNoOsm = \Eloquent\Church::where('ok','i')
                ->whereNotIn('id',$goodIDs)
                ->where(function ($query) {
                    $query->whereNull('osmtype')
                        ->orWhereNull('osmid');
                })
                ->orderBy('orszag')->orderBy('megye')->orderBy('varos')->orderBy('nev')
                ->get();
        
        $this->churchesWBadOsm = \Eloquent\Church::where('ok','i')
                ->whereNotIn('id',$goodIDs)
                ->whereNotNull('osmtype')->whereNotNull('osmid')
                ->orderBy('orszag')->orderBy('megye')->orderBy('varos')->orderBy('nev')
                ->get();
        
        $this->churchesWBad = \Eloquent\Church::where('ok','i')
                ->whereNotIn('id',$goodIDs)
                ->get();
				
				
		
               
    }

    function osm2txt($osm) {
        $osm = (array) $osm;

        $return = '';
        $e = array('node' => 'n', 'way' => 'w', 'relation' => 'r');
        $return .= (int) $osm['distance'] . "m: ";
        $return .= " <a title='Megnyitás JOSM-ben' href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "' target='_blank' class='ajax'>";
        if (isset($osm['tags']['name']))
            $return .= $osm['tags']['name'] . " ";
        else
            $return .= $e[$osm['type']] . $osm['id'];
        $return .= "</a>";
        if (isset($osm['tags']['alt_name']))
            $return .= "<span title='alt_name'>" . $osm['tags']['alt_name'] . "</span> ";
        if (isset($osm['tags']['denomination']))
            $return .= "<span title='denomination'>" . $osm['tags']['denomination'] . "</span> ";
        if (isset($osm['tags']['building']))
            $return .= "<span title='building'>" . $osm['tags']['building'] . "</span> ";

        return $return;
    }

   function checkOsmElements($elements) {
       $osmWBadTag = array();
       $goodOsmChurchIds = array();
       
        $c = 0;
        foreach ($elements as $element) {
            //$c++; if($c > 1900) break;
            //printr($element);
            if (isset($element->center->lat)) {
            $element->lat = $element->center->lat;
            }
            if (isset($element->center->lon)) {
                $element->lon = $element->center->lon;
            }
            
            preg_match('/miserend\.hu\/\?{0,1}templom(\/|=)([0-9]{1,5})(\/|)$/i', $element->tags->{'url:miserend'}, $match);
            if(!isset($match[2])) {                
                $osmWBadTag[] = $element;
            } else {
                $church = \Eloquent\Church::find($match[2]);
                if($church) {
                    $goodOsmChurchIds[] = $match[2];
                } else {
                    $osmWBadTag[] = $element;
                }
                
            }
        }
       return array($goodOsmChurchIds, $osmWBadTag);
       
   } 
   
                     
}

?>