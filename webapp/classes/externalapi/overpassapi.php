<?php

namespace ExternalApi;

# http://wiki.openstreetmap.org/wiki/Overpass_API#Introduction

class OverpassApi extends \ExternalApi\ExternalApi {

    public $name = 'overpass';
    public $apiUrl = "http://overpass-api.de/api/interpreter";
	public $testQuery = 'nwr["name"="Tápiószecső"];out geom;';

    function buildQuery() {
        $this->rawQuery = "[out:json][timeout:" . $this->queryTimeout . "];";
        $this->rawQuery .= $this->query;
        $this->rawQuery = "?data=" . urlencode($this->rawQuery); 
    }

    function buildEnclosingBoundariesQuery($lat, $lon) {
        $this->queryFilter = "['type'='boundary']";
        $this->buildEnclosingFeaturesQuery($lat, $lon);
    }

    function buildEnclosingFeaturesQuery($lat, $lon) {
        $this->query = "is_in(" . $lat . "," . $lon . ")->.a;"
                . "node" . $this->queryFilter . "(pivot.a);out bb center tags;"
                . "way" . $this->queryFilter . "(pivot.a);out bb center tags;"
                . "relation" . $this->queryFilter . "(pivot.a);out bb center tags;";
        $this->buildQuery();
    }

    function buildSimpleQuery($filter = false) {
        if ($filter) {
            $this->queryFilter = $filter;
        }
        $this->query = "("
                . "node" . $this->queryFilter . ";"
                . "way" . $this->queryFilter . ";"
                . "relation" . $this->queryFilter . ";);"
                . "out body qt center;";
        $this->buildQuery();
    }
	
	function buildOneEntityQuery($type, $id) {
		$this->query = "("
                . $type . "(id:" . $id . ");"
				. ");"
                . "out body qt center;";
        $this->buildQuery();
	
	}

    function downloadEnclosingBoundaries($lat, $lon) {
        $this->buildEnclosingBoundariesQuery($lat, $lon);
        $this->run();
    }

	function loadEnclosingBoundaries($lat,$lon) {
		$this->downloadEnclosingBoundaries($lat,$lon);
		
		$return = [];
		if(isset($this->jsonData->elements)) {
			foreach($this->jsonData->elements as $element) {
			
				if($element->tags->boundary == 'administrative' ) {
					$return['administration'][ $element->tags->admin_level ] = (array) $element->tags;
				
				}
				elseif($element->tags->boundary == 'religious_administration' ) {
							
					$return[$element->tags->denomination . '_administration'][ $element->tags->admin_level ] = (array) $element->tags;				
				}										
			}				
		}
		
		// Sorbarendezzük admin_level szerint
		foreach($return as $type => $levels) {
			ksort($levels);
			$return[$type] = $levels;
		}
		
		return $return;
		
	
	}
	
    function downloadUrlMiserend() {
        $this->buildSimpleQuery("['url:miserend']");
        $this->run();
    }

}
