<?php

namespace Html;

class Church extends Html {

    public function __construct() {
        global   $config, $meta;
        global $twig, $user;

        $tid = $_REQUEST['tid'];

        $church = getChurch($tid);
        if ($church != array())
            $vane = 1;
        foreach ($church as $k => $i)
            $$k = $i;

        $ma = date('Y-m-d');
        if ($frissites > 0) {
            $frissitve = $frissites;
            $frissites = str_replace('-', '.', $frissites) . '.';
            //$frissites="<span class=kicsi_kek><b><u>Frissítve:</u></b><br>$frissites</span>";
        }


        $titlekieg = " - $nev ($varos)";
        if ($vane != 1)
            $titlekieg = "404";

        if (!empty($turistautak)) {
            //$terkep="<br><a href=http://turistautak.hu/poi.php?id=$turistautak target=_blank title='További infók'><img src=http://www.geocaching.hu/images/mapcache/poi_$turistautak.gif border=0 vspace=5 hspace=5></a>";
        }

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
            $nev.=" <a href=?m_id=27&m_op=addtemplom&tid=$tid><img src=img/edit.gif align=absmiddle border=0 title='Szerkesztés/módosítás'></a> <a href=?m_id=27&m_op=addmise&tid=$tid><img src=img/mise_edit.gif align=absmiddle border=0 title='mise módosítása'></a>";

            $query = "select allapot from eszrevetelek where hol_id = '" . $tid . "' GROUP BY allapot ORDER BY allapot limit 5;";
            $result = mysql_query($query);
            $allapotok = array();
            while ($row = mysql_fetch_assoc($result)) {
                if ($row['allapot'])
                    $allapotok[] = $row['allapot'];
            }
            if (in_array('u', $allapotok))
                $nev.=" <a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid',550,500);\"><img src=img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";
            elseif (in_array('f', $allapotok))
                $nev.=" <a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid',550,500);\"><img src=img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";
            elseif (count($allapotok) > 0)
                $nev.=" <a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid',550,500);\"><img src=img/csomag1.gif title='Észrevételek!' align=absmiddle border=0></a> ";
        }
        if (!empty($ismertnev))
            $ismertnev = $ismertnev; //"<span class=alap><i><b>Közismert nevén:</b></i><br></span><span class=dobozfocim_fekete><b><font color=#AC007A>$ismertnev</font></b></span><br><img src=img/space.gif width=5 height=7><br>";
        $cim = "<span class=alap><i>Cím:</i> <u>$varos, $cim</u></span>";

        if ($checked > 0) {
            $staticmap = "kepek/staticmaps/" . $tid . "_227x140.jpeg";
            if (file_exists($staticmap))
                $cim .= "<a href=\"http://www.openstreetmap.org/?mlat=$lat&mlon=$lng#map=15/$lat/$lng\" target=\"_blank\"><img src='kepek/staticmaps/" . $tid . "_227x140.jpeg'></a>";
            else
                $cim .= "<br/><span class=alap><i>Térképen:</i> <u><a href=\"http://www.openstreetmap.org/?mlat=$lat&mlon=$lng\">$lat, $lng</a></u></span>";
        } else
            $cim .= "<br/><span class=alap><u><a href=\"http://terkep.miserend.hu/?templom=$tid\">Segíts megtalálni a térképen!</a></u></span>";

        $kapcsolat = nl2br($plebania);
        if (!empty($pleb_url))
            $kapcsolat.="<br/><div style=\"width: 230px;white-space: nowrap;overflow: hidden;o-text-overflow: ellipsis;text-overflow: ellipsis;\">Weboldal: <a href=$pleb_url target=_blank class=link title='$pleb_url'  onclick=\"ga('send','event','Outgoing Links','click','" . $pleb_url . "');\">" . preg_replace("/http:\/\//", "", $pleb_url) . "</a></div>";
        if (!empty($pleb_eml))
            $kapcsolat.="<div style=\"width: 230px;white-space: nowrap;overflow: hidden;o-text-overflow: ellipsis;text-overflow: ellipsis;\">Email: <a href='mailto:$pleb_eml' class=link>$pleb_eml</a></div>";

        $eszrevetel = "<p class=alapkizart>Ha észrevételed van a templommal vagy a miserenddel kapcsolatban, írd meg nekünk!</p>
    <div align=center><a href=\"javascript:OpenNewWindow('eszrevetel.php?id=$tid&kod=templomok',450,530);\" class=link><font color=#8D317C size='+1'><b>Észrevétel beküldése</b></font></a></div>
    <div align=center><a href=\"javascript:OpenNewWindow('kepkuldes.php?id=$tid&kod=templomok',450,530);\" class=link><font color=#8D317C size=''><b>Új kép beküldése</b></font></a></div>";

        ////////////////////////
        //$sz1='<span class=kicsi>a szolgáltatás átmenetileg szünetel</span>';
        //$sz2='<span class=kicsi>a szolgáltatás átmenetileg szünetel</span>';

        $marcsak = (int) ((strtotime('2014-03-20') - time()) / ( 60 * 60 * 24 ));
        //$sz1="<span class=\"kicsi\"><a href=\"http://terkep.miserend.hu\" target=\"_blank\">Már csak ".$marcsak." nap és itt a térkép.</a></span>";
        //$sz2= $sz1;
        ////////////////////////


        if (!empty($megjegyzes)) {
            $variables = array(
                'header' => array('content' => 'Jó tudni...'),
                'content' => nl2br($megjegyzes),
                'settings' => array('width=50%', 'align=right'),
                'design_url' => $config['path']['domain']);
            $jotudni = $twig->render('doboz_lila.html', $variables);
        }

        //képek	
        $query = "select fajlnev,felirat from kepek where tid='$tid' order by sorszam";
        $lekerdez = mysql_query($query);
        $mennyi = mysql_num_rows($lekerdez);
        $kepek = '';
        if ($mennyi > 0) {

            $scrollable .= '<script>
			$(document).ready(function(){                
                $("#my-als-list").als(	{visible_items: ';
            if ($mennyi < 4)
                $scrollable .= 4;
            else
                $scrollable .= 4;
            $scrollable .= '});                      
                $(".als-color").colorbox({rel:\'als-color\', transition:"fade",maxHeight:"98%"},
                    function() {
                        ga(\'send\',\'event\',\'Photos\',\'templom\',\'' . $tid . '\')        });            
                
             });
        </script>';

            $kepek .= "\n";

            $kepek .= '<div class="als-container" id="my-als-list">
  <span class="als-prev"><img src="img/als/thin_left_arrow_333.png" alt="prev" title="previous" /></span>
  <div class="als-viewport">
    <ul class="als-wrapper">';

            $konyvtar = "kepek/templomok/$tid";
            while (list($fajlnev, $kepcim) = mysql_fetch_row($lekerdez)) {
                $altT[$fajlnev] = $kepcim;
                if (!isset($ogimage))
                    $ogimage = '<meta property="og:image" content="' . $konyvtar . "/" . $fajlnev . '">';
                @$info = getimagesize("$konyvtar/kicsi/$fajlnev");
                $w1 = $info[0];
                $h1 = $info[1];
                if ($h1 > $w1 and $h1 > 90) {
                    $arany = 90 / $h1;
                    $ujh = 90;
                    $ujw = $w1 * $arany;
                } else {
                    $ujh = $h1;
                    $ujw = $w1;
                }
                $osszw = $osszw + $ujw;
                $title = rawurlencode($kepcim);

                $kepek .= "<li class='als-item'><a href=\"$konyvtar/$fajlnev\" title=\"$title\" class='als-color'><img src=$konyvtar/kicsi/$fajlnev title='$kepcim' ></a></li>\n";
            }
            if ($mennyi < 4)
                for ($i = 0; $i < 4 - $mennyi; $i++)
                    $kepek .= "<li class='als-item'></li>";

            $kepek.='</ul>
            </div>
            <span class="als-next"><img src="img/als/thin_right_arrow_333.png" alt="next" title="next" /></span>
            </div>';

            $kepek .= $scrollable;
            if (isset($ogimage))
                $meta .= $ogimage . "\n";
        }

        $result = mysql_query("SELECT id FROM favorites WHERE uid = " . $user->uid . " AND tid = " . $tid . " LIMIT 1");
        if (mysql_num_rows($result) == 1 AND $user->uid > 0)
            $favorite = 1;
        elseif ($user->uid > 0)
            $favorite = 0;
        else
            $favorite = -1;

        if ($vane > 0) {
            $variables = array(
                'tid' => $tid,
                'title' => $church['nev'] . " | Miserend",
                'nev' => $nev, 'ismertnev' => $ismertnev,
                'favorite' => $favorite,
                'varos' => $varos,
                'frissites' => $frissites,
                'nyari' => $nyari,
                'teli' => $teli,
                'miserend' => $misek,
                'eszrevetel' => $eszrevetel,
                'androidreklam' => androidreklam(),
                'kepek' => $kepek,
                'jotudni' => $jotudni,
                'leiras' => $leiras,
                'cim' => $cim,
                'terkep' => $terkep,
                'megkozelites' => nl2br($megkozelites),
                'kapcsolat' => $kapcsolat,
                'miseaktiv' => $miseaktiv,
                'misemegjegyzes' => nl2br($misemegj),
                'szomszedok' => $szomszedok,
                'napok' => array('', 'hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat', '<font color=#AC282B><b>vasárnap</b></font>'),
                'design_url' => $config['path']['domain'],
                'campaign' => updatesCampaign(),
                'alert' => LiturgicalDayAlert('html'),
                'titlekieg' => $titlekieg,
            );
            $variables['template'] = 'church.twig';
            
            foreach($variables as $key => $var) {
                $this->$key = $var;
            }
            
        } else {
            $this->template = 'church.twig';
            addMessage("A keresett templom nem található.","danger");
        }
    }

}
