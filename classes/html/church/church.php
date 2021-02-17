<?php

namespace Html\Church;

class Church extends \Html\Html {

    public function __construct($path) {
        global $user;

        $tid = $path[0];

        $church = \Eloquent\Church::find($tid);

        if(!$church) {
            throw new \Exception("Church with tid = '$tid' does not exist.");
        }
        if (!$church->McheckReadAccess($user)) {
            throw new \Exception("Read access denied to church tid = '$tid'");
        }

        if($church->ok == 'n') {
            addMessage('Ez a templom le van tiltva! Csak adminisztrátorok számára látható ez az oldal.', 'warning');
        } elseif($church->ok == 'f') {
            addMessage('Ez a templom áttekintésre vár. Csak adminisztrátorok számára látható ez az oldal.', 'warning');
        }

        $church->photos = $church->photos()->get();
                
        $church->append('liturgiatv')->get();
         /*
         * 
         */
        if( $church->lat != '' AND !isset($church->location->city)) {
            $church->MdownloadOSMBoundaries();
        }

        $church->MgetReligious_administration();
        
        if( count($church->neighbours) < 1 ) {
            $distance = new \Distance();        
            $distance->MupdateChurch($church);
        }        
  
        copyArrayToObject($church->toArray(), $this);
        $this->neighbours = $church->neighbours;
        
        
        if(isset($this->location->city))
            $this->setTitle($this->nev . " (" . $this->location->city['name'] . ")");
        else 
            $this->setTitle($this->nev);
        
        $this->updated = str_replace('-', '.', $this->frissites) . '.';

        //Miseidőpontok
        $misek = getMasses($tid);
        
        if ($user->checkRole('miserend') OR $user->checkRole('ehm:' . $this->religious_administration->diocese->id) OR ( isset($responsible) AND in_array($user->login, $responsible))) {
            $nev = " <a href='/templom/$tid/edit'><img src=/img/edit.gif align=absmiddle border=0 title='Szerkesztés/módosítás'></a> "
                    . "<a href='/templom/$tid/editschedule'><img src=/img/mise_edit.gif align=absmiddle border=0 title='mise módosítása'></a>";
            
            $allapotok = \Eloquent\Remark::where('church_id',$tid)->groupBy('allapot')->pluck('allapot')->toArray();            
            if (in_array('u', $allapotok))
                $nev.=" <a href=\"javascript:OpenScrollWindow('/templom/$tid/eszrevetelek',550,500);\"><img src=/img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";
            elseif (in_array('f', $allapotok))
                $nev.=" <a href=\"javascript:OpenScrollWindow('/templom/$tid/eszrevetelek',550,500);\"><img src=/img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";
            elseif (count($allapotok) > 0)
                $nev.=" <a href=\"javascript:OpenScrollWindow('/templom/$tid/eszrevetelek',550,500);\"><img src=/img/csomag1.gif title='Észrevételek!' align=absmiddle border=0></a> ";
            $this->nev .= $nev;
        }

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

        $data = \Html\Map::getGeoJsonDioceses();                
        $this->dioceseslayer = [];
        $this->dioceseslayer['geoJson'] = json_encode($data);        
        
        $this->miserend = $misek;
        $this->alert = LiturgicalDayAlert('html');
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
