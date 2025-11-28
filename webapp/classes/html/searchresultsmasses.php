<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class SearchResultsMasses extends Html {

    public function __construct() {
        parent::__construct();
        global $user, $config;

        $search = new \Search('masses', $_REQUEST);
        
        //TODO
        $zene = $_REQUEST['zene'];
        $kor = $_REQUEST['kor'];
               
        // Diocese filter
        $ehm = isset($_REQUEST['ehm']) ? $_REQUEST['ehm'] : 0;
        if ($ehm > 0) {
            $ehmnev = DB::table('egyhazmegye')->where('id',$ehm)->pluck('nev')[0];
            $search->addMust(["wildcard" => ['church.egyhazmegye.keyword' => $ehmnev ]]); 
            $search->filters[] = "Egyházmegye: " . htmlspecialchars($ehmnev) ." egyházmegye";                              
        }
            
        // nyelvek filter
        $tnyelv = isset($_REQUEST['tnyelv']) ? $_REQUEST['tnyelv'] : false;
        if($tnyelv == "h") $tnyelv = "hu";
        if ($tnyelv AND $tnyelv != '0') {
            $search->addMust(["term" => ['nyelvek' => $tnyelv ]]); 
            $search->filters[] = "Amelyik templomban van '" . htmlspecialchars($tnyelv) . "' nyelvű mise.";                              
        }
                
        $zeneT = array('g' => 'gitáros', 'o' => 'orgonás', 'cs' => 'csendes', 'na' => 'meghátorazatlan');
        $korT = array('csal' => 'családos', 'd' => 'diák', 'ifi' => 'ifjúsági', 'na' => 'meghátorazatlan');
        $ritusT = array('gor' => 'görögkatolikus', 'rom' => 'római katolikus', 'regi' => 'régi rítusú');
        $nyelvekT = array('h' => 'magyar', 'en' => 'angol', 'de' => 'német', 'it' => 'olasz', 'va' => 'latin', 'gr' => 'görög', 'sk' => 'szlovák', 'hr' => 'horvát', 'pl' => 'lengyel', 'si' => 'szlovén', 'ro' => 'román', 'fr' => 'francia', 'es' => 'spanyol');
        $tartalom = '';        

        // Main keyword search
        if (isset($_REQUEST['kulcsszo']) AND $_REQUEST['kulcsszo'] != '') {            
            $search->keyword($_REQUEST['kulcsszo']);
        }
    
        // Time range search
        $mikor = $_REQUEST['mikor'];
        $mikordatum = $_REQUEST['mikordatum'];
        if ($mikor != 'x')
            $mikordatum = $mikor;
        $mikor2 = isset($_REQUEST['mikor2']) ? $_REQUEST['mikor2'] : false;
        $mikorido = $_REQUEST['mikorido'];
        if ($mikor2 == 'de') {
            $hourFrom = "00:00";
            $hourTo = "11:59";
        } elseif ($mikor2 == 'du') {
            $hourFrom = "12:00";
            $hourTo = "23:59";
        } elseif ($mikor2 == 'x') {
            [$hourFrom, $hourTo] = explode('-', $mikorido);
            // normalize times to HH:MM (e.g. "8:00" -> "08:00")
            $hourFrom = trim($hourFrom);
            $hourTo = trim($hourTo);

            $fixTime = function ($t) {
                if ($t === '' || $t === null) return $t;
                $parts = explode(':', $t, 2);
                $h = isset($parts[0]) ? str_pad((int)$parts[0], 2, '0', STR_PAD_LEFT) : '00';
                $m = isset($parts[1]) ? str_pad((int)$parts[1], 2, '0', STR_PAD_LEFT) : '00';
                return $h . ':' . $m;
            };
            $hourFrom = $fixTime($hourFrom);
            $hourTo = $fixTime($hourTo);
        } else {
            $hourFrom = "00:00";
            $hourTo = "23:59";
        }                
        $search->timeRange($mikordatum."T".$hourFrom.":00", $mikordatum."T".$hourTo.":00");

        // Languages
        $nyelv = isset($_REQUEST['nyelv']) ? $_REQUEST['nyelv'] : false;        
        if (!empty($nyelv)) {
            $search->languages([$nyelv]);
        }

        // TODO
        if (!empty($zene)) {
            foreach ($zene as $z) {
                if (count($zene) < 3)
                    $tartalom.="$zeneT[$z], ";
            }
        }
        if (!empty($kor)) {
            foreach ($kor as $k) {
                if (count($kor) < 4)
                    $tartalom.="$korT[$k], ";
            }
        }

        // Ritus
        $ritus = isset($_REQUEST['ritus']) ? $_REQUEST['ritus'] : false;
        if (!empty($ritus)) {            
            $ritusMap = [
                'gor' => 'GREEK_CATHOLIC',
                'rom' => 'ROMAN_CATHOLIC',
                'regi' => 'TRADITIONAL'
            ];
            $search->rites([$ritusMap[$ritus]]);
        }

        // Exclude 'Igeliturgia' masses unless specifically requested
        $ige = isset($_REQUEST['ige']) ? $_REQUEST['ige'] : false;
        if (empty($ige)) {
            $search->notTitle('Igeliturgia'); 
        }

        $tartalom.="</span><br/>";

        $templomurlap = "<img src=/img/space.gif width=5 height=6><br><a href=\"/\" class=link><img src=/img/search.gif width=16 height=16 border=0 align=absmiddle hspace=2><b>Vissza a főoldali keresőhöz</b></a><br><img src=/img/space.gif width=5 height=6>";


        $min = isset($_REQUEST['min']) ? $_REQUEST['min'] : 0;       
		$leptet = isset($_REQUEST['leptet']) ? $_REQUEST['leptet'] : 25;		        
        $results = $search->getResults($min, $leptet, true);
                        
        if ($search->total != 0) {                   
            foreach ($results as &$result) {
                $result['church'] = \Eloquent\Church::find($result[0]->church_id)->toArray();                
            }
        }

        //Data for pagination
		$params = [];
		foreach( ['varos','tavolsag','hely','kulcsszo','gorog','tnyelv','espker','ehm',
            'mikor', 'mikordatum', 'mikor2','mikorido','nyelv','zene','kor','ritus','tnyelv'] as $param ) {
		
			if( isset($_REQUEST[$param]) AND $_REQUEST[$param] != ''  AND $_REQUEST[$param] != '0' ) {
				$params[$param] = $_REQUEST[$param];
			}
		}
		
        $params['q'] = 'SearchResultsMasses';
        $url = \Pagination::qe($params, '/?' );
        $this->pagination->set($search->total, $url );

        $this->filters = $search->getFilters();

        $this->alert = (new \ExternalApi\BreviarskApi())->LiturgicalAlert();

        $this->setTitle("Szentmise kereső");
        $this->tartalom = $tartalom;    
        $this->templomurlap = $templomurlap;
        $this->template = 'search/resultsmasses.twig';
        
        $this->results = $results;
    }

}
