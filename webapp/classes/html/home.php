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
            'class' => 'keresourlap');            						
        $searchform['ehm']['options'][0] = 'mindegy';
        
        $egyhmegyes = DB::table('egyhazmegye')
                    ->select('id','nev')
                    ->where('ok','i')
                    ->orderBy('sorrend')
                    ->get();
                    foreach ($egyhmegyes as $egyhmegye) {
                        $searchform['ehm']['options'][$egyhmegye->id] = $egyhmegye->nev;
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
		try {
            $this->alert = (new \ExternalApi\NapilelkibatyuApi())->liturgicalAlert();            
        } catch (\Exception $e) {
            addMessage('Nem sikerült a Napi Lelki Batyuból megtudni, hogy van-e ma különleges ünnep.', "warning");
        }
		
							
		// Adminok számára "dashboard"
		if ( $user->checkrole('miserend') ) {
		
			$this->admindashboard = [];
		
			$this->admindashboard['holders'] = \Eloquent\ChurchHolder::where('updated_at', '>', $user->lastlogin)
                             ->orWhere('status', 'asked')
                             ->orderBy('created_at', 'asc')
                             ->get();

            $this->admindashboard['suggestion_packages'] = \Eloquent\CalSuggestionPackage::where('updated_at', '>', $user->lastlogin)
                             ->orWhere('state', 'PENDING')
                             ->orderBy('created_at', 'asc')
                             ->get();
                                              
		}
        
    }

}
