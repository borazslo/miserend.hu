<?php

namespace Html\Church;

class Church extends \Html\Html {

    public function __construct($path) {
        global $user;

        $tid = $path[0];

        $church = getChurch($tid);
        foreach ($church as $k => $i)
            $$k = $i;

        $church = new \Church($tid);
        $church->getNeighbourChurches();
        $this->array2this($church);

        $this->setTitle($this->nev . " (" . $this->location->varos . ")");
        $this->updated = str_replace('-', '.', $this->frissites) . '.';

        $ma = date('Y-m-d');
        $ev = date('Y');
        $mostido = date('H:i:s');
        $mainap = date('w');
        if ($mainap == 0)
            $mainap = 7;
        $tolig = $nyariido . '!' . $teliido;
        $tolig = str_replace('-', '.', $tolig);
        $tolig = str_replace("$ev.", '', $tolig);
        $tolig = str_replace('!', ' - ', $tolig);
        if ($ma >= $nyariido and $ma <= $teliido) {
            $nyari = "<div align=center><span class=alap><b><font color=#B51A7E>nyári</font></b></span><br><span class=kicsi>($tolig)</span></div>";
            $teli = "<div align=center><span class=alap>téli</span></div>";
            $aktiv = 'ny';
        } else {
            $nyari = "<div align=center><span class=alap>nyári</span><br><span class=kicsi>($tolig)</span></div>";
            $teli = "<div align=center><span class=alap><b><font color=#B51A7E>téli</font></b></span></div>";
            $aktiv = 't';
        }

        //Miseidőpontok
        $misek = getMasses($tid);

        if ($user->checkRole('miserend') OR $user->checkRole('ehm:' . $egyhazmegye) OR ( isset($responsible) AND in_array($user->login, $responsible))) {
            $nev.=" <a href='/templom/$tid/edit'><img src=/img/edit.gif align=absmiddle border=0 title='Szerkesztés/módosítás'></a> "
                    . "<a href='/templom/$tid/editschedule'><img src=/img/mise_edit.gif align=absmiddle border=0 title='mise módosítása'></a>";

            $query = "select allapot from eszrevetelek where hol_id = '" . $tid . "' GROUP BY allapot ORDER BY allapot limit 5;";
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
        }

        /*
          $staticmap = "kepek/staticmaps/" . $tid . "_227x140.jpeg";
          if (file_exists($staticmap))
          $cim .= "<a href=\"http://www.openstreetmap.org/?mlat=$lat&mlon=$lng#map=15/$lat/$lng\" target=\"_blank\"><img src='/kepek/staticmaps/" . $tid . "_227x140.jpeg'></a>";
          else
          $cim .= "<br/>";
         */
        
        $this->addExtraMeta("og:image", "/kepek/templomok/" . $tid . "/" . $this->photos[0]->fajlnev);

        if ($user->checkFavorite($tid)) {
            $this->favorite = 1;
        }

        $variables = array(
            'nyari' => $nyari,
            'teli' => $teli,
            'miserend' => $misek,
            'napok' => array('', 'hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat', '<font color=#AC282B><b>vasárnap</b></font>'),
            'alert' => LiturgicalDayAlert('html'),
        );
        
        foreach ($variables as $key => $var) {
            $this->$key = $var;
        }
    }

    public function factory($path) {
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
