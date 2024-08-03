<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Service_times extends Api {

    public function run() {
        parent::run();

        $this->return = [];

		
        $churches = \Eloquent\Church::limit(10000)->get();
		set_time_limit('600');
        foreach($churches as $church ) {
			
			$church->loadAttributes();
		
            $serviceTimes = new \ServiceTimes();
            $serviceTimes->loadMasses($church->id,['skipvalidation']);
            
			$syntax = 'horariosdemisa';
			
			if($syntax == 'horariosdemisa') {
				$return = [
					'church_id' => $church->id,
					'name' => $church->nev,
					'address' => $church->location->address,
					'city, state' => $church->location->city['name'],
					'country' => $church->location->country['name'],
					'phone' => false,
					'email' => $church->pleb_eml,
					'url' => false,
					'location' => [$church->location->lat,$church->location->lon],
					'service_times' => $serviceTimes->string,
					'confessions' => false,
					'adoration' => false,
					'additional_information' => $church->misemegj,
					'last_confirmation' => $church->frissites,
				];
				
				if(count($church->links) > 1) {
					foreach($church->links as $link)
						$return['url'][] = $link->href;
				} elseif ( isset($church->link[0]) ) {
					$return['url'] = $church->links[0]->href;					
				}
				
				// Mivel ez egy nemzetközi oldalra megy ki, ezért a "nev" helyett az angol ill. eredeti nevét használjuk a helynek
				if(isset($church->{"name"})) {
					$return['name'] = $church->{"name"};
					if(isset($church->{"name:en"}) AND $church->{"name:en"} != $church->{"name"} )	{
						$return['name'] .= ' / '.$church->{"name:en"};
					}
				}
						
				$return['additional_information'] = 'Source: https://miserend.hu/templom/'.$church->id;
				
				$this->return[] = $return;
			} else {
				$this->return[] = [
					'church_id' => $church->id,
					'service_times' => $serviceTimes->string
				];
            }
        }
        
    }

}