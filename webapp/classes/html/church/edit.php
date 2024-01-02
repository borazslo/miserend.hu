<?php

namespace Html\Church;

class Edit extends \Html\Html {

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

        $isForm = \Request::Text('submit');
        if ($isForm) {
            $this->modify();
        }
        $this->preparePage();
    }

    function modify() {
        if ($this->input['church']['id'] != $this->tid) {
            throw new \Exception("Gond van a módosítandó templom azonosítójával.");
        }

        $allowedFields = ['adminmegj', 'kontaktmail', 'nev',
            'ismertnev', 'orszag', 'megye', 'varos', 'cim', 'megkozelites',
            'egyhazmegye', 'espereskerulet', 'plebania', 'pleb_eml', 
            'megjegyzes', 'miseaktiv', 'misemegj', 'leiras', 'ok', 'frissites',
            'lat','lon'];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $this->input['church'])) {
                $this->church->$field = $this->input['church'][$field];
            }
        }

		
		if(isset($this->input['church']['osm'])) {
			$allowedOSMFields = ['wheelchair', 'wheelchair:description','toilets:wheelchair','toilets','hearing_loop','disabled:description'];		
			foreach($allowedOSMFields as $field) {
				if (array_key_exists($field, $this->input['church']['osm'])) {
					//echo $field." = ".$this->input['church']['osm'][$field];
					
					$args = [
						'osmtype' => $this->church->osmtype, 
						'osmid' => $this->church->osmid,
						'name' => $field 
						];				
					$osmtag = \Eloquent\OSMTag::firstOrNew($args);
					$osmtag->value = $this->input['church']['osm'][$field];
					$osmtag->save();

				}
			
			}
			/* save OSM fields */
			$this->church->osm->upload();
		}
		
       
        global $user;
        $this->church->log .= "\nMod: " . $user->login . " (" . date('Y-m-d H:i:s') . ")";
        
        /* Valamiért a writeAcess nem az igazi és mivel nincs a tálában ezért kiakadt...*/
        $model = $this->church;
        foreach ($model->getAttributes() as $key => $value) {
        if(!in_array($key, array_keys($model->getOriginal())))
            unset($model->$key);
        }
        $model->save();

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

        global $user;
        if ($user->checkRole('miserend')) {
            $this->addFormNewHolder();
        }

        $this->addFormAdministrative();
        $this->addFormReligiousAdministration();
		
		$this->church->osm;
		if($this->church->osm) $this->church->osm->updateFromOverpass();

		
		$this->form['misemegj'] = array(
			'class' => 'tinymce',
            'name' => 'church[misemegj]',
			'type' => 'textarea',
			'value' => $this->church->misemegj
        );
		
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

    function addFormAdministrative() {
        $options = [0 => 'Válassz/Nem tudom'];
        $countries = \Illuminate\Database\Capsule\Manager::table('orszagok')
                        ->select('id', 'nev')
                        ->orderBy('nev')->get();
        foreach ($countries as $selectibleCountry) {
            $options[$selectibleCountry->id] = $selectibleCountry->nev;
        }
        $this->form['country'] = array(
            'type' => 'select',
            'name' => 'church[orszag]',
            'id' => 'selectOrszag',
            'options' => $options,
            'selected' => $this->church->orszag
        );

        foreach ($countries as $selectibleCountry) {
            $options = [0 => 'Válassz/Nem tudom'];
            $counties = \Illuminate\Database\Capsule\Manager::table('megye')
                            ->select('id', 'megyenev', 'orszag')
                            ->where('orszag', $selectibleCountry->id)
                            ->orderBy('megyenev')->get();

            foreach ($counties as $selectibleCounty) {
                $options[$selectibleCounty->id] = $selectibleCounty->megyenev . " megye";
                $allCounties[] = $selectibleCounty;
            }

            $this->form['counties'][$selectibleCountry->id] = array(
                'type' => 'select',
                'name' => 'church[megye]',
                'id' => 'selectMegyeCountry' . $selectibleCountry->id,
                'class' => 'selectMegyeCountry',
                'data' => $selectibleCountry->id,
                'options' => $options,
                'selected' => $this->church->megye
            );
            if ($selectibleCountry->id == $this->church->orszag) {
                $this->form['counties'][$selectibleCountry->id]['style'] = 'display: inline';
            } else {
                $this->form['counties'][$selectibleCountry->id]['style'] = 'display: none';
                $this->form['counties'][$selectibleCountry->id]['disabled'] = 'disabled';
            }

            if (count($counties) < 1) {
                $extra = new \stdClass();
                $extra->id = 0;
                $extra->megyenev = '(Nincs megadva)';
                $extra->orszag = $selectibleCountry->id;
                $allCounties[] = $extra;
            }
        }

        foreach ($allCounties as $selectibleCounty) {
            $options = [0 => 'Válassz/Nem tudom'];
            $cities = \Illuminate\Database\Capsule\Manager::table('varosok')
                            ->select('id', 'nev')
                            ->where('orszag', $selectibleCounty->orszag)
                            ->where('megye_id', $selectibleCounty->id)
                            ->orderBy('nev')->get();
            foreach ($cities as $selectibleCity) {
                $options[$selectibleCity->nev] = $selectibleCity->nev;
            }
            $this->form['cities'][$selectibleCounty->orszag . "-" . $selectibleCounty->id] = array(
                'type' => 'select',
                'name' => 'church[varos]',
                'id' => 'selectVarosCounty' . $selectibleCounty->orszag . "-" . $selectibleCounty->id,
                'class' => 'selectVarosCounty',
                'options' => $options,
                'selected' => $this->church->varos
            );
            if ($selectibleCounty->id == $this->church->megye AND $this->church->orszag == $selectibleCounty->orszag) {
                $this->form['cities'][$selectibleCounty->orszag . "-" . $selectibleCounty->id]['style'] = 'display: inline';
            } else {
                $this->form['cities'][$selectibleCounty->orszag . "-" . $selectibleCounty->id]['style'] = 'display: none';
                $this->form['cities'][$selectibleCounty->orszag . "-" . $selectibleCounty->id]['disabled'] = 'disabled';
            }
        }
    }

    function addFormReligiousAdministration() {
        $selected = ['diocese' => $this->church->egyhazmegye, 'deanery' => $this->church->espereskerulet];
        $selectReligiousAdministration = \Form::religiousAdministrationSelection($selected);
        $this->form['dioceses'] = $selectReligiousAdministration['dioceses'];
        $this->form['deaneries'] = $selectReligiousAdministration['deaneries'];
    }

    function addFormNewHolder() {
        $options = [];
        $users = \Illuminate\Database\Capsule\Manager::table('user')
                        ->select('login', 'nev', 'uid')
                        ->orderByRaw("CASE WHEN lastlogin > '" . date('Y-m-d H:i:s', strtotime('-6 month')) . "'     THEN 1 ELSE 0 END desc")
                        ->orderBy('login')->get();
        foreach ($users as $selectibleUser) {
            $options[$selectibleUser->uid] = $selectibleUser->login." (".$selectibleUser->nev.")";
        }
        $this->form['holder_uid'] = array(
            'type' => 'select',
            'name' => 'uid',
            'id' => 'combobox',
            'options' => $options
        );
        $this->form['holder_decription'] = array(
            'type' => 'text',
            'name' => 'description',
            'placeholder' => 'Megjegyzés / jogosultság / kapcsolat a templommal.'
        );        
        $this->form['holder_access'] = array(
            'type' => 'hidden',
            'name' => 'access',
            'value'=> 'allowed'
        );        
    }

}
