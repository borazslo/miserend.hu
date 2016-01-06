<?php

namespace Html;

class DioceseCatalogue extends Html {

    public function __construct($path) {
        global $user;

        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod megnézni a templomok listáját.');
        }

        $txt = "<form method=post>"
                . "<select name=ehm class=urlap>";
        $query = "select id,nev from egyhazmegye";
        $lekerdez = mysql_query($query);
        while (list($id, $nev) = mysql_fetch_row($lekerdez)) {
            $txt.="<option value=$id";
            if ($id == $_REQUEST['ehm'])
                $txt.=" selected";
            $txt.=">$nev</option>";
        }
        $txt.="</select><input type=submit value=Mutat class=urlap></form>";

        $ehm = $_REQUEST['ehm'];
        if (is_numeric($ehm) AND $ehm > 0) {

            list($ehmnev) = mysql_fetch_row(mysql_query("select nev from egyhazmegye where id='$ehm'"));
            $txt.="<h2>$ehmnev egyházmegye</h2>";

            $query = "select templomok.id,templomok.nev,templomok.varos,espereskerulet.nev from espereskerulet, templomok where espereskerulet.id=templomok.espereskerulet and templomok.egyhazmegye=$ehm order by templomok.espereskerulet, templomok.varos";
            if (!$lekerdez = mysql_query($query))
                echo "<br>HIBA!<br>$query<br>" . mysql_error();
            $a = 0;
            $excel = '';
            while (list($tid, $tnev, $varos, $espker) = mysql_fetch_row($lekerdez)) {
                $a++;
                if (!isset($espkerell) OR $espker != $espkerell) {
                    $txt.= "<br><h3>$espker espereskerület</h3>";
                    $espkerell = $espker;
                }
                $txt.= "$a. [$tid] $tnev ($varos)<br>";
                $excel.="\n$tid;$tnev;$varos;$espker";
            }
            $txt.="<br><br><span class=alap>Az alábbi szöveget kimásolva excelbe importálható.<br>Excelben: Adatok / Szövegből oszlopok -> táblázattá alakítható</span><br><textarea class=urlap cols=60 rows=20>$excel</textarea>";
        }


        $this->title = "Egyházmegyei templomok listája";
        $this->content = $txt;
        $this->template = 'layout.twig';
    }

}
