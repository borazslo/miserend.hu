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

				
		$this->loadOSMDataWithOSM();
		
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
			// Előkészítjük a FORM-ot, hogy megtaláljuk, mik a mezők amiket okés változtatni
			$this->prepareForm();
        }
		
        $this->preparePage();
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
		$osmtagsToSave = $this->prepareUpdatedOsmtags();
		if(!$osmtagsToSave) {
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

		if($versionID > 0)
			addMessage ('Közvelenül OSM adatokat is módosítottunk. Nagyon izgalmas. <a href="'.$messageurl.'">changeset/'.$changesetID.'</a>','success');
		

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

    function preparePage() {
		
		// A form
		
		
		return;
		
		$this->form['miseaktiv'] = array(
            'name' => 'church[miseaktiv]',
			'id' => 'miseaktiv',
			'type' => 'radio',
            'options' => array(
                '1' => 'Van rendszeresen mise',
                '0' => 'Nincs rendszeresen mise'
            ),
            'selected' => $this->church->miseaktiv
        );
		
		$this->form['ok'] = array(
            'name' => 'church[ok]',
            'options' => array(
                'i' => 'megjelenhet',
                'f' => 'áttekintésre vár',
                'n' => 'letiltva'
            ),
            'selected' => $this->church->ok
        );

		$this->form['frissites'] = array(
            'type' => 'checkbox',
            'name' => "church[frissites]",
            'value' => date('Y-m-d'),
            'checked' => false,
            'labelback' => 'Frissítsük a dátumot! (Utoljára frissítve: ' . date('Y.m.d.', strtotime($this->church->frissites)).')'
        );
		
		
		## OSM informations 
		if($this->church->osm) {
		
		# Disabilities
		# https://wiki.openstreetmap.org/wiki/Disabilitydescription
		# https://wiki.openstreetmap.org/wiki/How_to_map_for_the_needs_of_people_with_disabilities
		
		// https://wiki.openstreetmap.org/wiki/Key:wheelchair
		$this->form['osm']['wheelchair'] = array(
            'name' => 'church[osm][wheelchair]',
			'label' => 'Kerekesszékkel hozzáférhetőség:',
            'options' => array(
				'' => 'Nincs információ',
                'yes' => 'Akadálymentes',
                'limited' => 'Részben akadálymentes',				
                'no' => 'Egyáltalán nem akadálymentes'
            )
        );
		if(array_key_exists("wheelchair",$this->church->osm->tagList)) 
				$this->form['osm']['wheelchair']['selected'] = $this->church->osm->tagList["wheelchair"];
		
		// https://wiki.openstreetmap.org/wiki/Key:wheelchair:description
		$this->form['osm']['wheelchair:description'] = [
			'name' => 'church[osm][wheelchair:description]',
			'label' => 'Kiegészítés, ha szükséges'
			];
		if(array_key_exists("wheelchair:description",$this->church->osm->tagList)) 
				$this->form['osm']['wheelchair:description']['value'] = $this->church->osm->tagList["wheelchair:description"];
				
		// https://wiki.openstreetmap.org/wiki/Key:toilets:wheelchair
		$this->form['osm']['toilets:wheelchair'] = array(
            'name' => 'church[osm][toilets:wheelchair]',
			'label' => 'Akadálymentes mosdó:',
            'options' => array(
                '' => 'Nincs információ vagy nincs mosdó',
                'yes' => 'Kerekesszékkel hozzáférhető a mosdó',
                'no' => 'Kerekesszékkel nem hozzáférhető a mosdó'
            )
		);
		if(array_key_exists("toilets:wheelchair",$this->church->osm->tagList)) 
				$this->form['osm']['toilets:wheelchair']['selected'] = $this->church->osm->tagList["toilets:wheelchair"];
				
		// https://wiki.openstreetmap.org/wiki/Proposed_features/Hearing_loop		
		$this->form['osm']['hearing_loop'] = array(
            'name' => 'church[osm][hearing_loop]',
			'label' => 'Hallást segítő indukciós hurok:',
            'options' => array(
                '' => 'Nincs információ',
				'no' => 'Nincs indukciós hurok',
				'limited' => 'Van indukciós hurok, de tenni kell érte, hogy működjön',				
				'yes' => 'Van indukciós hurok'                
            )
		);
		if(array_key_exists("hearing_loop",$this->church->osm->tagList)) 
				$this->form['osm']['hearing_loop']['selected'] = $this->church->osm->tagList["hearing_loop"];				
				
		// https://wiki.openstreetmap.org/wiki/How_to_map_for_the_needs_of_people_with_disabilities
		$this->form['osm']['disabled:description'] = [
			'name' => 'church[osm][disabled:description]',
			'label' => 'További leírás bármilyen akadálymentesség kapcsán' 
			];
		if(array_key_exists("disabled:description",$this->church->osm->tagList)) 
				$this->form['osm']['disabled:description']['value'] = $this->church->osm->tagList["disabled:description"];		
		}
		// END of OSM informations
				
				
        $this->title = $this->church->fullName;
		
        
        for($i = 1; $i < 60; $i++) {
            $help = new \Help($i);
            if($help)
                $this->help[$i] = $help->html;
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
		
		$this->form[0] = [
			'title' => 'Elnevezés',
			'description' => 'Nem kötelező mindet kitölteni, de határon túli misézőhelyeknél figyeljünk erre!',
			'inputs' => []
		];
		$form = &$this->form[0]['inputs'];
		$form['name'] = array(
			'title' => 'Név (a helyi nyelven)',
			'type' => 'input',
            'name' => 'osm[name]',
			'value' => isset($osmtags->{"name"}) ? $osmtags->{"name"} : ""
        );
		$form['name:hu'] = array(
			'title' => 'Név magyarul (ha a helyi nyelv nem magyar)',
			'type' => 'input',
            'name' => 'osm[name:hu]',
			'value' => isset($osmtags->{"name:hu"}) ? $osmtags->{"name:hu"} : ""
        );		
		$form['alt_name'] = array(
			'title' => 'Közismert név (a helyi nyelven)',
			'type' => 'input',
            'name' => 'osm[alt_name]',
			'value' => isset($osmtags->{"alt_name"}) ? $osmtags->{"alt_name"} : ""
        );	
		$form['alt_name:hu'] = array(
			'title' => 'Közismert név (ha a helyi nyelv nem magyar)',
			'type' => 'input',
            'name' => 'osm[alt_name:hu]',
			'value' => isset($osmtags->{"alt_name:hu"}) ? $osmtags->{"alt_name:hu"} : ""
        );	
		$form['old_name'] = array(
			'title' => 'Régi elnevezés (helyi nyelven)',
			'type' => 'input',
            'name' => 'osm[old_name]',
			'value' => isset($osmtags->{"old_name"}) ? $osmtags->{"old_name"} : ""
        );
		
		$this->form[1] = [
			'title' => 'Elhelyezkedés',
			'description' => 'A cím adatoknál kifejezetten a templom adataira vagyunk kiváncsiak és nem a plébániáéra.',
			'inputs' => []
		];
		$form = &$this->form[1]['inputs'];
		$form['addr:country'] = array(
			'title' => 'Ország rövidítése (ha nem Magyarország)',
			'type' => 'input',
            'name' => 'osm[addr:country]',
			'value' => isset($osmtags->{"addr:country"}) ? $osmtags->{"addr:country"} : ""
        );			
		$form['addr:postcode'] = array(
			'title' => 'Irányítószám',
			'type' => 'input',
            'name' => 'osm[addr:postcode]',
			'value' => isset($osmtags->{"addr:postcode"}) ? $osmtags->{"addr:postcode"} : ""
        );	
		$form['addr:city'] = array(
			'title' => 'Település',
			'type' => 'input',
            'name' => 'osm[addr:city]',
			'value' => isset($osmtags->{"addr:city"}) ? $osmtags->{"addr:city"} : ""
        );	
		$form['addr:street'] = array(
			'title' => 'Közterület (utca/stb.)',
			'type' => 'input',
            'name' => 'osm[addr:street]',
			'value' => isset($osmtags->{"addr:street"}) ? $osmtags->{"addr:street"} : ""
        );	
		$form['addr:housenumber'] = array(
			'title' => 'Házszám',
			'type' => 'input',
            'name' => 'osm[addr:housenumber]',
			'value' => isset($osmtags->{"addr:housenumber"}) ? $osmtags->{"addr:housenumber"} : ""
        );	
		
   
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
				if ( $this->input['osm'][$key] == '' AND !isset($this->osmtags->$key)) {
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
		$this->osmtags;
		
		unset($this->osmEntity->{$this->input['osmtype']}->tag);
		
		foreach ($this->osmtags as $k => $v) {
			$newTag = $this->osmEntity->{$this->input['osmtype']}->addChild('tag');
			$newTag->addAttribute('k', $k);
			$newTag->addAttribute('v', $v);
		}
		
		return true;			
	}
	
   
}
