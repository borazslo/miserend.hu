<?php

namespace Html\Church;

class EditOsm extends \Html\Html {

    public function __construct($path) {
        global $user;

        $this->input = $_REQUEST;
        $this->tid = $path[0];
        $this->church = \Eloquent\Church::find($this->tid);
        if (!$this->church) {
            throw new \Exception('Nincs ilyen templom.');
        }
        $this->church = $this->church->append(['writeAccess']);

        if (!$this->church->writeAccess) {
            throw new \Exception('Hiányzó jogosultság!');
            return;
        }

		// Ha nem férünk hozzá az OSM, hez akkor hiába minden, így inkább ne is lehessen semmit kitölteni.
		try {
			$osmapi = new \ExternalApi\OpenstreetmapApi();
			$osmapi->query = "/api/0.6/user/details?json";
			$osmapi->run();
			if ( !isset($osmapi->xmlData->user)) {
				throw new \Exception($osmapi->rawData);
			}
		} catch (\Exception $e) {
			addMessage('Az OSM-hez írási joggal nem tudunk hozzáférni, ezért nincsenek szerkeszthető adataink.','danger');
			return;
		}

		
		// Letöltjük a legfrissebb saját OSM adatait
		$this->loadOSMDataWithOSM();
	
		// Letöltjük azt is, hogy milyen területi egység része ez a cucc
		global $twig;
		$overpassapi = new \ExternalApi\OverpassApi();
		$this->administration = $overpassapi->loadEnclosingBoundaries($this->church['lat'],$this->church['lon']);
		
		// Letöltjük a teljes listát az OSM-ről, hogy az autocomplete boldogan üzemelhessen
		$overpassapi->downloadUrlMiserend();
		$this->autocomplete = $this->prepareAutocomplete($overpassapi->jsonData);
		
				
		// Előkészítjük a FORM-ot, hogy megtaláljuk, mik a mezők amiket okés változtatni
		$this->prepareForm();
		$this->findValidKeys();

		// Ha beküldtünk adatokatt.
        $isForm = \Request::Text('submit');
        if ($isForm) {
			// Változtatunk, elmentünk, ilyenek.
            $this->modify();
		   
		    // Újra letöltjük az OSM-ről az adatokat, hogy a frissek legyenek meg.
			$this->loadOSMDataWithOSM();			
			$this->prepareForm();
        }
		 		
    }

    function modify() {
			
        if ($this->input['church']['id'] != $this->tid) {
            throw new \Exception("Gond van a módosítandó templom azonosítójával.");
        }
		
		if ( 
			$this->church['osmtype'] != $this->input['osmtype'] OR 
			$this->church['osmid'] != $this->input['osmid'] OR 
			$this->church['id'] != $this->input['church']['id']
			) {
			addMessage('Valami csalás próbál történni és az osmtype és osmid nem megfelelő. Ezért inkább nem mentettünk semmit.','warning');
			return;
		}
		
		// Összeállítjuk az OSM tagokat amiket menteni szeretnénk
		$this->osmtagsToSave = $this->prepareUpdatedOsmtags();
		if(!$this->osmtagsToSave) {
			addMessage('Nem volt változtatás, így nem volt mint elmenteni.','info');
			return;
		}
		// Az eredeti OSM entity XML-t átalakítjuk a megfelelőre.
		 $this->prepareOSMEntityXml();
		
		$osmapi = new \ExternalApi\OpenstreetmapApi();
		// Open ChangeSet
		$changeSetID = $osmapi->changesetCreate();
		// Upload OSM entity XML		
		$versionID = $osmapi->putEntity($changeSetID, $this->input['osmtype'], $this->osmEntity);
	
		// Close Changeset
		$osmapi->changesetClose($changeSetID);

		// Siker
		if($versionID > 0) {
			// Mentüsk el az adatokat a saját attribute adattáblánkba is.
			
			// Először töröljük az OSM-ből vett adatot, hogy ne maradjon benne olyan ami az újban már nincs
			\Eloquent\Attribute::where('church_id', $this->church['id'])
				->where('fromOSM', 1)
				->delete();
			// Az OSM tags elmentése az Attribute táblába.
			foreach($this->osmtagsToSave as $key => $value) {
				\Eloquent\Attribute::updateOrCreate(
					[
						'church_id' => $this->church['id'],
						'key' => $key
					],			
					[
						'value' => $value,
						'fromOSM' => 1
					]
				);
			}			
						
			addMessage ('Közvelenül OSM adatokat is módosítottunk. Nagyon izgalmas. <a href="'.$messageurl.'">changeset/'.$changesetID.'</a>','success');
			
		}
		
		// Hova is térjünk vissza
        switch ($this->input['modosit']) {
            case 'n':
                $this->redirect("/church/catalogue");
                break;

            case 't':
                $this->redirect("/church/" . $this->church->id);
                break;

            case 'm':
                $this->redirect("/church/" . $this->church->id . "/editschedule");
                break;

            default:
                break;
        }
    }

    
   function loadOSMDataWithOverpass() {
   
		// Elszállunk, hanincs OSM összekötettetés
		if(!isset($this->church['osmtype']) or !isset($this->church['osmid']) or $this->church['osmtype'] == '' or $this->church['osmid'] == '') {
			addMessage('Ehhez a misézőhelyhez nem létezik OSM azonosító, ezért nincs mit szerkeszteni.','danger');
			return;
		}
						
		// Lekérdezzük az OSM adatokat
		$overpassapi = new \ExternalApi\OverpassApi();
		$overpassapi->cache = "1 sec"; // Itt fontos, hogy mindig friss legyen az adat.		
		$overpassapi->buildOneEntityQuery($this->church['osmtype'],$this->church['osmid']);
		$overpassapi->run();
		
		// Elszállunk, ha nem találtunk OSM adatot az OSM azonosítók alapján
		if($overpassapi->jsonData->elements == array() ) {
			addMessage('Ehhez a misézőhelyhez bár van OSM azonosító ('.$this->church['osmtype'].':'.$this->church['osmid'].'), mégsem találjuk azt az OSM-ben.'); 
		}
		
		$this->osmtags = $overpassapi->jsonData->elements[0]->tags;
		
		/*
		// Lekérdezzük azt is, hogy mi veszi körül ezt a helyet
		$point = [ $overpassapi->jsonData->elements[0]->center->lat, $overpassapi->jsonData->elements[0]->center->lon ];
		$overpassapi->buildEnclosingBoundariesQuery($point[0],$point[1]);
		$overpassapi->run();
		printr($overpassapi);
		*/
		
		return true;
   }
   
   function loadOSMDataWithOSM() {
   
		// Elszállunk, hanincs OSM összekötettetés
		if(!isset($this->church['osmtype']) or !isset($this->church['osmid']) or $this->church['osmtype'] == '' or $this->church['osmid'] == '') {
			addMessage('Ehhez a misézőhelyhez nem létezik OSM azonosító, ezért nincs mit szerkeszteni.','danger');
			return;
		}
						
		// Lekérdezzük az OSM adatokat
		$osmapi = new \ExternalApi\OpenstreetmapApi();
		$osmapi->apiUrl = "https://api.openstreetmap.org"; // Hiába dev/staging, a lekérésnél az élő kell, különben nem ad eredményt.
		$osmapi->headerAuthorization = false;
		$osmapi->query = "/api/0.6/".$this->church['osmtype']."/".$this->church['osmid'];
		$osmapi->run();
		
		$this->osmEntity = $osmapi->xmlData;

		$this->osmtags =  new \stdClass();
		foreach ( $osmapi->xmlData->{$this->church['osmtype']}->tag as $entity ) {
			$current = current($entity->attributes());
			$this->osmtags->{$current['k']} = $current['v'];			
		};

		return true;
   
   }

   function prepareForm() {
   
		$osmtags = $this->osmtags;
		
		$this->form['name'] = [
			'title' => 'Elnevezés',
			
			'description' => 'Nem kötelező mindet kitölteni, de határon túli misézőhelyeknél figyeljünk erre!',
			'inputs' => [
				'name' => [
					'title' => 'Név (a helyi nyelven)'
				],
				'name:hu' => [
					'title' => 'Név magyarul (ha a helyi nyelv nem magyar)'
				],
				'alt_name' => [
					'title' => 'Közismert név (a helyi nyelven)'
				],
				'alt_name:hu' => [
					'title' => 'Közismert név (ha a helyi nyelv nem magyar)'
				],
				'old_name' => [
					'title' => 'Régi elnevezés (helyi nyelven)'
				],
				'official_name' => [
					'title' => 'Hivatalos elnevezés (ha az eltér)',
					'help' => 'Olykor nem hajlandó a világ elfogadni névnek a hivatalos nevet, ezért létezik ez a hivatalos név mező. Lehetőség szerint ez legyen üres.'
				],
			]
		];
			
		
		$this->form['disabilites'] = [
		# https://wiki.openstreetmap.org/wiki/Disabilitydescription
		# https://wiki.openstreetmap.org/wiki/How_to_map_for_the_needs_of_people_with_disabilities
			'title' => 'Akadálymentesség',
			'inputs' => [
				'wheelchair' => [
					'title' => 'Kerekesszékkel hozzáférhetőség',
					'options' => array(
						'' => 'Nincs információ',
						'yes' => 'Akadálymentes',
						'limited' => 'Részben akadálymentes',				
						'no' => 'Egyáltalán nem akadálymentes'
					)					
				],
				'wheelchair:description' => [
					'title' => 'Kiegészítés, ha szükséges'
				],
				'toilets:wheelchair' => [
					'title' => 'Akadálymentes mosdó',
					'options' => array(
						'' => 'Nincs információ vagy nincs mosdó',
						'yes' => 'Kerekesszékkel hozzáférhető a mosdó',
						'no' => 'Kerekesszékkel nem hozzáférhető a mosdó'
					)
				],
				'hearing_loop' => [
					'title' => 'Hallást segítő indukciós hurok',
					'options' => array(
						'' => 'Nincs információ',
						'no' => 'Nincs indukciós hurok',
						'limited' => 'Van indukciós hurok, de tenni kell érte, hogy működjön',				
						'yes' => 'Van indukciós hurok'                
					)
				],
				# https://wiki.openstreetmap.org/wiki/How_to_map_for_the_needs_of_people_with_disabilities
				'disabled:description' => [
					'title' => 'További leírás bármilyen akadálymentesség kapcsán'
				]
			]
		];
		
		
		$this->form['location'] = [
			'title' => 'Elhelyezkedés',			
			'inputs' => [
				'addr:country' => [
					'title' => 'Ország rövidítése (ha nem Magyarország)',
					'help' => 'Magyarország esetében nem kell kitölteni a magyar OSM szerkesztési hagyományoknak megfelelően. De minden más országban két betűs kód való ide.'
				],
				'addr:postcode' => [
					'title' => 'Irányítószám'
				],
				'addr:city' => [
					'title' => 'Település'
				],
				'addr:street' => [
					'title' => 'Közterület (utca/stb.)'
				],
				'addr:housenumber' => [
					'title' => 'Házszám'
				],
				'addr:postbox' => [
					'title' => 'Postafiók'
				],
				'addr:conscriptionnumber' => [
					'title' => 'Helyrajziszám'
				]
				
			]
		];
		
		$this->form['religious_administration'] = [
			'title' => 'Egyházigazgatási beosztás',			
			'inputs' => [
				'amenity' => [
					'title' => 'Elsődleges címke (mindig place_of_worship)',
					'help' => 'Minden hely, ahol szentmisék vannak, azok vallási helyek, ezért szükséges, hogy az elsődleges címke (amenity) mindig vallási hely (place_of_worship) kell legyen.'
				],
				'religion' => [
					'title' => 'Vallás (mindig christian)',
					'help' => 'Minden helyünk keresztény. Pont.',
					'options' => [ 
						'christian' => 'keresztény' 
					]
					
				],
				'denomination' => [
					'title' => 'Felekezet',
					'help' => 'Bár a görögkatolikus és a római katolikus az nem két külön felekezet, de az OSM története miatt ezek felekezetek. Ha itt más van, akkor bizony gond van.',
					'options' => [
						'roman_catholic' => 'római katolikus',
						'greek_catholic' => 'görögkatolikus'
					]
				],
				'operator' => [
					'title' => 'Üzemeltető (szerzetesrend)'
				],
				'diocese' => [
					'title' => 'Egyházmegye (opcionális)',
					'help' => 'Csak akkor kell kitölteni, hogy ha a terület alapján nem tudjuk meghatározni az egyházmegyét, vagy ha valami miatt mégsem ahhoz az egyházmegyéhez tartozik: pl. a katonai ordinariátus templom mint egy enklávé.'					
				],
				'deanery' => [
					'title' => 'Espereskerület (opcionális)',
					'help' => 'Csak akkor kell kitölteni, hogy ha a terület alapján nem tudjuk meghatározni az esperekerületet, mert nincs feltérképezve. Még.'
				],
				'parish' => [
					'title' => 'Plébánia (ajánlott)',
					'help' => 'Egy-két esetben a térképen be van jelölve egy plébánia határa és akkor nem kell kitölteni ezt. De a legtöbb esetben ide kel a plébánia nevét pontosan beírni.'
				]
				
			]
		];		
		
		$this->form['fyi'] = [
			'title' => 'Információk',			
			'inputs' => [
				'description' => [
					'title' => 'Leírás (max. 255 karakter)',
					'type' => 'textarea',
					'help' => 'A templomról, stílusáról, történetéről lehet itt írni. Maximum 255 karakterben!'
				],
				'note' => [
					'title' => 'Megjegyzés (más térképszerkesztőknek)',
					'type' => 'textarea',
					'help' => 'Az Open Street Map-en munkálkodó más önkéntesek számára lehet itt nyilvános üzenetet "küldeni". Maximum 255 karakterben.'
				]
			]
		];
		
		$this->form['contact'] = [
			'title' => 'Kapcsolattartási adatok',
			'description' => 'Itt azokat az adatokat gyűjtjük, amik segítenek elérni ezt a helyet. Vagyis itt meg lehet adni olyan telefonszámot és címet, ami nem a templomé magáé, hanem pl. a helyi plébániájé. <br/>
			Egyéb social media cucc megadható az openstreetmap saját szerkesztői felületén.',
			'inputs' => [
				'phone' => [
					'title' => 'Telefonszám',
					'help' => 'Nyilvánosan elérhető telefonszám. Mobiltelenfonszámot csak az éritett személyes jóváhagyásával adjunk meg itt!<br/>Legyen benne az országhívü: +36 30 1231212'
				],
				'email' => [
					'title' => 'Email cím'
				],
				'website' => [
					'title' => 'Honlap'
				],
				'facebook' => [
					'title' => 'Facebook oldal'
				],
				'youtube' => [
					'title' => 'Youtube felhasználó/csatorna'
				]							
			]
		];
		
		$this->form['mail'] = [
			'title' => 'Levelezési cím',
			'description' => 'Ide lehet megadni a plébánia elérhetőségét például, ahol a leveleket tudják fogadni.',
			'inputs' => [
				'contact:country' => [
					'title' => 'Ország rövidítése (ha nem Magyarország)',
					'help' => 'Magyarország esetében nem kell kitölteni a magyar OSM szerkesztési hagyományoknak megfelelően. De minden más országban két betűs kód való ide.'
				],
				'contact:postcode' => [
					'title' => 'Irányítószám'
				],
				'contact:city' => [
					'title' => 'Település'
				],
				'contact:street' => [
					'title' => 'Közterület (utca/stb.)'
				],
				'contact:housenumber' => [
					'title' => 'Házszám'
				]								
			]
		];
   
		foreach( $this->form as $sid => $section) {
			foreach( $section['inputs'] as $key => $input ) {
				if ( isset($input['options']) )  {
					$array = $input['options'];
					if ( array_keys($array) !== range(0, count($array) - 1)) {
						$map = [];
						foreach ( $input['options'] as $value => $label ) {
							
							if( isset($this->autocomplete[$key][$value]) ) 
								$label = $label . " (".$value.", ".$this->autocomplete[$key][$value]." db)";
							else
								$label = $label . " (".$value.")";
						
							$map[] = [ 'label' => $label, 'value' => $value ];
						}
						$this->form[$sid]['inputs'][$key]['options'] = $map;
					   // Associative array
					   //echo 'Associative array';
					} else {
					   // sequential array
					   echo 'Sequential array';
					   var_dump($input['options']);
					}								
				} else if ( array_key_exists($key, $this->autocomplete) ) {
					
					foreach($this->autocomplete[$key] as $value => $count) {
						$this->form[$sid]['inputs'][$key]['options'][] = [
							'value' => $value,
							'label' => $value." (".$count." db)"
						];
					}					
				}
				
				if ( !isset($input['name'])) {
					$this->form[$sid]['inputs'][$key]['name'] = "osm[".$key."]";
				}
				if ( !isset($input['value'])) {
					$this->form[$sid]['inputs'][$key]['value'] = isset($osmtags->{$key}) ? $osmtags->{$key} : "";
				}
				if ( !isset($input['type'])) {
					$this->form[$sid]['inputs'][$key]['type'] = "input";
				}
				
				if ( !isset($input['id'])) {
					$this->form[$sid]['inputs'][$key]['id'] = $key;
				}
			}
		}
		
   }
   
   function findValidKeys() {
		$this->validKeys = [];
		
		// Minden kulcs legális, amit a form-ban előkészítettünk
		foreach($this->form as $section) {
			foreach($section['inputs'] as $key => $input) {
				$this->validKeys[] = $key;			
			}
		}
		
   
   }

   
   
	function prepareUpdatedOsmtags() {
		$original = (array) $this->osmtags;
		$updated = (array) $this->osmtags;
		$isUpdated = false;
		$changes = [];
		// Csak a lehetséges kulcsokat végig nézzük, hogy van-e hozzá új vagy törölt adat
		foreach($this->validKeys as $key) {
		
			// Ha be van küldve az érvényes cucc
			if( isset($this->input['osm'][$key]) ) {
				// Ha ez a kulcs nincs az eredeti OSM-ben ÉS most sem kapott értéket
				if ( trim($this->input['osm'][$key]) == '' AND !isset($this->osmtags->$key)) {
					// semmit nem teszünk
				}
				// Ha ez a kulcs nincs az eredeti OSM-ben DE most kap értéket
				if ( $this->input['osm'][$key] != '' AND !isset($this->osmtags->$key)) {
					$updated[$key] = $this->input['osm'][$key];
					$changes[] = "Hozzáadva: ".$key."<br/>";
					$isUpdated = true;
				}								
				// Ha kulcs ott az erdeti OSM-ben DE most üres értéket kap, vagyis törlendő
				if ( $this->input['osm'][$key] == '' AND isset($this->osmtags->$key)) {
					unset($updated[$key]);
					$changes[] = "Törölve: ".$key."<br/>";
					$isUpdated = true;
				}
				// Ha kulcs ott az eredeti OSM-ben ÉS most új értéket kap
				if ( $this->input['osm'][$key] != '' AND isset($this->osmtags->$key) AND $this->input['osm'][$key] != $this->osmtags->$key) {
					$updated[$key] = $this->input['osm'][$key];
					$changes[] = "Frissítve: ".$key."<br/>";
					$isUpdated = true;
				}
			}		
		}
		
		if(!$isUpdated) 
			return false;
		else {
			addMessage('Debug:<br/>'.implode('',$changes),'info');
			return $updated;		
		}
	}
	
	function prepareOSMEntityXml() {
		$this->osmEntity;
		$this->osmtagsToSave;
		
		unset($this->osmEntity->{$this->input['osmtype']}->tag);
		
		foreach ($this->osmtagsToSave as $k => $v) {
			$newTag = $this->osmEntity->{$this->input['osmtype']}->addChild('tag');
			$newTag->addAttribute('k', $k);
			$newTag->addAttribute('v', $v);
		}
		
		return true;			
	}
	
    function prepareAutocomplete($jsonOSMData) {
		$return = [];
		foreach($jsonOSMData->elements as $element) {
			foreach($element->tags as $key => $value) {
				if(!isset($return[$key])) $return[$key] = [];
				if(!isset($return[$key][$value])) $return[$key][$value] = 0;
				$return[$key][$value]++;
			}		
		}
		foreach($return as $key => $list) {
			ksort($list);
			$return[$key] = $list; //array_values(array_unique($list));
		}

		return $return;
		
	
	
	}
}
