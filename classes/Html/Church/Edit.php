<?php

namespace Html\Church;

class Edit extends \Html\Html {

    public function __construct($path) {
        global $user;

        $this->tid = $path[0];
        $this->church = new \Church($this->tid);
        $this->church->loadLog();
        $this->array2this($this->church);

        if (!$this->church->checkWriteAccess($user)) {
            $this->title = 'Hiányzó jogosultság!';
            addMessage('Hiányzó jogosultság!', 'danger');
            return;
        }

        $isForm = \Request::Text('submit');
        if ($isForm) {
            $this->modify();
        }
        $this->preparePage();
    }

    function modify() {
        global $config, $user;

        $ip = $_SERVER['REMOTE_ADDR'];
        $host = gethostbyaddr($ip);

        $hiba = false;
        $tid = $_POST['tid'];
        $church = getChurch($tid);

        $ma = date('Y-m-d');

        $modosit = $_POST['modosit'];
        $adminmegj = $_POST['adminmegj'];
        $nev = $_POST['nev'];
        $ismertnev = $_POST['ismertnev'];
        $turistautak = $_POST['turistautak'];
        $egyhazmegye = $_POST['egyhazmegye'];
        $espkerT = $_POST['espkerT'];
        $espereskerulet = $espkerT[$egyhazmegye];
        $orszag = $_POST['orszag'];
        $megyeT = $_POST['megyeT'];
        $megye = $megyeT[$orszag];
        if (empty($megye))
            $megye = 0;
        $varosT = $_POST['varosT'];
        $varos = $varosT[$orszag][$megye];
        $cim = $_POST['cim'];
        $megkozelites = $_POST['megkozelites'];
        $plebania = $_POST['plebania'];
        $pleb_url = $_POST['pleb_url'];
        $pleb_eml = $_POST['pleb_eml'];
        $nyariido = $_POST['nyariido'];
        $teliido = $_POST['teliido'];
        $megjegyzes = $_POST['megjegyzes'];
        $miseaktiv = $_POST['miseaktiv'];
        $misemegj = $_POST['misemegj'];
        $frissit = $_POST['frissit'];
        if ($frissit == 'i')
            $frissites = " frissites='$ma', ";
        $kontakt = $_POST['kontakt'];
        $kontaktmail = $_POST['kontaktmail'];

        $bucsu = $_POST['bucsu'];
        $ok = $_POST['ok'];
        $feltolto = $_POST['feltolto'];
        $megbizhato = $_POST['megbizhato'];
        if ($megbizhato != 'i')
            $megbizhato = 'n';

        $lat = $_POST['lat'];
        $lng = $_POST['lng'];

        $szoveg = $_POST['szoveg'];
        $szoveg = str_replace('&eacute;', 'é', $szoveg);
        $szoveg = str_replace('&ouml;', 'ö', $szoveg);
        $szoveg = str_replace('&Ouml;', 'Ö', $szoveg);
        $szoveg = str_replace('&uuml;', 'ü', $szoveg);
        $szoveg = str_replace('&Uuml;', 'Ü', $szoveg);
        $szoveg = str_replace("'", "\'", $szoveg);

        $elsofeltoltes = $_POST['elsofeltoltes'];
        if ($elsofeltoltes == 'i' and ! empty($szoveg))
            $szoveg = '<p class=alap>' . nl2br($szoveg);

        if (empty($nev)) {
            $hiba = true;
            $hibauzenet.='<br>Nem lett kitöltve a templom neve!';
        }

        if ($hiba) {
            $txt.="<span class=hiba>HIBA a templom feltöltésénél!</span><br>";
            $txt.='<span class=alap>' . $hibauzenet . '</span>';
            $txt.="<br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";

            $adatT[2] = '<span class=alcim>Templomok feltöltése / módosítása</span><br><br>' . $txt;
            $tipus = 'doboz';
            $kod.=formazo($adatT, $tipus);
        } else {
            $most = date('Y-m-d H:i:s');
            if ($tid > 0) {
                $uj = false;
                $parameter1 = 'update';
                list($log) = mysql_fetch_row(mysql_query("select log from templomok where id='$tid'"));
                $ujlog = $log . "\nMod: " . $user->login . " ($most)";
                $parameter2 = ", modositotta='" . $user->login . "', moddatum='$most', log='$ujlog' where id='$tid'";

                //Módosítjuk a hozzákapcsolódó miseidőpontoknál is az időszámítási dátumot
                $query = "update misek set datumtol='$nyariido', datmig='$teliido' where tid='$tid' and torolte=''";
                mysql_query($query);
            } else {
                $uj = true;
                $parameter1 = 'insert';
                $parameter2 = ", regdatum='$most', log='Add: " . $user->login . " ($most)'";
                $frissites = " frissites='$ma', ";
            }

            $query = "$parameter1 templomok set nev='$nev', ismertnev='$ismertnev', turistautak='$turistautak', orszag='$orszag', megye='$megye', varos='$varos', cim='$cim', megkozelites='$megkozelites', plebania='$plebania', pleb_url='$pleb_url', pleb_eml='$pleb_eml', egyhazmegye='$egyhazmegye', espereskerulet='$espereskerulet', leiras='$szoveg', megjegyzes='$megjegyzes',  miseaktiv='$miseaktiv', misemegj='$misemegj', bucsu='$bucsu', nyariido='$nyariido', teliido='$teliido', $frissites kontakt='$kontakt', kontaktmail='$kontaktmail', adminmegj='$adminmegj', megbizhato='$megbizhato', ok='$ok' ";
            if ($user->checkRole('miserend'))
                $query .= ", letrehozta='$feltolto' ";
            $query .= " $parameter2 ";
            if (!mysql_query($query))
                echo 'HIBA!<br>' . mysql_error();
            if ($uj)
                $tid = mysql_insert_id();
            else {
                $katnev = "$nev ($varos)";
                if (!mysql_query("update kepek set katnev='$katnev' where tid='$tid'"))
                    ;
            }

            //geolokáció
            $query = "SELECT * FROM terkep_geocode WHERE tid = " . $tid . " LIMIT 1 ";
            $result = mysql_query($query);
            $geocode = mysql_fetch_assoc($result);
            if ($config['debug'] > 1)
                echo $geocode['lng'] . "->" . $lng . ";" . $geocode['lat'] . "->" . $lat;
            if ($lng != $geocode['lng'] OR $lat != $geocode['lat']) {
                if ($geocode != array()) {
                    mysql_query("DELETE FROM terkep_geocode WHERE tid = " . $tid . " LIMIT 1 ");
                    $geocode['checked'] = 0;
                }
                $query = "INSERT INTO terkep_geocode (tid,lng,lat,checked) VALUES (" . $tid . "," . $lng . "," . $lat . ",1)";
                mysql_query($query);
                $query = "INSERT INTO terkep_geocode_suggestion (tid,tchecked,slng,slat,uid) VALUES (" . $tid . "," . $geocode['checked'] . "," . $lng . "," . $lat . ",'" . $user->login . "')";
                mysql_query($query);
            }
            if ($lng != '' AND $lat != '') {
                if ($lng != $church['lng'] OR $lat != $church['lat']) {
                    //neighboursUpdate($tid);
                    $query = "UPDATE distance SET toupdate = 1 WHERE tid1 = " . $tid . " OR tid2 = " . $tid . " ;";
                    mysql_query($query);
                    //updateDistances($tid,15);
                }
            }


            //fájlkezelés
            $fajl = $_FILES['fajl']['tmp_name'];
            $fajlnev = $_FILES['fajl']['name'];
            $delfajl = $_POST['delfajl'];

            if (is_array($delfajl)) {
                foreach ($delfajl as $ertek) {
                    unlink("fajlok/templomok/$tid/$ertek");
                }
            }

            if (!empty($fajl)) {
                $konyvtar = "fajlok/templomok";
                //Könyvtár ellenőrzése
                if (!is_dir("$konyvtar/$tid")) {
                    //létre kell hozni
                    if (!mkdir("$konyvtar/$tid", 0775)) {
                        echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';
                    }
                }

                //Másolás
                if (!copy($fajl, "$konyvtar/$tid/$fajlnev"))
                    echo '<p>HIBA a másolásnál!</p>';
                unlink($fajl);
            }

            //képkezelés
            $konyvtar = "kepek/templomok/$tid";

            $delkepT = $_POST['delkepT'];
            if (is_array($delkepT)) {
                foreach ($delkepT as $ertek) {
                    @unlink("$konyvtar/$ertek");
                    @unlink("$konyvtar/kicsi/$ertek");
                    if (!mysql_query("delete from kepek where tid='$tid' and fajlnev='$ertek'"))
                        echo 'HIBA!<br>' . mysql_error();
                }
            }

            $kepfeliratT = $_POST['kepfeliratT'];
            $kepT = $_FILES['kepT']['tmp_name'];
            $kepnevT = $_FILES['kepT']['name'];

            if (is_array($kepT)) {
                foreach ($kepT as $id => $kep) {
                    if (!empty($kep)) {
                        //Könyvtár ellenőrzése
                        if (!is_dir("$konyvtar")) {
                            //létre kell hozni
                            if (!mkdir("$konyvtar", 0775)) {
                                echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';
                            }
                            if (!mkdir("$konyvtar/kicsi", 0775)) {
                                echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';
                            }
                        }

                        $kimenet = "$konyvtar/$kepnevT[$id]";
                        $kimenet1 = "$konyvtar/kicsi/$kepnevT[$id]";

                        if (!copy($kep, "$kimenet"))
                            print("HIBA a másolásnál ($kimenet)!<br>\n");
                        else {
                            $info = getimagesize($kimenet);
                            $w = $info[0];
                            $h = $info[1];
                            //Bejegyzés az adatbázisba
                            if (!mysql_query("insert kepek set tid='$tid', fajlnev='$kepnevT[$id]', felirat='$kepfeliratT[$id]', width=$w, height=$h "))
                                echo 'HIBA!<br>' . mysql_error();
                        }

                        unlink($kep);
                        if ($w > 800 or $h > 600)
                            kicsinyites($kimenet, $kimenet, 800);
                        kicsinyites($kimenet, $kimenet1, 120);
                    }
                }
            }
            $fooldalkepT = $_POST['fooldalkepT'];
            $kepfeliratmodT = $_POST['kepfeliratmodT'];
            $kepsorszamT = $_POST['kepsorszamT'];
            if (is_array($kepsorszamT)) {
                foreach ($kepsorszamT as $melyikkep => $ertek) {
                    if ($fooldalkepT[$melyikkep] == 'i')
                        $kiemelt = 'i';
                    else
                        $kiemelt = 'n';
                    //Módosítás az adatbázisban
                    if (!mysql_query("update kepek set felirat='$kepfeliratmodT[$melyikkep]', sorszam='$ertek', kiemelt='$kiemelt' where tid='$tid' and fajlnev='$melyikkep'"))
                        echo 'HIBA!<br>' . mysql_error();
                }
            }

            if ($modosit == 'i') {
                return;
            } elseif ($modosit == 'm') {
                $this->redirect("/templom/".$this->church->id."/editschedule");                
                exit;
            } elseif ($modosit == 't') {
                $this->redirect("/templom/".$this->church->id);                
                exit;
            } else {
                $this->redirect("/crhuch/catalogue");                
                exit;
            }
        }

        return $kod;
    }

    function preparePage() {
        global $user;

        $tid = $this->tid;

        $query = "select id,nev from egyhazmegye where ok='i' order by sorrend";
        $lekerdez = mysql_query($query);
        while (list($id, $nev) = mysql_fetch_row($lekerdez)) {
            $ehmT[$id] = $nev;
        }

        $query = "select id,ehm,nev from espereskerulet";
        $lekerdez = mysql_query($query);
        while (list($id, $ehm, $nev) = mysql_fetch_row($lekerdez)) {
            $espkerT[$ehm][$id] = $nev;
        }

        if ($tid > 0) {
            $most = date("Y-m-d H:i:s");
            $urlap.=include('editscript2.php'); //Csak, ha módosításról van szó

            $query = "select nev,ismertnev,turistautak,orszag,megye,varos,cim,megkozelites,plebania,pleb_url,pleb_eml,egyhazmegye,espereskerulet,leiras,megjegyzes,miseaktiv,misemegj,szomszedos1,szomszedos2,bucsu,nyariido,teliido,frissites,kontakt,kontaktmail,adminmegj,log,ok,letrehozta,megbizhato,eszrevetel,lat,lng from templomok LEFT JOIN terkep_geocode ON id=tid where id='$tid'";
            if (!$lekerdez = mysql_query($query))
                echo 'HIBA!<br>' . mysql_error();
            list($nev, $ismertnev, $turistautak, $orszag, $megye, $varos, $cim, $megkozelites, $plebania, $pleb_url, $pleb_eml, $egyhazmegye, $espereskerulet, $szoveg, $megjegyzes, $miseaktiv, $misemegj, $szomszedos1, $szomszedos2, $bucsu, $nyariido, $teliido, $frissites, $kontakt, $kontaktmail, $adminmegj, $log, $ok, $feltolto, $megbizhato, $teszrevetel, $lat, $lng) = mysql_fetch_row($lekerdez);
        }
        else {
            $datum = date('Y-m-d H:i');
            $nyariido = '2014-03-30';
            $teliido = '2014-10-25';
            $urlapkieg = "\n<input type=hidden name=elsofeltoltes value=i>";
        }

        $urlap.="\n<FORM ENCTYPE='multipart/form-data' method=post>";
        $urlap.=$urlapkieg;


        $urlap.="\n<input type=hidden name=tid value=$tid>";

        $urlap.='<table cellpadding=4>';

        //Észrevétel
        $jelzes = getRemarkMark($tid);
        $urlap.="\n<tr><td colspan=2><span class=kiscim>Észrevétel: </span>" . $jelzes['html'] . "</td></tr>";

        if ($tid > 0) {
            //Megnéz
            $urlap.="\n<tr><td colspan=2><span class=kiscim>Nyilvános oldal megnyitása:</span><span class=alap> (új ablakban)</span> <a href='/templom/$tid' class=link target=_blank><u>$nev</u></a></td></tr>";
        }

        //megjegyzés
        $urlap.="\n<tr><td bgcolor=#ECE5C8><div class=kiscim align=right>Megjegyzés:<br><a href=\"javascript:OpenNewWindow('/help/1',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó'></a></div></td><td bgcolor=#ECE5C8><textarea name=adminmegj class=urlap cols=50 rows=3>$adminmegj</textarea><span class=alap> a szerkesztéssel kapcsolatosan</span></td></tr>";

        //kontakt
        $urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Felelős:<br><a href=\"javascript:OpenNewWindow('/help/2',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó'></a></div></td><td bgcolor=#efefef><textarea name=kontakt class=urlap cols=50 rows=2>$kontakt</textarea><span class=alap> név és telefonszám</span><br><input type=text name=kontaktmail size=40 class=urlap value='$kontaktmail'><span class=alap> emailcím</span></td></tr>";
        //feltöltő
        if (empty($feltolto))
            $feltolto = $user->login;
        $urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Feltöltő (jogosult):</div></td><td bgcolor=#efefef>";
        global $user;
        if ($user->checkRole('miserend')) {
            $urlap.="<select name=feltolto class=urlap><option value=''>Nincs</option>";
            $query = "select login from user where ok='i' order by login";
            $lekerdez = mysql_query($query);
            while (list($usr) = mysql_fetch_row($lekerdez)) {
                $urlap.="<option value='$usr'";
                if ($usr == $feltolto)
                    $urlap.=" selected";
                $urlap.=">$usr</option>";
            }
            $urlap.="</select> <input type=checkbox name=megbizhato class=urlap value=i";
            if ($megbizhato != 'n')
                $urlap.=" checked";
            $urlap.="><span class=alap> megbízható, nem kell külön engedélyezni</span></td></tr>";
        } else {
            $urlap.= "<span class='alap'>" . $feltolto . "</td></tr>";
        }


        //név
        $urlap.="\n<tr><td bgcolor=#F5CC4C><div class=kiscim align=right>Templom neve:</div></td><td bgcolor=#F5CC4C><input type=text name=nev value=\"$nev\" class=urlap size=80 maxlength=150> <a href=\"javascript:OpenNewWindow('/help/3',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a></td></tr>";
        $urlap.="\n<tr><td bgcolor=#FAE19C><div class=kiscim align=right>közismert neve:</div></td><td bgcolor=#FAE19C><input type=text name=ismertnev value=\"$ismertnev\" class=urlap size=80 maxlength=150> <a href=\"javascript:OpenNewWindow('/help/4',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a><br><span class=alap>(Helyben elfogadott (ismert) templomnév, valamint település, vagy település résznév, amennyiben eltérő a település hivatalos nevétől, pl. <u>izbégi templom</u>)</span></td></tr>";

        //cím
        $urlap.="\n<tr><td><div class=kiscim align=right>Egyházigazgatási cím:<br><a href=\"javascript:OpenNewWindow('/help/5',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td>";

        //Egyházmegye
        $urlap.="<select name=egyhazmegye class=urlap onChange=\"if(this.value!=0) {";
        foreach ($ehmT as $id => $nev) {
            $urlap.="document.getElementById($id).style.display='none'; ";
        }
        $urlap.="document.getElementById(this.value).style.display='inline'; document.getElementById('valassz').style.display='none'; } else {";
        foreach ($ehmT as $id => $nev) {
            $urlap.="document.getElementById($id).style.display='none'; ";
        }
        $urlap.="document.getElementById('valassz').style.display='inline';}\"><option value=0>Nincs / nem tudom</option>";
        foreach ($ehmT as $id => $nev) {
            $urlap.="<option value=$id";
            if ($egyhazmegye == $id)
                $urlap.=' selected';
            $urlap.=">$nev</option>";

            if ($egyhazmegye == $id)
                $display = 'inline';
            else
                $display = 'none';
            $espkerurlap.="<div id=$id style='display: $display'><select name=espkerT[$id] class=keresourlap><option value=0>Nincs / nem tudom</option>";
            if (is_array($espkerT[$id])) {
                foreach ($espkerT[$id] as $espid => $espnev) {
                    $espkerurlap.="<option value=$espid";
                    if ($espid == $espereskerulet)
                        $espkerurlap.=' selected';
                    $espkerurlap.=">$espnev</option>";
                }
            }
            $espkerurlap.="</select><span class=alap> (espereskerület)</span><br></div>";
        }
        $urlap.="</select><span class=alap> (egyházmegye)</span><br>";

        //Espereskerület
        $urlap.=$espkerurlap;

        $urlap.="\n</tr></td>";

        $urlap .= $this->getLocationForm();
        
        /////////////////////////////////
                $query = "select id,nev from orszagok where kiemelt='i'";
        $lekerdez = mysql_query($query);
        while (list($id, $nev) = mysql_fetch_row($lekerdez)) {
            $orszagT[$id] = $nev;
        }

        $query = "select id,megyenev,orszag from megye";
        $lekerdez = mysql_query($query);
        while (list($id, $nev, $orszag) = mysql_fetch_row($lekerdez)) {
            $megyeT[$orszag][$id] = $nev;
        }

        $query = "select megye_id,orszag,nev from varosok order by nev";
        $lekerdez = mysql_query($query);
        while (list($megye, $orszag, $vnev) = mysql_fetch_row($lekerdez)) {
            $vnev1 = str_replace('Ö', 'O', $vnev);
            $vnev1 = str_replace('Ő', 'O', $vnev1);
            $vnev1 = str_replace('ö', 'o', $vnev1);
            $vnev1 = str_replace('ő', 'o', $vnev1);
            $vnev1 = str_replace('Ü', 'U', $vnev1);
            $vnev1 = str_replace('Ü', 'U', $vnev1);
            $vnev1 = str_replace('ü', 'u', $vnev1);
            $vnev1 = str_replace('ű', 'u', $vnev1);
            $vnev1 = str_replace('Á', 'A', $vnev1);
            $vnev1 = str_replace('á', 'a', $vnev1);
            $vnev1 = str_replace('É', 'E', $vnev1);
            $vnev1 = str_replace('é', 'e', $vnev1);
            $vnev1 = str_replace('í', 'i', $vnev1);
            $vnev1 = str_replace('Ú', 'U', $vnev1);
            $vnev1 = str_replace('ú', 'u', $vnev1);
            $vnev1 = str_replace('Ó', 'O', $vnev1);
            $vnev1 = str_replace('ó', 'o', $vnev1);

            $szam = rand(0, 100);
            $vnev1.=$szam;
            $varos1T[$orszag][$megye][] = $vnev1;
            $varosT[$orszag][$megye][$vnev1] = $vnev;
        }
        foreach ($varos1T as $orszagid => $m1T) {
            foreach ($m1T as $megyeid => $v1T) {
                asort($v1T, SORT_STRING);
                $varos1T[$orszagid][$megyeid] = $v1T;
            }
        }
        
        $urlap.="\n<tr><td><div class=kiscim align=right>Templom címe:<br><a href=\"javascript:OpenNewWindow('/help/5',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td>";
        //Ország
        $urlap.="<img src=/img/space.gif width=5 height=8><br>\n<select name=orszag class=urlap onChange=\"if(this.value!=0) {";
        foreach ($orszagT as $id => $nev) {
            $urlap.="document.getElementById('m$id').style.display='none'; ";
        }
        $urlap.="document.getElementById('m'+this.value).style.display='inline';} else {";
        foreach ($orszagT as $id => $nev) {
            $urlap.="document.getElementById('m$id').style.display='none'; ";
        }
        $urlap.="}\"><option value=0>Nincs / nem tudom</option>";
        foreach ($orszagT as $id => $nev) {
            $urlap.="\n<option value=$id";
            if ($orszag == $id)
                $urlap.=' selected';
            $urlap.=">$nev</option>";

            if ($orszag == $id)
                $mdisplay = 'inline';
            else
                $mdisplay = 'none';
            //megye
            if (is_array($megyeT[$id])) {

                $megyeurlap.="\n<div id=m$id style='display: $mdisplay'><select name=megyeT[$id] class=keresourlap onChange=\"if(this.value!=0) {";
                foreach ($megyeT[$id] as $meid => $nev) {
                    $megyeurlap.="document.getElementById('v$meid').style.display='none'; ";
                }
                $megyeurlap.="document.getElementById('v'+this.value).style.display='inline';} else {";
                foreach ($megyeT[$id] as $meid => $nev) {
                    $megyeurlap.="document.getElementById('v$meid').style.display='none'; ";
                }
                $megyeurlap.="}\"><option value=0>Nincs / nem tudom</option>";
                foreach ($megyeT[$id] as $meid => $mnev) {
                    $megyeurlap.="\n<option value='$meid'";
                    if ($meid == $megye)
                        $megyeurlap.=' selected';
                    $megyeurlap.=">$mnev</option>";

                    //település
                    if ($megye == $meid)
                        $vdisplay = 'inline';
                    else
                        $vdisplay = 'none';

                    $varosurlap.="\n<div id=v$meid style='display: $vdisplay'><select name=varosT[$id][$meid] class=keresourlap><option value=0>Nincs / nem tudom</option>";
                    if (is_array($varos1T[$id][$meid])) {
                        foreach ($varos1T[$id][$meid] as $vnev1) {
                            $varosurlap.="\n<option value='" . $varosT[$id][$meid][$vnev1] . "'";
                            if ($varosT[$id][$meid][$vnev1] == $varos)
                                $varosurlap.=' selected';
                            $varosurlap.=">" . $varosT[$id][$meid][$vnev1] . "</option>";
                        }
                    } else
                        $varosurlap.="<option value=0 selected>NINCS település feltöltve!!!</option>";
                    $varosurlap.="</select><span class=alap> (település)</span><br></div>";
                }
                $megyeurlap.="</select><span class=alap> (megye)</span><br></div>";
            }
            else {
                //település

                $varosurlap.="\n<div id=m$id style='display: $mdisplay'><select name=varosT[$id][0] class=keresourlap><option value=0>Nincs / nem tudom</option>";
                if (is_array($varos1T[$id][0])) {
                    foreach ($varos1T[$id][0] as $vnev1) {
                        $varosurlap.="\n<option value='" . $varosT[$id][0][$vnev1] . "'";
                        if ($varosT[$id][0][$vnev1] == $varos)
                            $varosurlap.=' selected';
                        $varosurlap.=">" . $varosT[$id][0][$vnev1] . "</option>";
                    }
                }
                $varosurlap.="</select><span class=alap> (település)</span><br></div>";
            }
        }
        $urlap.="</select><span class=alap> (ország)</span><br>";

        //Település
        $urlap.=$megyeurlap . $varosurlap;
        $urlap.="<input type=text name=cim value=\"$cim\" class=urlap size=60 maxlength=250><span class=alap> (utca, házszám)</span>";
        $urlap.="<br><img src=/img/space.gif widt=5 height=5><br><textarea name=megkozelites class=urlap cols=50 rows=2>$megkozelites</textarea><span class=alap> (megközelítés rövid leírása)</span>";

        //Koordináta
        $urlap.="<input type=text name=lat value=\"$lat\" class=urlap size=10 maxlength=7><span class=alap> (szélesség)</span> ";
        $urlap.="<input type=text name=lng value=\"$lng\" class=urlap size=10 maxlength=7><span class=alap> (hosszúság)</span>";
        $urlap.="</td></tr>";
        
        /////////////////////////////////

        //plébánia
        $urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Plébánia adatai:<br><a href=\"javascript:OpenNewWindow('/help/6',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td bgcolor=#efefef><textarea name=plebania class=urlap cols=50 rows=3>$plebania</textarea><span class=alap> cím, telefon, fax, kontakt</span>";
        $urlap.="<br><input type=text name=pleb_eml value='$pleb_eml' size=40 class=urlap maxlength=100><span class=alap> email</span>";
        $urlap.="<br><input type=text name=pleb_url value='$pleb_url' size=40 class=urlap maxlength=100><span class=alap> web http://-rel együtt!!!</span>";
        $urlap.="</td></tr>";


        //megjegyzés
        $urlap.="\n<tr><td bgcolor=#ffffff><div class=kiscim align=right>Megjegyzés:<br><a href=\"javascript:OpenNewWindow('/help/10',200,360);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td bgcolor=#ffffff><textarea name=megjegyzes class=urlap cols=50 rows=3>$megjegyzes</textarea><br><span class=alap> ami a \"jó tudni...\" dobozban megjelenik (pl. búcsú, védőszent, \"reklám\" stb.)</span></td></tr>";


        //miseaktív
        if ($miseaktiv == 1)
            $van = ' checked ';
        else
            $nincs = ' checked ';
        $urlap.="\n<tr><td bgcolor=#ffffff><div class=kiscim align=right>Aktív misézőhely:</div></td><td bgcolor=#ffffff>
	<input type=radio name=miseaktiv class=urlap value=1 " . $van . "> <span class=alap>Van rendszeresen mise.</span>
	<input type=radio name=miseaktiv class=urlap value=0 " . $nincs . "> <span class=alap>Nincs rendszeresen mise.</span></td></tr>";
        //mise megjegyzés
        $urlap.="\n<tr><td bgcolor=#ffffff><div class=kiscim align=right>Mise megjegyzés:<br><a href=\"javascript:OpenNewWindow('/help/41',200,360);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td bgcolor=#ffffff><textarea name=misemegj class=urlap cols=50 rows=3>$misemegj</textarea><br><span class=alap> Rendszeres rózsafűzér, szentségimádás, hittan, stb.</span></td></tr>";

        //nyári-téli időszámítás
        //	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Nyári időszámítás:</div></td><td bgcolor=#efefef><input type=text name=nyariido value=\"$nyariido\" class=urlap size=10 maxlength=10><span class=alap> - </span><input type=text name=teliido value=\"$teliido\" class=urlap size=10 maxlength=10> <a href=\"javascript:OpenNewWindow('/help/8',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a></td></tr>";
        //Szöveg
        $urlap.="<tr><td valign=top><div class=kiscim align=right>Részletes leírás, templom története:<br><a href=\"javascript:OpenNewWindow('/help/9',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td valign=top><span class=alap><font color=red><b>FONTOS!</b></font> A szöveghez MINDIG legyen stílus rendelve!</span><br><textarea name=szoveg class=urlap cols=90 rows=30>$szoveg</textarea>";

        $urlap.="\n</td></tr>";

        //Fájlok
        $urlap.="\n<tr><td bgcolor=#efefef valign=top><div class=kiscim align=right>Letölthető fájl(ok):</td><td bgcolor=#efefef valign=top>";
        $urlap.="\n<span class=alap>Kapcsolódó dokumentum, ha van ilyen:</span><br>";
        $urlap.="\n<span class=alap>Új fájl: </span><input type=file size=60 name=fajl class=urlap> <a href=\"javascript:OpenNewWindow('/help/12',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a><br>";
        //Könyvtár tartalmát beolvassa
        if ($tid > 0) {
            $konyvtar = "fajlok/templomok/$tid";
            if (is_dir($konyvtar)) {
                $handle = opendir($konyvtar);
                while ($file = readdir($handle)) {
                    if ($file != '.' and $file != '..') {
                        $meret = intval((filesize("$konyvtar/$file") / 1024));
                        if ($meret > 1000) {
                            $meret = intval(($meret / 1024) * 10) / 10;
                            $meret.=' MB';
                        } else
                            $meret.=' kB';
                        $filekiir = rawurlencode($file);
                        $urlap.="<br><li><a href='$konyvtar/$filekiir' class=alap><b>$file</b></a><span class=alap> ($meret) </span><input type=checkbox class=urlap name=delfajl[] value='$file'><span class=alap>Töröl</span></li>";
                    }
                }
                closedir($handle);
            }
        }

        //Képek
        $urlap.="\n<tr><td><div class=kiscim align=right>Képek:<br><a href=\"javascript:OpenNewWindow('/help/11',200,450);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td><span class=alap><font color=red>FIGYELEM!</font><br>Azonos nevű képek felülírják egymást!!! A fájlnévben ne legyen ékezet és szóköz!</span><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap>";
        if ($tid > 0) {
            //Meglévő képek listája
            $query = "select fajlnev,felirat,sorszam,kiemelt from kepek where tid='$tid' order by sorszam";
            $lekerdez = mysql_query($query);
            $konyvtar = "kepek/templomok/$tid/kicsi";
            $urlap.="\n<table width=100% cellpadding=0 cellspacing=0><tr>";
            while (list($fajlnev, $felirat, $sorszam, $kiemelt) = mysql_fetch_row($lekerdez)) {
                if ($a % 3 == 0 and $a > 0)
                    $urlap.="</tr><tr>";
                $a++;
                if ($kiemelt == 'n')
                    $fokepchecked = '';
                else
                    $fokepchecked = ' checked';
                $urlap.="\n<td valign=bottom><img src=$konyvtar/$fajlnev title='$fajlnev'><br><input type=text name=kepsorszamT[$fajlnev] value='$sorszam' maxlength=2 size=1 class=urlap><span class=alap> -főoldal:</span><input type=checkbox name=fooldalkepT[$fajlnev] $fokepchecked value='i' class=urlap><span class=alap> -töröl:</span><input type=checkbox name=delkepT[] value='$fajlnev' class=urlap><br><input type=text name=kepfeliratmodT[$fajlnev] value='$felirat' maxlength=250 size=20 class=urlap></td>";
            }
            $urlap.='</tr></table>';
        }
        $urlap.='</td></tr>';

        //Frissítés dátuma
        if ($tid > 0) {
            $urlap.="\n<tr><td valign=top><div class=kiscim align=right>Frissítés:<br><a href=\"javascript:OpenNewWindow('/help/14',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td valign=top><input type=text disabled value='$frissites' size=10 class=urlap><br><input type=checkbox name=frissit value=i class=urlap><span class=alap> Frissítsük a dátumot</span></td></tr>";
        }

        //Engedélyezés
        $urlap.="\n<tr><td bgcolor=#efefef valign=top><div class=kiscim align=right>Megjelenhet:</div></td><td bgcolor=#efefef valign=top><input type=radio name=ok value=i";
        if ($ok != 'n' and $ok != 'f')
            $urlap.=" checked";
        $urlap.="><span class=alap> igen</span>";
        $urlap.="<input type=radio name=ok value=f";
        if ($ok == 'f')
            $urlap.=" checked";
        $urlap.="><span class=alap> áttekintésre vár</span>";
        $urlap.="<input type=radio name=ok value=n";
        if ($ok == 'n')
            $urlap.=" checked";
        $urlap.="><span class=alap> nem</span>";
        $urlap.=" <a href=\"javascript:OpenNewWindow('/help/15',200,300);\"><img src=/img/sugo.gif border=0 title='Súgó' align=absmiddle></a></td></tr>";

        
        

       
        $this->title = "Templom feltöltése / módosítása";
        $this->content = $urlap;
        $this->columns2 = true;
        
        #printr($this);
    }

    function getLocationForm() {
        
        if($this->location->osm) {
            $return = $this->getOSMLocationForm();
        } else {
            $return = $this->getMiserendLocationForm();
        }
        return $return; 
    }

    function getOSMLocationForm() {
        return false;
        $osmTypeId = explode('/',$this->location->osm);
        
        printr($this);
        exit;
        $osm = new \OSM();
        $osm->getById($osmTypeId[0],$osmTypeId[1]);
        
        $osm->downloadEnclosingBoundaries();
        exit;
        printr($osm);
        echo "ok";
    }
    
    function getMiserendLocationForm() {

        return $urlap;
    }
}
