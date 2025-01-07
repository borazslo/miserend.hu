<?php


namespace Html;
//use Illuminate\Database\DatabaseManager as DB;
//use \Illuminate\Support\Facades\DB as DB;
use Illuminate\Database\Capsule\Manager as DB;

class Home extends Html {



    public function __construct() {
        global $user, $config;

        $attributes = unserialize(ATTRIBUTES);
        $languages = unserialize(LANGUAGES);

        $ma = date('Y-m-d');
        $holnap = date('Y-m-d', (time() + 86400));
        $mikor = '8:00-19:00';

        
        $espkers = DB::table('espereskerulet')
                    ->select('id','ehm','nev')
                    ->get();
            
        foreach ($espkers as $espker) {
            $espkerT[$espker->ehm][$espker->id] = $espker->nev;# code...
        }
    
        //MISEREND űRLAP	
        $searchform = array(
            'kulcsszo' => array(
                'name' => "kulcsszo",
                'id' => 'keyword',
                'size' => 20,
                'class' => 'keresourlap',
                'placeholder' => 'név, település, kulcsszó'),
            'varos' => array(
                'name' => "varos",
                'size' => 20,
                'id' => 'varos',
                'class' => 'keresourlap',
                'placeholder' => 'település'),
            'hely' => array(
                'name' => "hely",
                'size' => 20,
                'id' => 'varos',
                'class' => 'keresourlap'),
            'tavolsag' => array(
                'name' => "tavolsag",
                'size' => 1,
                'id' => 'tavolsag',
                'class' => 'keresourlap',
                'value' => 4)
        );


        $searchform['ehm'] = array(
            'name' => "ehm",
            'class' => 'keresourlap',
            'onChange' => "
						var a = document.getElementsByName('espker');	
						for (index = 0; index < a.length; ++index) {
						    console.log(a[index]);
						    a[index].style.display = 'none';
						}

						if(this.value!=0) {	
							document.getElementById('espkerlabel').style.display='inline';
							document.getElementById('ehm'+this.value).style.display='inline';

						} else {
							document.getElementById('espkerlabel').style.display='none';
						}");
        $searchform['ehm']['options'][0] = 'mindegy';
        
        $egyhmegyes = DB::table('egyhazmegye')
                    ->select('id','nev')
                    ->where('ok','i')
                    ->orderBy('sorrend')
                    ->get();
                    foreach ($egyhmegyes as $egyhmegye) {
                        $searchform['ehm']['options'][$egyhmegye->id] = $egyhmegye->nev;
                    }
        
        foreach ($espkerT as $ehm => $espker) {
            $searchform['espker'][$ehm] = array(
                'name' => "espker",
                'id' => "ehm" . $ehm,
                'style' => "display:none",
                'class' => 'keresourlap');
            $searchform['espker'][$ehm]['options'][0] = 'mindegy';
            if (is_array($espker)) {
                foreach ($espker as $espid => $espnev) {
                    $searchform['espker'][$ehm]['options'][$espid] = $espnev;
                }
            }
        }

        $searchform['gorog'] = array(
            'type' => 'checkbox',
            'name' => "gorog",
            'id' => "gorog",
            'class' => "keresourlap",
            'value' => "gorog"
        );

        $searchform['tnyelv'] = array(
            'name' => "tnyelv",
            'id' => "tnyelv",
            'class' => 'keresourlap',
            'options' => array(0 => 'bármilyen')
        );
        foreach ($languages as $abbrev => $language) {
            $searchform['tnyelv']['options'][$abbrev] = $language['name'];
        }

        //Mikor
        $mainap = date('w');
        if ($mainap == 0)
            $vasarnap = $ma;
        else {
            $kulonbseg = 7 - $mainap;
            $vasarnap = date('Y-m-d', (time() + (86400 * $kulonbseg)));
        }
        $searchform['mikor'] = array(
            'name' => "mikor",
            'id' => "mikor",
            'class' => 'keresourlap',
            'onChange' => "if (this.value == 'x') $('#md').show().focus(); else $('#md').hide();",
            'options' => array($vasarnap => 'vasárnap', $ma => 'ma', $holnap => 'holnap', 'x' => 'adott napon:')
        );
        $searchform['mikordatum'] = array(
            'name' => "mikordatum",
            'id' => "md",
            'style' => "display:none",
            'class' => "keresourlap datepicker",
            'size' => "10",
            'value' => $ma
        );
        $searchform['mikor2'] = array(
            'name' => "mikor2",
            'id' => "mikor2",
            'style' => "margin-top:12px",
            'class' => 'keresourlap',
            'onChange' => "
						if(this.value == 'x') {
							document.getElementById('md2').style.display='inline'; 
							alert('FIGYELEM! Fontos a formátum!');} 
						else {document.getElementById('md2').style.display='none';}",
            'options' => array(0 => 'egész nap', 'de' => 'délelőtt', 'du' => 'délután', 'x' => 'adott időben:')
        );
        $searchform['mikorido'] = array(
            'name' => "mikorido",
            'id' => "md2",
            'style' => "display:none;",
            'class' => "keresourlap",
            'size' => "7",
            'value' => $mikor
        );

        //languages
        $searchform['nyelv'] = array(
            'name' => "nyelv",
            'id' => "nyelv",
            'class' => 'keresourlap',
            'options' => array(0 => 'mindegy')
        );
        foreach ($languages as $abbrev => $language) {
            $searchform['nyelv']['options'][$abbrev] = $language['name'];
        }

        //group music
        $music['na'] = '<i>meghatározatlan</i>';
        foreach ($attributes as $abbrev => $attribute) {
            if ($attribute['group'] == 'music')
                $music = array($abbrev => $attribute['name']) + $music;
        }
        foreach ($music as $value => $label) {
            $searchform['zene'][] = array(
                'type' => 'checkbox',
                'name' => "zene[]",
                'class' => "keresourlap",
                'value' => $value,
                'labelback' => $label,
                'checked' => true,
            );
        }

        //group age
        $age['na'] = '<i>meghatározatlan</i>';
        foreach ($attributes as $abbrev => $attribute) {
            if ($attribute['group'] == 'age')
                $age = array($abbrev => $attribute['name']) + $age;
        }
        foreach ($age as $value => $label) {
            $searchform['kor'][] = array(
                'type' => 'checkbox',
                'name' => "kor[]",
                'class' => "keresourlap",
                'value' => $value,
                'checked' => true,
                'labelback' => $label,
            );
        }

        //group rite
        $searchform['ritus'] = array(
            'name' => "ritus",
            'id' => "ritus",
            'class' => 'keresourlap',
            'options' => array(0 => 'mindegy')
        );
        foreach ($attributes as $abbrev => $attribute) {
            if ($attribute['group'] == 'liturgy' AND isset($attribute['isitmass']))
                $searchform['ritus']['options'][$abbrev] = $attribute['name'];
        }

        $searchform['ige'] = array(
            'type' => 'checkbox',
            'name' => "liturgy[]",
            'id' => "liturgy",
            'checked' => true,
            'class' => "keresourlap",
            'value' => "ige"
        );

        $this->photo = \Eloquent\Photo::big()->vertical()->where('flag', 'i')->orderbyRaw('RAND()')->first();
        if($this->photo->church) //TODO: Van, hogy a random képhez nem is tartozik templom. Valami régi hiba miatt.
            $this->photo->church->location;

        $this->favorites = $user->getFavorites();
        $this->searchform = $searchform;
        		
		$this->alert = (new \ExternalApi\BreviarskApi())->LiturgicalAlert();
							
		// Adminok számára "dashboard"
		if ( $user->checkrole('miserend') ) {
		
			$this->admindashboard = [];
		
			$this->admindashboard['holders'] = \Eloquent\ChurchHolder::where('updated_at', '>', $user->lastlogin)
                             ->orWhere('status', 'asked')
                             ->get();
		}
        
    }

}
