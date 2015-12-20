<?php

namespace Html;

class Home extends Html {

    public function __construct() {
        global $user, $config;


        $attributes = unserialize(ATTRIBUTES);
        $languages = unserialize(LANGUAGES);


        $ma = date('Y-m-d');
        $holnap = date('Y-m-d', (time() + 86400));
        $mikor = '8:00-19:00';

        $query = "select id,ehm,nev from espereskerulet";
        $lekerdez = mysql_query($query);
        while (list($id, $ehm, $nev) = mysql_fetch_row($lekerdez)) {
            $espkerT[$ehm][$id] = $nev;
        }

        //MISEREND űRLAP	
        $searchform = array(
            'm_id' => array(
                'type' => 'hidden',
                'name' => "m_id",
                'value' => 26),
            'm_op' => array(
                'type' => 'hidden',
                'name' => "m_op",
                'value' => "keres"),
            'kulcsszo' => array(
                'name' => "kulcsszo",
                'size' => 20,
                'class' => 'keresourlap',
                'placeholder' => 'kulcsszó'),
            'varos' => array(
                'name' => "varos",
                'size' => 20,
                'id' => 'varos',
                'class' => 'keresourlap',
                'placeholder' => 'település'),
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
            'class' => 'keresourlap',
            'onChange' => "
						var a = document.getElementsByName('espker');	
						for (index = 0; index < a.length; ++index) {
						    console.log(a[index]);
						    a[index].style.display = 'none';
						}

						if(this.value!=0) {	
							document.getElementById('espkerlabel').style.display='inline';
							document.getElementById('ehm'+this.value).style.display='inline';

						} else {
							document.getElementById('espkerlabel').style.display='none';
						}");
        $searchform['ehm']['options'][0] = 'mindegy';
        $query = "select id,nev from egyhazmegye where ok='i' order by sorrend";
        $lekerdez = mysql_query($query);
        while (list($id, $nev) = mysql_fetch_row($lekerdez)) {
            $searchform['ehm']['options'][$id] = $nev;
        }

        foreach ($espkerT as $ehm => $espker) {
            $searchform['espker'][$ehm] = array(
                'name' => "espker",
                'id' => "ehm" . $ehm,
                'style' => "display:none",
                'class' => 'keresourlap');
            $searchform['espker'][$ehm]['options'][0] = 'mindegy';
            if (is_array($espker)) {
                foreach ($espker as $espid => $espnev) {
                    $searchform['espker'][$ehm]['options'][$espid] = $espnev;
                }
            }
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
        $searchform['mikor'] = array(
            'name' => "mikor",
            'id' => "mikor",
            'class' => 'keresourlap',
            'onChange' => "
						if(this.value == 'x') {document.getElementById('md').style.display='inline';} 
						else {document.getElementById('md').style.display='none';}",
            'options' => array($vasarnap => 'vasárnap', $ma => 'ma', $holnap => 'holnap', 'x' => 'adott napon:')
        );
        $searchform['mikordatum'] = array(
            'name' => "mikordatum",
            'id' => "md",
            'style' => "display:none",
            'class' => "keresourlap",
            'size' => "10",
            'value' => $ma
        );
        $searchform['mikor2'] = array(
            'name' => "mikor2",
            'id' => "mikor2",
            'style' => "margin-top:12px",
            'class' => 'keresourlap',
            'onChange' => "
						if(this.value == 'x') {
							document.getElementById('md2').style.display='inline'; 
							alert('FIGYELEM! Fontos a formátum!');} 
						else {document.getElementById('md2').style.display='none';}",
            'options' => array(0 => 'egész nap', 'de' => 'délelőtt', 'du' => 'délután', 'x' => 'adott időben:')
        );
        $searchform['mikorido'] = array(
            'name' => "mikorido",
            'id' => "md2",
            'style' => "display:none;",
            'class' => "keresourlap",
            'size' => "7",
            'value' => $mikor
        );

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

        //group music
        $music['na'] = '<i>meghatározatlan</i>';
        foreach ($attributes as $abbrev => $attribute) {
            if ($attribute['group'] == 'music')
                $music = array($abbrev => $attribute['name']) + $music;
        }
        foreach ($music as $value => $label) {
            $searchform['zene'][] = array(
                'type' => 'checkbox',
                'name' => "zene[]",
                'class' => "keresourlap",
                'value' => $value,
                'labelback' => $label,
                'checked' => true,
            );
        }

        //group age
        $age['na'] = '<i>meghatározatlan</i>';
        foreach ($attributes as $abbrev => $attribute) {
            if ($attribute['group'] == 'age')
                $age = array($abbrev => $attribute['name']) + $age;
        }
        foreach ($age as $value => $label) {
            $searchform['kor'][] = array(
                'type' => 'checkbox',
                'name' => "kor[]",
                'class' => "keresourlap",
                'value' => $value,
                'checked' => true,
                'labelback' => $label,
            );
        }

        //group rite
        $searchform['ritus'] = array(
            'name' => "ritus",
            'id' => "ritus",
            'class' => 'keresourlap',
            'options' => array(0 => 'mindegy')
        );
        foreach ($attributes as $abbrev => $attribute) {
            if ($attribute['group'] == 'liturgy' AND $attribute['isitmass'] == true)
                $searchform['ritus']['options'][$abbrev] = $attribute['name'];
        }

        $searchform['ige'] = array(
            'type' => 'checkbox',
            'name' => "liturgy[]",
            'id' => "liturgy",
            'checked' => true,
            'class' => "keresourlap",
            'value' => "ige"
        );

        $templomurlap = '';
        $miseurlap = $urlap;
        //AndroidReklám
        $androidreklam = androidreklam();

        //Napi gondolatok
        //Napi igehely

        $url = LirugicalDay();
        if ($url != false) {
            $readingsId = array();
            foreach ($url->Celebration as $celebration) {
                $unnep .= $celebration->StringTitle->span[0] . " (" . $celebration->LiturgicalCelebrationType . ") <br/>\n";
                $readingsId[] = " id = '" . $celebration->LiturgicalReadingsId . "' ";
            }

            $ev = $celebration->LiturgicalYearLetter;
            if (preg_match("/évközi/i", $celebration->LiturgicalSeason))
                $idoszak = 'e';
            elseif (preg_match("/nagyböjti/i", $celebration->LiturgicalSeason))
                $idoszak = 'n';
            elseif (preg_match("/húsvéti/i", $celebration->LiturgicalSeason))
                $idoszak = 'h';
            else
                $idoszak = "%";
            $nap = $celebration->LiturgicalWeek . ". hét, " . $url->DayOfWeek;

            $where = " WHERE ( ev = '{$ev}' AND idoszak = '{$idoszak}' AND nap = '{$nap}' ) OR (" . implode(' OR ', $readingsId) . " ) LIMIT 1";

            /* */

            /*
              //A liturgikus naptárból kiszedjük, hogy mi kapcsolódik a dátumhoz
              $query="select ige,szent,szin from lnaptar where datum='$datum'";
              list($ige,$szent,$szin)=mysql_fetch_row(mysql_query($query));
             */
            //Az igenaptárból kikeressük a mai napot
            //$query="select ev,idoszak,nap,oszov_hely,ujszov_hely,evang_hely,unnep,intro,gondolat from igenaptar where id='$ige'";
            $query = "select ev,idoszak,nap,oszov_hely,ujszov_hely,evang_hely,unnep,intro,gondolat from igenaptar " . $where;
            //echo $query;
            list($ev, $idoszak, $nap, $oszov_hely, $ujszov_hely, $evang_hely, $unnep, $intro, $gondolat) = mysql_fetch_row(mysql_query($query));
            $napiuzenet = nl2br($intro);
            $elmelkedes = $gondolat;

            if ((!empty($ev)) and ( $ev != '0'))
                $igenap.="$ev év, ";
            if (!empty($idoszak))
                $igenap.=idoszak($idoszak);
            if (!empty($nap))
                $igenap.=" $nap";

            if (empty($unnep))
                $unnep = $igenap;


            if ($szent > 0) {
                //Ha szent tartozik a napohoz
                $query = "select nev,intro,leiras from szentek where id='$szent'";
                list($szentnev, $szentintro, $szentleiras) = mysql_fetch_row(mysql_query($query));
                $unnep = $szentnev;
                $napiuzenet = nl2br($szentintro);
                $elmelkedes = $szentleiras;
            }

            //További szentek
            $s_ho = substr($datum, 5, 2);
            $s_nap = substr($datum, 8, 2);
            if ($s_ho[0] == '0')
                $s_ho = $s_ho[1];
            if ($s_nap[0] == '0')
                $s_nap = $s_nap[1];
            $query = "select id,nev,intro,leiras from szentek where ho='$s_ho' and nap='$s_nap' and id!='$szent'";
            $lekerdez = mysql_query($query);
            while (list($szid, $sznev, $szintro, $szleiras) = mysql_fetch_row($lekerdez)) {
                $szentidT[] = $szid;
                $szentnevT[] = $sznev;
                $introT[] = nl2br($szintro);
                $leirasT[] = $szleiras;
            }

            if (is_array($szentidT)) {
                foreach ($szentidT as $kulcs => $ertek) {
                    if ($a > 0)
                        $megszentek.='<span class=link>, </span>';
                    if (!empty($introT[$kulcs]) or ! empty($leirasT[$kulcs])) {
                        $link = "<a href=?m_id=1&m_op=szview&id=$ertek&szin=$_GET[szin] class=link>";
                    } else
                        $megszentek.='<span class=link>';
                    $megszentek.=$link . $szentnevT[$kulcs];
                    if (!empty($link))
                        $megszentek.='</a>';
                    else
                        $megszentek.='</span>';
                    $link = '';
                    $a++;
                }
            }
        }

        if (!empty($unnep)) {
            $unnepkiir = "<span class=kiscim>$unnep</span>";
        }
        if (!empty($megszentek)) {
            $unnepkiir.='<br><span class=alap>(</span>' . $megszentek . '<span class=alap>)</span>';
        }

        $uzenet = "$unnepkiir<br>";
        $uzenet.="<br><div class=alapkizart>$napiuzenet</div>";
        $elmelkedes = "<span class=alapkizart>$elmelkedes</span>";


        $van_o = false;
        $van_u = false;
        $van_e = false;
        foreach (array('oszov', 'ujszov', 'evang') as $hely) {
            if (!empty(${$hely . "_hely"})) {
                $van_o = true;
                $tomb1 = explode(',', ${$hely . "_hely"});
                $tomb2 = explode('-', $tomb1[1]);
                $tomb3 = explode(' ', $tomb1[0]);
                $konyv = $tomb3[0];
                $fej = $tomb3[1];
                $vers = $tomb2[0];
                $link = "http://szentiras.hu/SZIT/" . preg_replace('/ /i', '', $konyv) . "/$fej#$vers";
                ${$hely . "_biblia"} = "<a href=$link target=_blank title='ez a rész és a környezete a Bibliában' class=link><img src=img/biblia.gif border=0 align=absmiddle> " . ${$hely . "_hely"} . "</a><br>";
            }
        }

        ///////////////////////////////////////////////////////////////////
        $igehelyek = $oszov_biblia . $ujszov_biblia . $evang_biblia;

        //Lit. naptár
        $naptar = "<span class=alap>naptár</span>";

        //Programajánló
        $programajanlo = "<span class=alap>kapcsolódó programok a naptárból<br>Fejlesztés alatt...</span>";

        //Képek
        $query = "SELECT t.id, t.nev, t.ismertnev, t.varos, k.fajlnev
    FROM kepek  k
    JOIN templomok t ON t.id=k.tid 
	 WHERE k.kiemelt = 'i' AND k.width < k.height AND k.height > 599 
    GROUP BY t.id 
    ORDER by RAND()
    LIMIT 15";

        if (!$lekerdez = mysql_query($query))
            echo "HIBA!<br>" . mysql_error();
        $mennyi = mysql_num_rows($lekerdez);
        if ($mennyi > 0) {
            $kepek.="\n<img src=".$config['path']['domain']."/img/negyzet_kek.gif align=absmiddle><img src=img/space.gif width=5 height=5><span class=dobozcim_fekete>Képek templomainkról</span><br>";
            $konyvtaralap = "kepek/templomok";

            $kepek .= '<div style="height:180px"><div class="als-container" id="my-als-list">
            <span class="als-prev"><img src="img/als/thin_left_arrow_333.png" alt="prev" title="previous" /></span>
                <div class="als-viewport">
                    <ul class="als-wrapper">';

            $randoms = array();
            while ($random = mysql_fetch_assoc($lekerdez)) {
                $randoms[] = $random;
            }
            foreach ($randoms as $k => $random) {
                $random['konyvtar'] = $randoms[$k]['konyvtar'] = "$konyvtaralap/" . $random['id'];
                if (file_exists($random['$konyvtar'] . "/kicsi/" . $random['fajlnev'])) {
                    //if(is_file("$konyvtar/kicsi/$fajlnev")) {
                    @$info = getimagesize($random['$konyvtar'] . "/kicsi/" . $random['fajlnev']);
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

                    $kepek .= "<li class='als-item colorbox'><a href=\"" . $random['konyvtar'] . "/" . $random['fajlnev'] . "\" title=\"" . $random['title'] . "\" class='als-color' onclick=\"ga('send','event','Inbound Links','Photos','?templom=" . $random['id'] . "')\">
	            <img src=\"" . $random['konyvtar'] . "/kicsi/" . $random['fajlnev'] . "\" title='" . $random['nev'] . " (" . $random['varos'] . ")' ></a>
	            
	                <div tid='" . $random['id'] . "' style='display:none;text-align:center'>
	                    <a href=\"?templom=" . $random['id'] . "\" title=\"" . $random['title'] . "\">
	                    <img src=\"" . $random['konyvtar'] . "/" . $random['fajlnev'] . "\" title='" . $random['nev'] . " (" . $random['varos'] . ")' align=\"center\" style=\"max-height:80%;display:block;margin-left:auto;margin-right:auto\">
	                    <div style=\"background-color:rgba(255,255,255,0.3);padding:10px;\" class=\"felsomenulink\">" . $random['nev'] . " (" . $random['varos'] . ")</div>
	                    </a>
	                </div>
	            
	            </li>\n";
                } else {
                    
                }
            }
            if ($mennyi < 4)
                for ($i = 0; $i < 4 - $mennyi; $i++)
                    $kepek .= "<li class='als-item'></li>";
            $kepek.='</ul>
            </div>
            <span class="als-next"><img src="img/als/thin_right_arrow_333.png" alt="next" title="next" /></span>
            </div></div>';

            $scrollable .= '<script>
			$(document).ready(function(){                
                $("#my-als-list").als(	{visible_items: ';
            if ($mennyi < 4)
                $scrollable .= 4;
            else
                $scrollable .= 4;
            $scrollable .= ',	circular: "no"});                      
                
                $("li.colorbox").each(function() {
                    $(this).colorbox({
                        html: $(this).find("div").html(),
                        rel: "group_random",
                        transition:"fade",
                        maxHeight:"98%"
                    },
                    function() {
                        ga(\'send\',\'event\',\'Photos\',\'fooldal\',$(this).find("div").attr("tid"));
                    }                        
                   );
                });
                /*$(".als-color").colorbox({rel:\'als-color\', transition:"fade",maxHeight:"98%"},
                    function() {
                        ga(\'send\',\'event\',\'Photos\',\'fooldal\',\'' . $tid . '\')        });            
                
            */ });
        </script>';
            $kepek .= $scrollable;
        }

        //statisztika
        $statisztika = miserend_printRegi();

        $variables = array(
            'favorites' => $user->getFavorites(),
            'formnyit' => $formnyit,
            'formvalaszt' => $formvalaszt,
            'miseurlap' => $miseurlap,
            'searchform' => $searchform,
            'androidreklam' => $androidreklam,
            'templomurlap' => $templomurlap,
            'kepek' => $kepek,
            'kep' => $randoms[0],
            'uzenet' => $uzenet,
            'igehelyek' => $igehelyek,
            'elmelkedes' => $elmelkedes,
            'design_url' => $config['path']['domain'],
            'alert' => LiturgicalDayAlert('html'),
            'campaign' => updatesCampaign(),
        );
        $variables['template'] = 'home.twig';
        
        foreach($variables as $key => $var) {
            $this->$key = $var;
        }
    }

}
