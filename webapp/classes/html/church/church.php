<?php

namespace Html\Church;

class Church extends \Html\Html {

    public function __construct($path) {
        global $user;

        $tid = $path[0];

        $church = \Eloquent\Church::find($tid);
        if(!$church AND $user->checkRole('miserend')) {
            $church = \Eloquent\Church::withTrashed()->find($tid);
            if($church)
                addMessage ('Ez a templom törölve van. Nem létezik. Elhunyt. Vége.','danger');            
        }
            
        if(!$church) {
            throw new \Exception("Church with tid = '$tid' does not exist.");
        }
        $church = $church->append(['readAccess','writeAccess','accessibility']);
        
        if (!$church->readAccess) {
            throw new \Exception("Read access denied to church tid = '$tid'");
        }

        if($church->ok == 'n') {
            addMessage('Ez a templom le van tiltva! Csak adminisztrátorok számára látható ez az oldal.', 'warning');
        } elseif($church->ok == 'f') {
            addMessage('Ez a templom áttekintésre vár. Csak adminisztrátorok számára látható ez az oldal.', 'warning');
        }

        $church->photos = $church->photos()->get();
		
				
		$church->kozossegek = $church->kozossegek;
       $church->confessions = $church->confessions; 

        $church->adorations = $church->adorations()
            ->where('date', '>=', date('Y-m-d'))
            ->orderBy('date', 'ASC')
            ->orderBy('starttime', 'ASC')
            ->limit(5)
            ->get()
            ->toArray();
        		   
		global $_honapok;
		$this->_honapok = $_honapok;
		
         /*
         * 
         */
        if( $church->lat != '' AND !isset($church->location->city)) {
            $church->MdownloadOSMBoundaries();
        }

        $church->MgetReligious_administration();
        
        if( count($church->neighbours) < 1 ) {
           // $distance = new \Distance();        
           // $distance->MupdateChurch($church);
        }        
  
								
        copyArrayToObject($church->toArray(), $this);
		
        global $_tidsToWorkWith;
        if(in_array($this->id, $_tidsToWorkWith)) {
            $this->hasWorkAccess = false;
        } else {
            $this->hasWorkAccess = true;
        }
		$this->church = ['hasPendingSuggestionPackage' => $church->hasPendingSuggestionPackage, 'remarksicon' => $church->remarksicon, 'id' => $church->id]; // A church/_adminlinks.twig számára kell ez. Bocsi.
        $this->neighbours = $church->neighbours;
        
        
        if(isset($this->location->city))
            $this->setTitle($this->names[0] . " (" . $this->location->city['name'] . ")");
        else 
            $this->setTitle($this->names[0]);
        
        $this->updated = str_replace('-', '.', $this->frissites) . '.';

        /*
          $staticmap = "kepek/staticmaps/" . $tid . "_227x140.jpeg";
          if (file_exists($staticmap))
          $cim .= "<a href=\"http://www.openstreetmap.org/?mlat=$lat&mlon=$lng#map=15/$lat/$lng\" target=\"_blank\"><img src='/kepek/staticmaps/" . $tid . "_227x140.jpeg'></a>";
          else
          $cim .= "<br/>";
         */
                
        $this->photos;
        if (isset($this->photos[0])) {
            $this->addExtraMeta("og:image", "/kepek/templomok/" . $tid . "/" . $this->photos[0]->fajlnev);
        }

        if ($user->checkFavorite($tid)) {
            $this->favorite = 1;
        }
                        
		$this->alert = (new \ExternalApi\BreviarskApi())->LiturgicalAlert();
        
        $this->isChurchHolder = $user->getHoldingData($this->id);                
		

		
    }

    static function factory($path) {
        if (isset($path[1])) {
            $urlmapping = ['new' => 'edit'];
            if (array_key_exists($path[1], $urlmapping)) {
                $class = $urlmapping[$path[1]];
            } else {
                $class = $path[1];
            }
            $className = "\Html\\Church\\" . $class;
        } else {
            $className = "\Html\\Church\\Church";
        }
        return new $className($path);
    }

}
