<?php

use Illuminate\Database\Capsule\Manager as DB;

class OSM {
   
     /*
     * Az OSM-el rendelkező templomoknál letöltjük, hogy milyen területekhez
     * tartozik.
     */

    function checkBoundaries($limit = 50) {

        $churches = \Eloquent\Church::where('ok','i')->where('lat','<>','')
                ->doesntHave('boundaries')
                ->orderByRaw("RAND()")
                ->take($limit)
                ->get();

        if(count($churches) < 1) {
            $results= DB::table('templomok')
            ->join('lookup_boundary_church', 'templomok.id', '=', 'lookup_boundary_church.church_id')
            ->select('lookup_boundary_church.*')
            ->orderBy('lookup_boundary_church.updated_at','ASC')
            ->groupBy('church_id')
            ->limit($limit)
            ->get(); 
            $churches = array();
            foreach($results as $result) {
                $churches[] = \Eloquent\Church::find($result->church_id);
            }
        }
        /**/
        foreach($churches as $church) {    
            $church->MdownloadOSMBoundaries();            
            $church->MmigrateBoundaries();            
        }
                
    }
    
    /*
     * Az OSM-ből az url:miserend -es cuccok lekérése és a templomok azok 
     * alapján való mentése.
     */

    function checkUrlMiserend() {
        $overpass = new \ExternalApi\OverpassApi();
        $overpass->downloadUrlMiserend();
                
         if (!$overpass->jsonData->elements) {
            throw new Exception("Missing Json Elements from OverpassApi Query");
        }
        $c = 0;
        foreach ($overpass->jsonData->elements as $element) {
            $c++;
            if($c > 10000) exit;
            preg_match('/miserend\.hu\/\?{0,1}templom(\/|=)([0-9]{1,5})/i', $element->tags->{'url:miserend'}, $match);
            if(!isset($match[2])) {
                /*
                 * TODO: Van url:miserend, de az értéke vacak. 
                 */
                //printr($element);
               
            } else {
                $church = \Eloquent\Church::find($match[2]);
                if($church)
                    $this->saveOSM2Church($church,$element);
            }                                    
        }
    }
    
    function saveOSM2Church($church, $element) {
			
			// Ha valamiért nincs church.id, akkor inkább elszállunk, minthogy mindent töröljünk és megkavarjunk.
			if(!isset($church->id)) {
				return false;
			}
	
			// Először töröljük az OSM-ből vett adatot, hogy ne maradjon benne olyan ami az újban már nincs
			\Eloquent\Attribute::where('church_id', $church->id)
				->where('fromOSM', 1)
				->delete();
			// Az OSM tags elmentése az Attribute táblába.
			foreach($element->tags as $key => $value) {
				\Eloquent\Attribute::updateOrCreate(
					[
						'church_id' => $church->id,
						'key' => $key
					],			
					[
						'value' => $value,
						'fromOSM' => 1
					]
				);
			}
			
			// Az osm azonosítók és koordináták elmentése 
            if (isset($element->center->lat)) {
            $element->lat = $element->center->lat;
            }
            if (isset($element->center->lon)) {
                $element->lon = $element->center->lon;
            }
            
            $changed = false;
            
			
			// Ha a templomnál még nincs megadva az OSM azonosító, akkor jól megadjuk. És a koordinátákat is jól felülírjuk.
			if( $church->osmid == '' OR $church->osmtype == '' ) {				
                
				$church->osmtype = $element->type;           
                $church->osmid = $element->id;    
				$church->lon = $element->lon;           
                $church->lat = $element->lat;           
				$changed = true;
            }
            /* TODO: biztosan fejetlenül átkütjük? Ha az OSM-ben az url:miserend máshova kerül, máris változik az átkötés. */			
            if( (int) $element->id != (int) $church->osmid OR $element->type != $church->osmtype ) {
				/*
				echo "Változás van az OSM azonosítóban!<br/>\n".
					"'".$church->osmtype.":".$church->osmid."' megváltozik erre: '".$element->type.":".$element->id."'";
                $changed = true;
				$church->osmtype = $element->type;           
                $church->osmid = $element->id;    
				*/
            }
            
            /* Ha biztosan ugyan az az OSM azonosító, de mégis más a koordináta akkor átmentjük aokat az adaokat */
            if(
				( $element->id == $church->osmid AND $element->type == $church->osmtype )
				AND 
				( $element->lat != $church->lat OR $element->lon != $church->lon )
			) {
                $church->lon = $element->lon;           
                $church->lat = $element->lat;           
                $changed = true;
            }            
            $changed ? $church->save() : false;                    
    }

}
