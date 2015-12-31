<?php

namespace Html;

class SearchResultsChurches extends Html {

    public function __construct() {
        global $user, $config;

        $this->input = $_REQUEST;

        $search = \Eloquent\Church::where('ok', 'i');
        if ($this->input['kulcsszo']) {
            $keyword = preg_replace("/\*/","%",$this->input['kulcsszo']);
            $search->whereShortcutLike($keyword, 'name');
        }
        if ($this->input['varos']) {
            $keyword = preg_replace("/\*/","%",$this->input['varos']);
            $search->whereShortcutLike($keyword, 'administrative');
        }

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


        $varos = $_POST['varos'];
        $kulcsszo = $_POST['kulcsszo'];
        $ehm = $_POST['ehm'];
        //	$megye=$_POST['megye'];
        if (empty($_POST['espker'])) {
            $espkerpT = $_POST['espkerT'];
            $espker = $espkerpT[$ehm];
        } else
            $espker = $_POST['espker'];


        //Templom űrlap
        $templomurlap = "\n<div style='display: none'><form method=post><input type=hidden name=q value=SearchResultsChurches><input type=hidden name=m_op value=keres></div>";
        $templomurlap .="<input type=hidden id=keresestipus name=keresestipus value=0>";
        //$templomurlap .= "<input type=hidden name=tavolsag id=tavolsag size=20 class=keresourlap value='".$_REQUEST['tavolsag']."'>";
        //$templomurlap .= "<input type=hidden name=hely id=tavolsag size=20 class=keresourlap value='".$_REQUEST['hely']."'>";
        $templomurlap.="\n<img src=img/space.gif width=5 height=10><br><span class=kiscim>Település: </span><input type=text name=varos id=varos size=20 class=keresourlap value='$varos'><br><img src=img/space.gif width=5 height=8>";

        $templomurlap.="<br><span class=kiscim>Kulcsszó: </span><input type=text name=kulcsszo id='keyword' size=20 class=keresourlap value='$kulcsszo'><br><img src=img/space.gif width=5 height=8>";

        //Egyházmegye
        $templomurlap.="<br><span class=kiscim>Egyházmegye: </span><br><img src=img/space.gif width=5 height=5><br><img src=img/space.gif width=10 height=5><select name=ehm class=keresourlap onChange=\"if(this.value!=0) {";
        foreach ($ehmT as $id => $nev) {
            $templomurlap.="document.getElementById($id).style.display='none'; ";
        }
        $templomurlap.="document.getElementById(this.value).style.display='inline'; document.getElementById('valassz').style.display='none'; } else {";
        foreach ($ehmT as $id => $nev) {
            $templomurlap.="document.getElementById($id).style.display='none'; ";
        }
        $templomurlap.="document.getElementById('valassz').style.display='inline';}\"><option value=0>mindegy</option>";
        foreach ($ehmT as $id => $nev) {
            $templomurlap.="<option value=$id";
            if ($id == $ehm)
                $templomurlap.=' selected';
            $templomurlap.=">$nev</option>";

            if ($id == $ehm)
                $espkerurlap.="<select id=$id name=espkerT[$id] class=keresourlap style='display: inline'><option value=0>mindegy</option>";
            else
                $espkerurlap.="<select id=$id name=espkerT[$id] class=keresourlap style='display: none'><option value=0>mindegy</option>";
            if (is_array($espkerT[$id])) {
                foreach ($espkerT[$id] as $espid => $espnev) {
                    $espkerurlap.="<option value=$espid";
                    if ($espker == $espid)
                        $espkerurlap.=' selected';
                    $espkerurlap.=">$espnev</option>";
                }
            }
            $espkerurlap.="</select>";
        }
        $templomurlap.="</select><br><img src=img/space.gif width=5 height=8>";

        //Espereskerület
        $templomurlap.="<br><span class=kiscim>Espereskerület: </span><br><img src=img/space.gif width=5 height=5><br><img src=img/space.gif width=10 height=5>";
        if (empty($ehm))
            $templomurlap.="<div id='valassz' style='display: inline' class=keresourlap>Először válassz egyházmegyét.</div>";
        $templomurlap.=$espkerurlap;
        $templomurlap.="<br><img src=img/space.gif width=5 height=8>";

        $templomurlap.="\n<br><img src=img/space.gif width=5 height=10><div align=right><input type=submit value=keresés class=keresourlap><br><img src=img/space.gif width=5 height=10></div><div style='display: none'></form></div>";


        $postdata.="<input type=hidden name=varos value='$varos'>";
        $postdata.="<input type=hidden name=tavolsag value='" . $_REQUEST['tavolsag'] . "'>";
        $postdata.="<input type=hidden name=hely value='" . $_REQUEST['hely'] . "'>";
        $postdata.="<input type=hidden name=kulcsszo value='$kulcsszo'>";
        $postdata.="<input type=hidden name=gorog value='" . $_REQUEST['gorog'] . "'>";
        $postdata.="<input type=hidden name=tnyelv value='" . $_REQUEST['tnyelv'] . "'>";

        $postdata.="<input type=hidden name=espker value='$espker'>";
        $postdata.="<input type=hidden name=ehm value='$ehm'>";

        $min = $_POST['min'];
        $leptet = $_POST['leptet'];
        if ($min < 0 or empty($min))
            $min = 0;
        if (empty($leptet))
            $leptet = 20;

        $results = searchChurches($_POST, $min, $leptet);

        $mennyi = $results['sum'];

        if ($mennyi == 1) {
            //ga('send','event','Outgoing Links','click','".$pleb_url."');
            //header ("Location: /templom/".$talalat['id']);
            echo "
        <script type='text/javascript'>
            (function(i,s,o,g,r,a,m){i[\"GoogleAnalyticsObject\"]=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,\"script\",\"//www.google-analytics.com/analytics.js\",\"ga\");

    ga(\"create\", \"UA-3987621-4\", \"miserend.hu\");
    ga('send','event','Search','fast','" . $varos . $kulcsszo . $ehm . "');
    
    window.location = '/templom/" . $results['results'][0]['id'] . "';
           
         </script>";

            die();
        }

        $kezd = $min + 1;
        $veg = $mennyi;
        if ($min > 0) {
            $leptetprev.="\n<form method=post><input type=hidden name=q value=SearchResultsChurches><input type=hidden name=m_op value=keres>";
            $leptetprev.=$postdata;
            $leptetprev.="<input type=hidden name=min value=$prev>";
            $leptetprev.="\n<input type=submit value=Előző class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
        }
        if ($mennyi > $leptet) {
            $veg = $min + $leptet;
            $prev = $min - $leptet;
            if ($prev < 0)
                $prev = 0;
            $next = $min + $leptet;

            if ($mennyi > $min + $leptet) {
                $leptetnext.="\n<form method=post><input type=hidden name=q value=SearchResultsChurches><input type=hidden name=m_op value=keres><input type=hidden name=min value=$next>";
                $leptetnext.=$postdata;
                $leptetnext.="\n<input type=submit value=Következő class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
            }
        }

        $tartalom.="<span class=alap>Összesen: $mennyi találat<br>Listázás: $kezd - $veg</span><br><br>";

        if ($mennyi > 0) {
            foreach ($results['results'] as $templom) {
                $tid = $templom['id'];
                $tnev = $templom['nev'];
                $tismertnev = $templom['ismertnev'];
                $tvaros = $templom['varos'];
                $letrehozta = $templom['letrehozta'];
                $tartalom.="<a href='/templom/$tid' class=felsomenulink title='$tismertnev'><b>$tnev</b> <font color=#8D317C>($tvaros)</font></a>";
                if ($user->checkRole('miserend') OR $letrehozta == $user->login)
                    $tartalom.=" <a href='/templom/$tid/edit'><img src=/img/edit.gif title='szerkesztés' align=absmiddle border=0></a> "
                            . "<a href='/templom/$tid/editschedule'><img src=/img/mise_edit.gif align=absmiddle border=0 title='mise módosítása'></a>";
                if ($tismertnev != '')
                    $tartalom .= "<br/><span class=\"alap\" style=\"margin-left: 20px; font-style: italic;\">" . $tismertnev . "</span>";
                $tartalom.="<br><img src=/img/space.gif width=4 height=5><br>";
            }
            $tartalom.='<br>' . $leptetprev . $leptetnext;
        }
        else {
            $tartalom.='<span class=alap>A keresés nem hozott eredményt</span>';
        }

        $focim = "Keresés a templomok között";
        $variables = array(
            'focim' => $focim,
            'content' => $tartalom,
            'templomurlap' => $templomurlap,
            'design_url' => $config['path']['domain']);

        $variables['template'] = 'search/resultsChurches.twig';

        foreach ($variables as $key => $var) {
            $this->$key = $var;
        }
    }

}
