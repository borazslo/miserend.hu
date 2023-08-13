<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class SearchResultsMasses extends Html {

    public function __construct() {

        global $user, $config;

        $mikor = $_REQUEST['mikor'];
        $mikordatum = $_REQUEST['mikordatum'];
        if ($mikor != 'x')
            $mikordatum = $mikor;

        $mikor2 = $_REQUEST['mikor2'];
        $mikorido = $_REQUEST['mikorido'];
        $varos = $_REQUEST['varos'];
        $ehm = $_REQUEST['ehm'];
        $espkerT = $_REQUEST['espkerT'];
        $nyelv = $_REQUEST['nyelv'];
        $zene = $_REQUEST['zene'];
        $kor = $_REQUEST['kor'];
        $ritus = $_REQUEST['ritus'];
        $ige = $_REQUEST['ige'];


        $min = $_REQUEST['min'];
        if (!isset($min))
            $min = 0;
        $leptet = $_REQUEST['leptet'];
        if (!isset($leptet))
            $leptet = 25;

        $ma = date('Y-m-d');
        $holnap = date('Y-m-d', (time() + 86400));
      
        if ($ehm > 0) {
            $ehmnev = DB::table('egyhazmegye')->where('id',$ehm)->pluck('nev')[0];            
        }
                
        if ($espkerT[$ehm] > 0) {
            $espkernev = DB::table('espereskerulet')->where('id',$espkerT[$ehm])->pluck('nev')[0];            
        }
                
        $zeneT = array('g' => 'gitáros', 'o' => 'orgonás', 'cs' => 'csendes', 'na' => 'meghátorazatlan');
        $korT = array('csal' => 'családos', 'd' => 'diák', 'ifi' => 'ifjúsági', 'na' => 'meghátorazatlan');
        $ritusT = array('gor' => 'görögkatolikus', 'rom' => 'római katolikus', 'regi' => 'régi rítusú');
        $nyelvekT = array('h' => 'magyar', 'en' => 'angol', 'de' => 'német', 'it' => 'olasz', 'va' => 'latin', 'gr' => 'görög', 'sk' => 'szlovák', 'hr' => 'horvát', 'pl' => 'lengyel', 'si' => 'szlovén', 'ro' => 'román', 'fr' => 'francia', 'es' => 'spanyol');

        $tartalom.="\n<span class=kiscim>Keresési paraméterek:</span><br><span class=alap>";
        if (isset($_REQUEST['kulcsszo']) AND $_REQUEST['kulcsszo'] != '') {
            $tartalom.="<img src=/img/negyzet_lila.gif align=absmidle> Kulcsszó: " . $_REQUEST['kulcsszo'] . "<br/>";
            $leptet_urlap.="<input type=hidden name=kulcsszo value='" . $_REQUEST['kulcsszo'] . "'>";
        }
        if (isset($_REQUEST['hely']) AND $_REQUEST['hely'] != '') {
            $_REQUEST['hely_geocode'] = mapquestGeocode($_REQUEST['hely']);
            $tartalom.="<img src=/img/negyzet_lila.gif align=absmidle> " . $_REQUEST['hely'] . " + " . $_REQUEST['tavolsag'] . " km<br/><img src='" . $_REQUEST['hely_geocode']['mapUrl'] . "'><br/>";
            $leptet_urlap.="<input type=hidden name=hely value='" . $_REQUEST['hely'] . "'>";
            $leptet_urlap.="<input type=hidden name=tavolsag value='" . $_REQUEST['tavolsag'] . "'>";
        }

        if (!empty($varos)) {
            $varos = ucfirst($varos);
            $tartalom.="<img src=/img/negyzet_lila.gif align=absmidle> $varos településen<br/>";
            $leptet_urlap.="<input type=hidden name=varos value='$varos'>";
        }
        if (isset($_REQUEST['gorog']) AND $_REQUEST['gorog'] == 'gorog') {
            $tartalom.="<br><img src=/img/negyzet_lila.gif align=absmidle> Csak görögkatolikus templomokban.";
            $leptet_urlap.="<input type=hidden name=gorog value='gorog'>";
        }
        if (isset($_REQUEST['tnyelv']) AND $_REQUEST['tnyelv'] != '0') {
            $tartalom.="<br><img src=/img/negyzet_lila.gif align=absmidle>Amelyik templomban van '" . $_REQUEST['tnyelv'] . "' nyelvű mise.<br/>";
        }
        $leptet_urlap.="<input type=hidden name=tnyelv value='" . $_REQUEST['tnyelv'] . "'>";

        if (!empty($ehmnev)) {
            $tartalom.="<br><img src=/img/negyzet_lila.gif align=absmidle> $ehmnev egyházmegyében,";
            $leptet_urlap.="<input type=hidden name=ehm value='$ehm'>";
        }
        if (!empty($espkernev)) {
            $tartalom.="<br><img src=/img/negyzet_lila.gif align=absmidle> $espkernev espereskerületben,";
            $leptet_urlap.="<input type=hidden name=espkerT[$ehm] value='$espkerT[$ehm]'>";
        }
        if (!empty($ehmnev))
            $tartalom .= '<br/>';
        $tartalom.="<img src=/img/negyzet_lila.gif align=absmidle> ";
        if ($mikordatum == $ma) {
            $tartalom.='ma';
            $leptet_urlap.="<input type=hidden name=mikor value='$ma'>";
        } elseif ($mikordatum == $holnap) {
            $tartalom.='holnap';
            $leptet_urlap.="<input type=hidden name=mikor value='$holnap'>";
        } else {
            $mev = substr($mikordatum, 0, 4);
            $mho = substr($mikordatum, 5, 2);
            $mnap = substr($mikordatum, 8, 2);
            $mnapnev = date('w', mktime(0, 0, 0, $mho, $mnap, $mev));
            $napokT = array('vasárnap', 'hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat');
            $mikornap = ' ' . $napokT[$mnapnev];
            $tartalom.=$mikordatum . $mikornap;

            $leptet_urlap.="<input type=hidden name=mikor value='x'>";
            $leptet_urlap.="<input type=hidden name=mikordatum value='$mikordatum'>";
        }
        $tartalom.=' ';
        if ($mikor2 == 'de') {
            $tartalom.='délelőtt,';
            $leptet_urlap.="<input type=hidden name=mikor2 value='de'>";
        } elseif ($mikor2 == 'du') {
            $tartalom.='délután,';
            $leptet_urlap.="<input type=hidden name=mikor2 value='du'>";
        } elseif ($mikor2 == 'x') {
            $tartalom.=$mikorido;
            $leptet_urlap.="<input type=hidden name=mikor2 value='x'>";
            $leptet_urlap.="<input type=hidden name=mikorido value='$mikorido'>";
        } else {
            $tartalom.='egész nap,';
            $leptet_urlap.="<input type=hidden name=mikor2 value='0'>";
        }
        if (!empty($nyelv) or ( !empty($zene) AND count($zene) < 3) or ( !empty($kor) AND count($kor) < 4))
            $tartalom.="<br><img src=/img/negyzet_lila.gif align=absmidle> ";
        if (!empty($nyelv)) {
            $tartalom.="$nyelvekT[$nyelv] nyelvű, ";
            //		$leptet_urlap.="<input type=hidden name=nyelv value='$nyelv'>";
        }
        $leptet_urlap.="<input type=hidden name=nyelv value='$nyelv'>";

        if (!empty($zene)) {
            foreach ($zene as $z) {
                if (count($zene) < 3)
                    $tartalom.="$zeneT[$z], ";
                $leptet_urlap.="<input type=hidden name=zene[] value='$z'>";
            }
        }
        if (!empty($kor)) {
            foreach ($kor as $k) {
                if (count($kor) < 4)
                    $tartalom.="$korT[$k], ";
                $leptet_urlap.="<input type=hidden name=kor[] value='$k'>";
            }
        }
        if (!empty($ritus)) {
            $tartalom.="<br><img src=/img/negyzet_lila.gif align=absmidle> $ritusT[$ritus]";
            $leptet_urlap.="<input type=hidden name=ritus value='$ritus'>";
        }
        if (!empty($ige)) {
            $tartalom.="<br><img src=/img/negyzet_lila.gif align=absmidle> igeliturgiák is";
            $leptet_urlap.="<input type=hidden name=ige value='yes'>";
        }

        $tartalom.="</span><br/>" . LiturgicalDayAlert('html', $mikordatum);

        if (!empty($_REQUEST['leptet']))
            $visszalink = "?";
        else
            $visszalink = "javascript:history.go(-1);";
        $templomurlap = "<img src=/img/space.gif width=5 height=6><br><a href=$visszalink class=link><img src=/img/search.gif width=16 height=16 border=0 align=absmiddle hspace=2><b>Vissza a főoldali keresőhöz</b></a><br><img src=/img/space.gif width=5 height=6>";

        $results = searchMasses($_REQUEST, $min, $leptet);
        $mennyi = $results['sum'];

        $kezd = $min + 1;
        $prev = $min - $leptet;
        if ($prev < 0)
            $prev = 0;
        $veg = $mennyi;
        if ($min > 0) {
            $leptetprev.="\n<form method=post><input type=hidden name=q value=SearchResultsMasses><input type=hidden name=m_op value=keres><input type=\"hidden\" id=\"misekereses\" name=\"misekereses\" value=\"1\">";
            $leptetprev.=$leptet_urlap;
            $leptetprev.="<input type=hidden name=min value=$prev>";
            $leptetprev.="\n<input type=submit value=Előző class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
        }
        if ($mennyi > $leptet) {
            $veg = $min + $leptet;

            $next = $min + $leptet;

            if ($mennyi > $min + $leptet) {
                $leptetnext.="\n<form method=post><input type=hidden name=q value=SearchResultsMasses><input type=hidden name=m_op value=keres><input type=hidden name=min value=$next><input type=\"hidden\" id=\"misekereses\" name=\"misekereses\" value=\"1\">";
                $leptetnext.=$leptet_urlap;
                $leptetnext.="\n<input type=submit value=Következő class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
            }
        }



        if ($mennyi == 0) {
            $tartalom.='<br/><span class=alap><strong>Sajnos nincs találat</strong></span>';
            //$tartalom.='<span class=alap>Elnézést kérünk, a kereső technikai hiba miatt nem üzemel. Javításán már dolgozunk.</span>';
        } else {
            //$tartalom.="<span class=kiscim>Összesen $mennyi templomban van megfelelő mise.</span><br><br>";
            $tartalom.="<br><span class=alap>Összesen: $mennyi templomban van megfelelő mise.<br>Listázás: $kezd - $veg</span><br><br>";

            //echo "<pre>".print_r($results['results'],1)."</pre>";
            foreach ($results['churches'] as $result) {
                $tartalom .= "<img src=/img/templom1.gif align=absmiddle width=16 height=16 hspace=2>
				<a href='/templom/" . $result['tid'] . "' class=felsomenulink><b>" . $result['nev'] . "</b> <font color=#8D317C>(" . $result['varos'] . ")</font></a><br><span class=alap style=\"margin-left: 20px; font-style: italic;\">" . $result['ismertnev'] . "</span>";
                
                $tartalom.=$ertek . '<br> &nbsp; &nbsp; &nbsp;';

                if ($_REQUEST['mikor'] == 'x')
                    $_REQUEST['mikor'] = $_REQUEST['mikordatum'];
                //$masses = getMasses($result['tid'],$_REQUEST['mikordatum']);
                //$masses = searchMasses(array_merge(array('templom'=>$result['tid']),$_REQUEST));
                foreach ($result['masses'] as $mass) {
                    $tartalom .="<img src=/img/clock.gif width=16 height=16 align=absmiddle hspace=2><span class=alap>" . substr($mass['ido'], 0, 5) . "</span>";

                    $mass['nyelv'] = decodeMassAttr($mass['nyelv']);
                    foreach ($mass['nyelv'] as $milyen)
                        $tartalom.= '<img src="/img/' . $milyen['file'] . '" class="massinfo" width=14 title="' . $milyen['description'] . '"" height=14 align=absmiddle style="margin-top:0px;margin-left:1px">
    					<span class="massfullinfo" style="display:none" >' . $milyen['description'] . '</span>';

                    $mass['milyen'] = decodeMassAttr($mass['milyen']);
                    foreach ($mass['milyen'] as $milyen)
                        $tartalom.= '<img src="/img/' . $milyen['file'] . '" class="massinfo" width=14 title="' . $milyen['description'] . '"" height=14 align=absmiddle style="margin-top:0px;margin-left:1px">
    					<span class="massfullinfo" style="display:none">' . $milyen['description'] . '</span>';

                    if ($mass['megjegyzes'] != '')
                        $tartalom.= '<img src="/img/info2.gif" class="massinfo" width=14 title="' . $milyen['megjegyzes'] . '"  height=14 align=absmiddle style="margin-top:0px;margin-left:1px">
					<span class="massfullinfo" style="display:none">' . $mass['megjegyzes'] . '</span>';
                }
                //$tartalom .= print_r($masses,1);

                $tartalom.="<br><img src=/img/space.gif width=4 height=8><br>";
            }
        }

        //Léptetés
        if ($mennyi > $min + $leptet) {
            $next = $min + $leptet;
            $leptetes = "<br><form method=post><input type=hidden name=q value=SearchResultsMasses><input type=hidden name=m_op value=keres><input type=\"hidden\" id=\"keresestipus\" name=\"keresestipus\" value=\"1\">";
            $leptetes.=$leptet_urlap;
            $leptetes.="<input type=submit value=Következő class=urlap><input type=text name=leptet value=$leptet class=urlap size=2><input type=hidden name=min value=$next></form>";
        }
        $tartalom.=$leptetprev . $leptetnext;

        $this->setTitle("Szentmise kereső");

        $this->content = $tartalom;
        $this->templomurlap = $templomurlap;
        $this->template = 'search/resultsmasses.twig';
    }

}
