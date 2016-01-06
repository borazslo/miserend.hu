<?php

namespace Html\Church;

use Illuminate\Database\Capsule\Manager as DB;

class Catalogue extends \Html\Html {

    public function __construct() {
        global $user;

        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod megnézni a templomok listáját.');
        }

        $egyhazmegye = (isset($_REQUEST['egyhazmegye']) ? $_REQUEST['egyhazmegye'] : false);

        if ($egyhazmegye == '0')
            $egyhazmegye = 'mind';
        $kulcsszo = (isset($_REQUEST['kkulcsszo']) ? $_REQUEST['kkulcsszo'] : false);
        $allapot = (isset($_REQUEST['allapot']) ? $_REQUEST['allapot'] : false);

        $sort = (isset($_REQUEST['sort']) ? $_REQUEST['sort'] : false);
        if (!$sort)
            $sort = 'moddatum desc';

        $min = (isset($_REQUEST['min']) ? $_REQUEST['min'] : false);
        if (!is_numeric($min) or $min < 0 or ! $min)
            $min = 0;

        $leptet = (isset($_REQUEST['leptet']) ? $_REQUEST['leptet'] : false);
        if (!$leptet)
            $leptet = 50;

        $next = $min + $leptet;
        $prev = $min - $leptet;

        $query_kat = "select id,ehm,nev from espereskerulet";
        $lekerdez_kat = mysql_query($query_kat);
        while (list($esid, $eshm, $esnev) = mysql_fetch_row($lekerdez_kat)) {
            $espkerT[$eshm][$esid] = $esnev;
        }

        $kiir = "<span class=kiscim>A lista szűkíthető egyházmegyék, kulcsszó és állapot alapján:</span><br>";
        $csakpriv = 'mind';
        $ehmmindkiir = '<option value=mind>Mind</option>';
        $query_kat = "select id,nev,felelos,csakez from egyhazmegye where ok='i' order by sorrend";
        $lekerdez_kat = mysql_query($query_kat);
        while (list($kid, $knev, $kfelelos, $kcsakez) = mysql_fetch_row($lekerdez_kat)) {
            if ($kfelelos == $user->login) {
                $ehmT['priv'][$kid] = $knev;
                if ($kcsakez == 'i') {
                    $csakpriv = 'priv';
                    $ehmmindkiir = '';
                } else
                    $csakpriv = 'mind';
                if (empty($egyhazmegye))
                    $egyhazmegye = "$kid-0";
            }
            $ehmT['mind'][$kid] = $knev;
        }
        if (empty($egyhazmegye))
            $egyhazmegye = 'mind';

        $kiir.="\n<form method=post>";
        $kiir.="\n<select name=egyhazmegye class=urlap>";
        $kiir.=$ehmmindkiir;
        foreach ($ehmT[$csakpriv] as $kid => $knev) {
            $kiir.="<option value=$kid-0";
            if ($egyhazmegye == "$kid-0")
                $kiir.=" selected";
            $kiir.=">";
            $kiir.="$knev</option>";
            if (isset($espkerT[$kid]) AND is_array($espkerT[$kid])) {
                foreach ($espkerT[$kid] as $esid => $esnev) {
                    $kiir.="<option value=$kid-$esid";
                    if ($egyhazmegye == "$kid-$esid")
                        $kiir.=" selected";
                    $kiir.="> -> $esnev espker.</option>";
                }
            }
        }
        $kiir.="</select>";

        $kiir.="\n <input type=text name=kkulcsszo value='$kulcsszo' class=urlap size=20>";

        //Állapot szerinti szűrés
        $kiir.="\n <select name=allapot class=urlap><option value=0>Mind</option><option value=i";
        if ($allapot == 'i')
            $kiir.=" selected";
        $kiir.=">csak engedélyezett templomok</option><option value=f";
        if ($allapot == 'f')
            $kiir.=" selected";
        $kiir.=">áttekintésre várók</option><option value=n";
        if ($allapot == 'n')
            $kiir.=" selected";
        $kiir.=">letiltott templomok</option><option value=e";
        if ($allapot == 'e')
            $kiir.=" selected";
        $kiir.=">észrevételezett templomok</option><option value=ef";
        if ($allapot == 'ef')
            $kiir.=" selected";
        $kiir.=">javítás alatt lévő templomok</option>";
        //$kiir.="<opton value=m";
        //	if($allapot=='m') $kiir.=" selected";
        //	$kiir.=">miserend nélküli templomok</option>";
        $kiir.="</select>";

        $kiir.="\n<br><span class=alap>rendezés: </span><select name=sort class=urlap> ";
        $sortT['utolsó módosítás'] = 'moddatum DESC';
        $sortT['település'] = 't.varos';
        $sortT['templomnév'] = 't.nev';
        $sortT['utolsó észrevétel'] = 'e.datum DESC';
        foreach ($sortT as $kulcs => $ertek) {
            $kiir.="<option value='$ertek'";
            if ($ertek == $sort)
                $kiir.=' selected';
            $kiir.=">$kulcs</option>";
        }
        $kiir.="\n</select><input type=submit value=Lista class=urlap></form>";

        $form = $kiir;

        if ($egyhazmegye != 'mind' and isset($egyhazmegye)) {
            $ehmT = explode('-', $egyhazmegye);
            if ($ehmT[1] == '0')
                $feltetelT[] = "egyhazmegye='$ehmT[0]'";
            else
                $feltetelT[] = "espereskerulet='$ehmT[1]'";
        }
        if (!empty($kulcsszo))
            $feltetelT[] = "(nev like '%$kulcsszo%' or varos like '%$kulcsszo%' or ismertnev like '%$kulcsszo%' or letrehozta like '%$kulcsszo%')";
        $wallapot = '';
        if (!empty($allapot)) {
            if ($allapot == 'e')
                $wallapot = "e1.allapot = 'u'";
            elseif ($allapot == 'ef')
                $wallapot = "e1.allapot = 'f'";
            else
                $feltetelT[] = "ok='$allapot'";
        }
        if (isset($feltetelT) AND is_array($feltetelT))
            $feltetel = ' where ' . implode(' and ', $feltetelT);

        //Misék lekérdezése
        $querym = "select distinct(tid) from misek where torolte=''";
        if (!$lekerdezm = mysql_query($querym))
            echo "HIBA!<br>$querym<br>" . mysql_error();
        while (list($templom) = mysql_fetch_row($lekerdezm)) {
            $vanmiseT[$templom] = true;
        }

        if ($wallapot != '')
            $wallapot .= " AND ";
        $query = "SELECT t.id,t.nev,ismertnev,varos,ok,miseaktiv
	FROM templomok as t ";

        if ($sort == 'e.datum DESC' OR $wallapot != '') {
            $query .= "
		LEFT JOIN remarks as e ON e.id = ( 
			SELECT id  FROM remarks as e1
				WHERE " . $wallapot . " e1.church_id = t.id
	 			ORDER BY CASE `allapot`
	     				WHEN 'u' THEN 1
	     				WHEN 'f' THEN 2
	     				WHEN 'j' THEN 3
	     				END, created_at DESC 
			 	LIMIT 1
			) AND e.church_id = t.id 
		";
        }
        if (isset($feltetel))
            $query .= $feltetel . " ";
        if (isset($feltetel) AND $feltetel != '' AND $wallapot != '')
            $query .= " AND ";
        elseif ($wallapot != '')
            $query .= " WHERE ";
        if ($wallapot != '')
            $query .= " e.id IS NOT NULL ";
        $query .= "ORDER BY " . $sort . " ";

        $lekerdez = mysql_query($query);
        $mennyi = mysql_num_rows($lekerdez);
        if ($mennyi > $leptet) {
            $query.=" limit $min,$leptet";
            $lekerdez = mysql_query($query);
        }
        $kezd = $min + 1;
        $veg = $min + $leptet;
        if ($veg > $mennyi)
            $veg = $mennyi;
        if ($mennyi > 0) {
            $lapozo = '';
            $sum = "<span class=alap>Összesen: $mennyi találat<br>Listázás: $kezd - $veg</span><br><br>";
            if ($min > 0) {
                $lapozo.="\n<form method=post><input type=hidden name=kkulcsszo value='" . $kulcsszo . "'><input type=hidden name=egyhazmegye value=$egyhazmegye><input type=hidden name=min value=$prev><input type=hidden name=sort value='$sort'>";
                $lapozo.="\n<input type=submit value=Előző class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
            }
            if ($mennyi > $min + $leptet) {
                $lapozo.="\n<form method=post><input type=hidden name=kkulcsszo value='" . $kulcsszo . "'><input type=hidden name=egyhazmegye value=$egyhazmegye><input type=hidden name=min value=$next><input type=hidden name=sort value='$sort'>";
                $lapozo.="\n<input type=submit value=Következő class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
            }
        } else
            $sum = "<span class=alap>Jelenleg nincs módosítható templom az adatbázisban.</span>";

        $churches = array();
        while ($church = mysql_fetch_assoc($lekerdez)) {
            //list($tid,$tnev,$tismert,$tvaros,$tok,$teszrevetel,$miseaktiv)=mysql_fetch_row($lekerdez)) {

            $tid = $church['id'];

            $ch = \Eloquent\Church::find($tid);
            $jelzes = $ch->remarksStatus['html'];

            if (!isset($vanmiseT[$tid]) AND $church['miseaktiv'] == 1) {
                $jelzes.="<img src=/img/lampa.gif title='Nincs hozzá mise!' align=absmiddle> ";
            }
            if ($church['ok'] == 'n')
                $jelzes.="<img src=/img/tilos.gif title='Nem engedélyezett!' align=absmiddle> ";
            elseif ($church['ok'] == 'f')
                $jelzes.="<img src=/img/ora.gif title='Feltöltött/módosított templom, áttekintésre vár!' align=absmiddle> ";

            $church['jelzes'] = $jelzes;
            $churches[$church['id']] = $church;
        }

        /* észrevételezett templomok esetén RSS lehetőség */
        if ($allapot == 'e') {
            $query = array();
            foreach (array('egyhazmegye', 'allapot', 'kkulcsszo', 'sort', 'sid') as $var) {
                if (isset($$var) AND $ $var != '')
                    $query[] = $var . "=" . urlencode($$var);
            }
            $link = 'naplo_rss.php';
            if (count($query) > 0)
                $link .= "?" . implode('&', $query);
            //$kiir .= "<br/><a href=\"".$link."\" class=felsomenulink>RSS</a>";
        }

        $vars = array(
            'form' => $form,
            'sum' => $sum,
            'pager' => $lapozo,
            'churches' => $churches,
        );

        $this->form = $form;
        $this->sum = $sum;
        $this->pager = $lapozo;
        $this->churches = $churches;
    }

}
