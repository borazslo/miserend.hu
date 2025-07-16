<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Service_times extends Api {

	public function docs() {

        $docs = [];
        $docs['title'] = 'Kapcsolat a "Horarios de Misa" API-val';
        $docs['input'] = [];
        

        $docs['description'] = <<<HTML
        <p>Olyan kimenetet próbálunk adni, ami a <a href="https://horariosdemisa.com/" target="_blank">Horario de Misa</a> nemzetközi miserend honlap számára értelmes adatot tud közvetíteni.</p>
        <p><strong>Elérhető:</strong> <code>http://miserend.hu/api/v3/service_times</code></p>
        HTML;

        $docs['response'] = <<<HTML
        0, ha nem volt változás és 1, ha volt változás.
        HTML;

        return $docs;
    }

    public function run() {
        parent::run();

        $this->return = [];

		
        $churches = \Eloquent\Church::limit(10)->get();
		set_time_limit('600');
        foreach($churches as $church ) {
			
			$church->loadAttributes();
		
            $serviceTimes = new \ServiceTimes();
            $serviceTimes->loadMasses($church->id,['skipvalidation']);
            
			$syntax = 'horariosdemisa';
			
			if($syntax == 'horariosdemisa') {
				$return = [
					'church_id' => $church->id,
					'name' => $church->names[0],
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