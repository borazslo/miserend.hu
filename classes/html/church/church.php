<?php

namespace Html\Church;

class Church extends \Html\Html {

    public function __construct($path) {
        global $user;

        $tid = $path[0];

        $church = \Eloquent\Church::find($tid);

        $church->closestNeighbour = $church->closestNeighbour()->first();
        $church->neighbourWithinDistance = $church->neighbourWithinDistance()->get();
        $church->photos = $church->photos()->get();

        $church->MgetReligious_administration();


        if ($church->osm AND $church->osm->enclosing->toArray() == array()) {
            $overpass = new \ExternalApi\OverpassApi();
            $overpass->updateEnclosing($church->osm);
            $church->load(osms);
            $church->osm = $church->osms()->first();
        }

        if (!$church->McheckReadAccess($user)) {
            throw new \Exception("Read access denied to church tid = '$tid'");
        }
        copyArrayToObject($church->toArray(), $this);
        $this->location = $church->location;

        $this->osm = $church->osm;

        $this->setTitle($this->nev . " (" . $this->location->city . ")");
        $this->updated = str_replace('-', '.', $this->frissites) . '.';

        //Miseidőpontok
        $misek = getMasses($tid);
        
        if ($user->checkRole('miserend') OR $user->checkRole('ehm:' . $this->religious_administration->diocese->id) OR ( isset($responsible) AND in_array($user->login, $responsible))) {
            $nev = " <a href='/templom/$tid/edit'><img src=/img/edit.gif align=absmiddle border=0 title='Szerkesztés/módosítás'></a> "
                    . "<a href='/templom/$tid/editschedule'><img src=/img/mise_edit.gif align=absmiddle border=0 title='mise módosítása'></a>";

            $query = "select allapot from remarks where church_id = '" . $tid . "' GROUP BY allapot ORDER BY allapot limit 5;";
            $result = mysql_query($query);
            $allapotok = array();
            while ($row = mysql_fetch_assoc($result)) {
                if ($row['allapot'])
                    $allapotok[] = $row['allapot'];
            }
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
