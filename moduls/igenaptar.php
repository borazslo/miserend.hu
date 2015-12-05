<?php

include_once("szotar/datumok.php");

function haview() {
    global $text, $vars, $linkveg, $db_name, $m_id;

    $vars = monthi();
    $datumT = explode('-', $_GET['date']);

    $vars['months'][$vars['currMonth']];

    $text = '<table width=100% cellspacing=0><tr><td><center>';

    $text .= '<table style="width: 98%;">';
    $text .= '<tr><td>&nbsp;</td>';

    $text .= '<td align="center">';
    $text .= '<a href="?m_id=1&op=aktiv&dateh=' . $vars['prevYear'] . '-' . $vars['prevMonth'] . $linkveg . '" title="' . _PREV_MONTH . '" class="kiscimlink">&lt;&lt;&lt;</a> &nbsp; &nbsp;';
    $text .= '<span class=kiscim>' . $vars['currYear'] . '. ' . $vars['months'][$vars['currMonth']] . '</span>';

    $text .= ' &nbsp; &nbsp;<a href="?m_id=1&op=aktiv&dateh=' . $vars['nextYear'] . '-' . $vars['nextMonth'] . $linkveg . '" title="' . _NEXT_MONTH . '" class="kiscimlink">&gt;&gt;&gt;</a>';
    $text .= '</td><td>&nbsp;</td>';
    $text .= '</tr>';
    $text .= '<tr><td colspan="5">&nbsp;</td></tr>';

    // fejléc-sor
    $text .= '<tr><td><center><span class=kislink>' . alapnyelv('nap') . '</span></center></td><td colspan="3"><center><span class=kislink>' . alapnyelv('ünnep') . '</span></center></td><td style="width: 4%;"><center><span class=kislink>' . alapnyelv('liturgikus szín') . '</span></center></td></tr>';

    $text .= '<tr>';

    $dayCount = 1;

    $szin_query = "SELECT datum,ige,szent,szin FROM lnaptar WHERE datum LIKE '" . $vars['currYear'] . "-" . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT) . "-%' ORDER BY datum;";

    $_szin = mysql_query($szin_query) or die(mysql_error());
    while ($eredmeny = mysql_fetch_array($_szin)) {
        $nap = substr($eredmeny[0], -2);
        if ($nap[0] == 0)
            $nap = $nap[1];
        $ho = substr($eredmeny[0], 5, 2);
        if ($ho[0] == 0)
            $ho = $ho[1];

        $col = $eredmeny[3];
        $kesz_szin[$nap] = array($col);

        $datum = $eredmeny[0];
        $ige = $eredmeny[1];
        $szent = $eredmeny[2];

        //További szent keresése
        $query = "select id,nev,intro,leiras from szentek where ho='$ho' and nap='$nap' and id!='$szent'";
        $lekerdez = mysql_db_query($db_name, $query);
        while (list($szid, $sznev, $szintro, $szleiras) = mysql_fetch_row($lekerdez)) {
            $szentidT[] = $szid;
            $szentnevT[] = $sznev;
            $introT[] = $szintro;
            $leirasT[] = $szleiras;
        }

        if (is_array($szentidT)) {
            $msz = 0;
            foreach ($szentidT as $kulcs => $ertek) {
                if ($msz > 0)
                    $megszentek.='<span class=kislink>, </span>';
                if (!empty($introT[$kulcs]) or ! empty($leirasT[$kulcs])) {
                    $link = "<a href=?m_id=$m_id&m_op=szview&id=$ertek&szin=$_GET[szin]$linkveg class=kislink>";
                } else
                    $megszentek.='<span class=kislink>';
                $megszentek.=$link . $szentnevT[$kulcs];
                if (!empty($link))
                    $megszentek.='</a>';
                else
                    $megszentek.='</span>';
                $link = '';
                $msz++;
            }
            $megszentT[$nap] = $megszentek;
            $megszentek = '';
            $szentidT = '';
            $szentnevT = '';
            $introT = '';
            $leirasT = '';
        }


        if ($szent > 0) {
            $szentT[] = "id=$szent";
            $napTsz[$szent] = $nap;
        } else {
            $igeT[] = "id=$ige";
            $napTg[$ige] = $nap;
        }
    }

    //Szentek lekérdezése
    if (is_array($szentT)) {
        $feltetel = 'where ' . implode(' or ', $szentT);
        $query = "select id,nev from szentek $feltetel";
        $lekerdezsz = mysql_query($query);
        while (list($id, $nev) = mysql_fetch_row($lekerdezsz)) {
            $n = $napTsz[$id];
            if ($n[0] == '0')
                $n = $n[1];
            $unnepT[$n] = $nev;
        }
    }

    //Ünnep lekérdezése
    if (is_array($igeT)) {
        $feltetel = implode(' or ', $igeT);
        $query = "select id,ev,idoszak,nap,unnep from igenaptar where $feltetel";
        $lekerdezg = mysql_query($query);
        while (list($id, $ev, $idoszak, $nap, $unnep) = mysql_fetch_row($lekerdezg)) {
            $n = $napTg[$id];
            if ($n[0] == '0')
                $n = $n[1];
            $igenap = '';
            if ((!empty($ev)) and ( $ev != '0'))
                $igenap.="$ev év, ";
            if (!empty($idoszak))
                $igenap.=idoszak($idoszak);
            if (!empty($nap))
                $igenap.=" $nap";
            if (!empty($igenap) and ! empty($unnep))
                $igenap.='<br>';
            if (!empty($unnep))
                $igenap = "$igenap$unnep";
            $unnepT[$n] = $igenap;
        }
    }



    while ($dayCount <= $vars['totalDays'][$vars['currMonth']]) {
        $class = $kesz_szin[$dayCount][0];
        if (empty($class))
            $class = 'zold';
        $text .= '</tr><tr>';


        /* 			
          $query_unnep = "SELECT nev FROM szentek WHERE ho='".$vars['currMonth']."' AND nap='".$dayCount."';";
          $_unnep = mysql_query($query_unnep) or die(mysql_error());
         */
        $linktext = '<a href="?m_id=1&m_op=view&szoveg&today&date=' . $vars['currYear'] . '-' . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dayCount, 2, '0', STR_PAD_LEFT) . "&szin=$class" . $linkveg . '" class=kislink>';

        if ($dayCount == date("j") && $vars['currYear'] == date("Y") && $vars['currMonth'] == date("n")) {  // today
            // napi dátum kiírása
            $text .= '<td align="center" class="naptar_' . $class . '" style="border-width: 2px;">' . $linktext . $dayCount . '</a></td>';
            $text .= '<td class="naptar_' . $class . '" colspan="3" style="border-width: 2px;">' . $linktext;
            if (!empty($unnepT[$dayCount])) {
                $text .='<b>' . $unnepT[$dayCount] . '</b></a>';
            }
            // ha nincs ünnep, semmit nem írunk ki
            else
                $text .='&nbsp;';
            if (!empty($megszentT[$dayCount]))
                $text.='<br><span class=kislink>(</span>' . $megszentT[$dayCount] . '<span class=kislink>)</span>';
            $text .= '</td>';
        }
        else {
            $text .= '<td align="center" class="naptar_' . $class . '">' . $linktext . $dayCount . '</a></td>';
            $text .= '<td class="naptar_' . $class . '" colspan="3">' . $linktext;

            if (!empty($unnepT[$dayCount])) {
                $text .= '<b>' . $unnepT[$dayCount] . '</b></a>';
            }
            // ha nincs ünnep, semmit nem írunk ki
            else
                $text .='&nbsp;';
            if (!empty($megszentT[$dayCount]))
                $text.='<br><span class=kislink>(</span>' . $megszentT[$dayCount] . '<span class=kislink>)</span>';
            $text .= '</td>';
        }
        $text .= '<td class="naptar_' . $class . '"><center><span class=kislink>' . szin($kesz_szin[$dayCount][0]) . '</span></center></td>';
        $dayCount++;
    }

    $text .= '</tr>';
    /*
      $text .= '<tr>';
      $text .= '<td align="center" colspan="5">';
      $text .= '<br><small><a href="?calendar&action=month">'._CURR_MONTH.'</a>';
      $text .= '</td>';
      $text .= '</tr>';
     */
    $text .= '</table>';

    $text .= '</td></tr></table>';

    return $text;
}

function igenaptar_index() {
    global $_GET, $linkveg, $db_name, $m_id;

    //Főcím
    $adatT[0] = alapnyelv('igenaptár');
    $tipus = 'fomenucim';
    $kod.=formazo($adatT, $tipus);

    include_once("igenaptar_functions_havi.php");
    $tartalom = haview();

    $adatT[2] = $tartalom;
    $tipus = 'doboz';
    $kod.=formazo($adatT, $tipus);

    return $kod;
}

function igenaptar_viewnap($datum) {
    global $_GET, $linkveg, $db_name, $m_id;

    if (!isset($datum))
        $datum = date('Y-m-d');

    //Főcím
    $adatT[0] = alapnyelv('igenaptár');
    $tipus = 'fomenucim';
    $kod.=formazo($adatT, $tipus);

    //tartalom létrehozása
    //////////////////////////////
    //A liturgikus naptárból kiszedjük, hogy mi kapcsolódik a dátumhoz
    $query = "select ige,szent,szin from lnaptar where datum='$datum'";
    list($ige, $szent, $szin) = mysql_fetch_row(mysql_db_query($db_name, $query));

    //Az igenaptárból kikeressük a mai napot
    $query = "select ev,idoszak,nap,oszov_hely,oszov,ujszov_hely,ujszov,evang_hely,evang,unnep,intro,gondolat from igenaptar where id='$ige'";
    list($ev, $idoszak, $nap, $oszov_hely, $oszov, $ujszov_hely, $ujszov, $evang_hely, $evang, $unnep, $intro, $gondolat) = mysql_fetch_row(mysql_db_query($db_name, $query));
    $napiuzenet = nl2br($intro);
    if (!empty($gondolat)) {
        //Tovább
        $adatT[4] = 'további gondolatok';
        $adatT[5] = "?m_id=1&m_op=gview&id=$ige&date=$datum&szin=$_GET[szin]$linkveg";
        $tipus = 'tovabb';
        $tovabb = formazo($adatT, $tipus);
        $napiuzenet.=$tovabb;
    }

//	if(!empty($ev)) $igenap.="$ev év, ";
    if ((!empty($ev)) and ( $ev != '0'))
        $igenap.="$ev év, ";
    if (!empty($idoszak))
        $igenap.=idoszak($idoszak);
    if (!empty($nap))
        $igenap.=" $nap";

    if (empty($unnep))
        $igenap = "<br><span class=alcim>$igenap</span>";
    else
        $igenap = "<br><span class=alcim>$igenap</span>";      // dölt betűt kivettem .


    if ($szent > 0) {
        //Ha szent tartozik a napohoz
        $query = "select nev,intro,leiras from szentek where id='$szent'";
        list($szentnev, $szentintro, $szentleiras) = mysql_fetch_row(mysql_db_query($db_name, $query));
        $unnep = $szentnev;
        $napiuzenet = nl2br($szentintro);
        $igenap = '';

        if (!empty($szentleiras)) {
            //Tovább
            $adatT[4] = 'bővebben';
            $adatT[5] = "?m_id=1&m_op=szview&id=$szent&date=$datum&szin=$_GET[szin]$linkveg";
            $tipus = 'tovabb';
            $tovabb = formazo($adatT, $tipus);
            $napiuzenet.=$tovabb;
        }
    }

    //További szentek
    $s_ho = substr($datum, 5, 2);
    $s_nap = substr($datum, 8, 2);
    if ($s_ho[0] == '0')
        $s_ho = $s_ho[1];
    if ($s_nap[0] == '0')
        $s_nap = $s_nap[1];
    $query = "select id,nev,intro,leiras from szentek where ho='$s_ho' and nap='$s_nap' and id!='$szent'";
    $lekerdez = mysql_db_query($db_name, $query);
    while (list($szid, $sznev, $szintro, $szleiras) = mysql_fetch_row($lekerdez)) {
        $szentidT[] = $szid;
        $szentnevT[] = $sznev;
        $introT[] = nl2br($szintro);
        $leirasT[] = $szleiras;
    }

    if (is_array($szentidT)) {
        foreach ($szentidT as $kulcs => $ertek) {
            if ($a > 0)
                $megszentek.='<span class=kozepeslink>, </span>';
            if (!empty($introT[$kulcs]) or ! empty($leirasT[$kulcs])) {
                $link = "<a href=?m_id=1&m_op=szview&id=$ertek&szin=$_GET[szin]$linkveg class=kozepeslink>";
            } else
                $megszentek.='<span class=kozepeslink>';
            $megszentek.=$link . $szentnevT[$kulcs];
            if (!empty($link))
                $megszentek.='</a>';
            else
                $megszentek.='</span>';
            $link = '';
            $a++;
        }
    }

    $szinkiir = szin($szin);
    $datumT = explode('-', $datum);
    $ev = $datumT[0];
    $ho = $datumT[1];
    $nap = $datumT[2];

    if ($nap[0] == '0')
        $nap = $nap[1];

    $datumkiir = "$ev. " . alapnyelv('ho' . $ho) . " $nap.";
    if (!empty($unnep)) {
        $unnepkiir = "<br><span class=alcim>$unnep</span>";
    }
    if (!empty($megszentek)) {
        $unnepkiir.='<br><span class=kozepeslink>(</span>' . $megszentek . '<span class=kozepeslink>)</span>';
    }

    $tartalom = "<div align=center><span class=kiscim>$datumkiir</span>$igenap";
    $tartalom.="$unnepkiir<br><a href=?m_id=7&m_op=view&id=7$linkveg class=kismenulink title='bővebben a színekről'><small>A nap liturgikus színe: <b>$szinkiir</b></small></a></div>";
    $tartalom.="<br><div class=alapkizart>$napiuzenet</div>";

    $adatT[2] = $tartalom;
    $tipus = 'doboz';
    $kod.=formazo($adatT, $tipus);
    $tartalom = '';


    $van_o = false;
    $van_u = false;
    $van_e = false;
    if (!empty($oszov_hely)) {
        $van_o = true;
        $tomb1 = explode(',', $oszov_hely);
        $tomb2 = explode('-', $tomb1[1]);
        $tomb3 = explode(' ', $tomb1[0]);
        $konyv = $tomb3[0];
        $fej = $tomb3[1];
        $vers = $tomb2[0];
        $link = "http://www.kereszteny.hu/biblia/showchapter.php?reftrans=1&abbook=$konyv&numch=$fej#$vers";
        $oszov_biblia = "<a href=$link target=_blank title='ez a rész és a környezete a Bibliába'><img src=img/biblia.gif border=0 align=absmiddle></a>";
    }
    if (!empty($ujszov_hely)) {
        $van_u = true;
        $tomb1 = explode(',', $ujszov_hely);
        $tomb2 = explode('-', $tomb1[1]);
        $tomb3 = explode(' ', $tomb1[0]);
        $konyv = $tomb3[0];
        $fej = $tomb3[1];
        $vers = $tomb2[0];
        $link = "http://www.kereszteny.hu/biblia/showchapter.php?reftrans=1&abbook=$konyv&numch=$fej#$vers";
        $ujszov_biblia.="<a href=$link target=_blank title='ez a rész és a környezete a Bibliába'><img src=img/biblia.gif border=0 align=absmiddle></a>";
    }
    if (!empty($evang_hely)) {
        $van_e = true;
        $tomb1 = explode(',', $evang_hely);
        $tomb2 = explode('-', $tomb1[1]);
        $tomb3 = explode(' ', $tomb1[0]);
        $konyv = $tomb3[0];
        $fej = $tomb3[1];
        $vers = $tomb2[0];
        $link = "http://www.kereszteny.hu/biblia/showchapter.php?reftrans=1&abbook=$konyv&numch=$fej#$vers";
        $evang_biblia.="<a href=$link target=_blank title='ez a rész és a környezete a Bibliába'><img src=img/biblia.gif border=0 align=absmiddle></a>";
    }

    if ($van_o or $van_u or $van_e) {

        //Főcím2
        $adatT[0] = alapnyelv('Napi igehelyek');
        $tipus = 'fomenucim2';
        $kod.=formazo($adatT, $tipus);

        if ($van_o)
            $tartalom.="<div class=alapkizart><b>$oszov_hely</b> $oszov_biblia<br><br>$oszov</div>";
        if ($van_o and $van_u)
            $tartalom.='<hr>';
        if ($van_u)
            $tartalom.="<div class=alapkizart><b>$ujszov_hely</b> $ujszov_biblia<br><br>$ujszov</div>";
        if ($van_e and ( $van_o or $van_u))
            $tartalom.='<hr>';
        if ($van_e)
            $tartalom.="<div class=alapkizart><b>$evang_hely</b> $evang_biblia<br><br>$evang</div>";

        $adatT[2] = $tartalom;
        $tipus = 'doboz';
        $kod.=formazo($adatT, $tipus);
    }

    return $kod;
}

function igenaptar_fooldal() {

    //Főcím
    $adatT[0] = alapnyelv('igenaptár');
    $tipus = 'fomenucim';
    $cim1 = formazo($adatT, $tipus);

    $datum = date('Y-m-d');
    $kod = igenaptar_viewnap($datum);

    $kod = str_replace($cim1, '', $kod);

    return $kod;
}

function igenaptar_gview() {
    global $_GET, $linkveg, $db_name;

    $id = $_GET['id'];
    $kulcsszo = $_GET['kulcsszo'];

    //Főcím
    $adatT[0] = alapnyelv('igenaptár');
    $tipus = 'fomenucim';
    $kod.=formazo($adatT, $tipus);

    $query = "select ev,idoszak,nap,unnep,intro,gondolat from igenaptar where id='$id'";
    $lekerdez = mysql_db_query($db_name, $query);
    list($ev, $idoszak, $nap, $unnep, $intro, $leiras) = mysql_fetch_row($lekerdez);
    if (!empty($kulcsszo)) {
        $nap = str_replace($kulcsszo, "<font color=red>$kulcsszo</font>", $nap);
        $unnep = str_replace($kulcsszo, "<font color=red>$kulcsszo</font>", $unnep);
        $intro = str_replace($kulcsszo, "<font color=red>$kulcsszo</font>", $intro);
        $leiras = str_replace($kulcsszo, "<font color=red>$kulcsszo</font>", $leiras);
    }
    $leiras = nl2br($leiras);
    $intro = nl2br($intro);

    if ((!empty($ev)) and ( $ev != '0'))
        $igenap.="$ev év, ";
    if (!empty($idoszak))
        $igenap.=idoszak($idoszak);
    if (!empty($nap))
        $igenap.=" $nap";

    if (!empty($igenap) and ! empty($unnep))
        $igenap = '<br>' . $igenap;

    $tartalom = "<div align=center><span class=alcim>$unnep</span><span class=kiscim>$igenap</span></div>";
    $tartalom.="<br><div class=alapkizart>$intro<br><br>$leiras</div>";

    //Vissza
    $adatT[4] = 'vissza';
    $adatT[5] = "javascript:history.go(-1);";
    $tipus = 'tovabb';
    $tovabb = formazo($adatT, $tipus);
    $tartalom.=$tovabb;

    $adatT[2] = $tartalom;
    $tipus = 'doboz';
    $kod.=formazo($adatT, $tipus);

    return $kod;
}

function igenaptar_szview() {
    global $_GET, $linkveg, $db_name;

    $id = $_GET['id'];
    $kulcsszo = $_GET['kulcsszo'];

    //Főcím
    $adatT[0] = alapnyelv('igenaptár');
    $tipus = 'fomenucim';
    $kod.=formazo($adatT, $tipus);

    $query = "select nev,intro,ho,nap,leiras from szentek where id='$id'";
    $lekerdez = mysql_db_query($db_name, $query);
    list($nev, $intro, $ho, $nap, $leiras) = mysql_fetch_row($lekerdez);
    if (!empty($kulcsszo)) {
        $nev = str_replace($kulcsszo, "<font color=red>$kulcsszo</font>", $nev);
        $intro = str_replace($kulcsszo, "<font color=red>$kulcsszo</font>", $intro);
        $leiras = str_replace($kulcsszo, "<font color=red>$kulcsszo</font>", $leiras);
    }
    $leiras = nl2br($leiras);
    $intro = nl2br($intro);

    $datumkiir = 'Ünnepe: ' . alapnyelv('ho' . $ho) . " $nap.";

    $tartalom = "<div align=center><span class=alcim>$nev</span><br><span class=kiscim>$datumkiir</span></div>";
    $tartalom.="<br><div class=alapkizart>$intro<br><br>$leiras</div>";

    //Vissza
    $adatT[4] = 'vissza';
    $adatT[5] = "javascript:history.go(-1);";
    $tipus = 'tovabb';
    $tovabb = formazo($adatT, $tipus);
    $tartalom.=$tovabb;

    $adatT[2] = $tartalom;
    $tipus = 'doboz';
    $kod.=formazo($adatT, $tipus);

    return $kod;
}

switch ($m_op) {
    case 'index':
        $tartalom = igenaptar_index();
        break;

    case 'view':
        $datum = $_GET['date'];
        $tartalom = igenaptar_viewnap($datum);
        break;

    case 'fooldal':
        $tartalom = igenaptar_fooldal();
        break;

    case 'gview':
        $tartalom = igenaptar_gview();
        break;

    case 'szview':
        $tartalom = igenaptar_szview();
        break;
}
?>
