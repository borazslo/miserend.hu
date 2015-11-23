<?php

function idoszak($i) {
    switch($i) {
        case 'a': $tmp = 'Ádventi idő'; break;
        case 'k': $tmp = 'Karácsonyi idő'; break;
        case 'n': $tmp = 'Nagyböjti idő'; break;
        case 'h': $tmp = 'Húsvéti idő'; break;
        case 'e': $tmp = 'Évközi idő'; break;
		case 's': $tmp = 'Szent ünnepe'; break;
    }
    return $tmp;
}

function igenaptar_index() {

	$kod="<span class=alap>Válassz!</span>";

	return $kod;
}

function addszent($id) {
    global $linkveg,$sid,$m_id;

	$kod.=include('editscript2.php');

	$kod.="<p class=alcim>Szent / ünnep hozzáadása, módosítása</p>";

    $query="select nev,nevnap,intro,ho,nap,leiras,szin from szentek where id='$id'";
    if(!$lekerdez=mysql_query($query))
      echo '<p>HIBA!<br>'.mysql_error();
    list($nev,$nevnap,$intro,$ho,$nap,$leiras,$szin)=mysql_fetch_row($lekerdez);

    $kod.="\n<form method=post><input type=hidden name=id value=$id>";
    $kod.="\n<input type=hidden name=sid value=$sid><input type=hidden name=id value=$id>";
    $kod.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=addingszent>";

    $kod.="\n<span class=kiscim>Szent / ünnep neve:</span>";
	$kod.="\n<br><input tpye=text name=nev value='$nev' size=40 class=urlap>";

	$kod.="\n<br><br><span class=kiscim>Ünnepe:</span>";
    $kod.="<br><select name=ho class=urlap>";
    for($i=1;$i<=12;$i++) {
        $kod.= "<option value=$i";
        if($i==$ho) $kod.= ' selected';
        $kod.= '>'.alapnyelv("ho$i").'</option>';
    }
    $kod.= "</select> <input type=text name=nap value='$nap' size=2 maxlength=2 class=urlap>";

    $kod.="\n<br><br><span class=kiscim>Ünnep színe:</span><span class=alap><br>(vértanúnál piros, egyébként általában fehér)</span>";
	$kod.="\n<br><select name=szinsz class=urlap>";
	$kod.="<option";
	if($szin=='feher') $kod.=' selected';
	$kod.=">feher</option><option";
	if($szin=='piros') $kod.=' selected';
	$kod.=">piros</option><option";
	if($szin=='zold') $kod.=' selected';
	$kod.=">zold</option><option";
	if($szin=='lila') $kod.=' select';
	$kod.=">lila</option></select>";
	
	$kod.="\n<br><br><span class=kiscim>Névnap:</span><span class=alap><br>Az adott napon a szent alapján tartandó névnap (csak név)</span>";
	$kod.="\n<br><input tpye=text name=nevnap value='$nevnap' size=40 class=urlap>";

    $kod.="\n<br><br><span class=kiscim>Rövid leírás:</span>";
	$kod.="<br><textarea name=intro cols=60 rows=5 class=urlap>$intro</textarea>";

    $kod.="\n<br><br><span class=kiscim>Teljes leírás (elmélkedés):</span>";
	$kod.="<br><textarea name=szoveg cols=80 rows=50 class=urlap>$leiras</textarea>";

	$kod.="\n<br><br><input type=submit value=Mehet class=urlap></form>";

	return $kod;

}

function addingszent() {
	global $_POST;

	$id=$_POST['id'];
	$nev=$_POST['nev'];
	$ho=$_POST['ho'];
	$nap=$_POST['nap'];
	$szin=$_POST['szinsz'];
	$nevnap=$_POST['nevnap'];
	$intro=$_POST['intro'];
	$leiras=$_POST['szoveg'];

	if($id>0) {
		//Módosítás
		$parameter1='update';
		$parameter2="where id='$id'";
		$uj=false;
	}
	else {
		$parameter1='insert';
		$parameter2='';
		$uj=true;
	}

	$query="$parameter1 szentek set nev='$nev', ho='$ho', nap='$nap', szin='$szin', nevnap='$nevnap', intro='$intro', leiras='$leiras' $parameter2";
	if(!mysql_query($query)) echo 'HIBA!<br>'.mysql_error();
	if($uj) $id=mysql_insert_id();

	$kod.=addszent($id);

	return $kod;
}

function addige($id) {
    global $m_id,$ssid,$m_id;

	$kod.=include('editscript2.php');
	$kod.="<p class=alcim>Napi ünnep, gondolat (üzenet) hozzáadása, módosítása</p>";

	if($id>0) {
		$query="select szin,ev,idoszak,nap,oszov_hely,ujszov_hely,evang_hely,unnep,intro,gondolat from igenaptar where id='$id'";
		if(!$lekerdez=mysql_query($query))
			echo '<p>HIBA!<br>'.mysql_error();
		list($szing,$ev,$idoszak,$nap,$oszov_hely,$ujszov_hely,$evang_hely,$unnep,$intro,$gondolat)=mysql_fetch_row($lekerdez);
	}

    $kod.= "<form method=post><input type=hidden name=id value=$id>";
    $kod.= "<input type=hidden name=sid value=$sid>";
    $kod.= "<input type=hidden name=m_id value=$m_id>";
    $kod.= "<input type=hidden name=m_op value=addingige>";

//Időszak    
    $kod.="\n<span class=kiscim>Időszak:</span>";
	$kod.="\n<br><SELECT NAME=idoszak class=urlap>";
	$kod.='<option value=a';
	if($idoszak=='a') $kod.=' selected';
	$kod.='>Ádventi idő</option><option value=k';
	if($idoszak=='k') $kod.=' selected';
	$kod.='>Karácsonyi idő</option><option value=n';
	if($idoszak=='n') $kod.=' selected';
	$kod.='>Nagyböjti idő</option><option value=h';
	if($idoszak=='h') $kod.=' selected';
	$kod.='>Húsvéti idő</option><option value=e';
	if($idoszak=='e') $kod.=' selected';
	$kod.='>Évközi idő</option><option value=s';
	if($idoszak=='s') $kod.=' selected';
	$kod.='>Szent ünnepe</option></select>';

//Év
	$kod.="\n<br><br><span class=kiscim>Év:</span>";
	$kod.='<br><select name=ev class=urlap>';
	$kod.='<option value=0';
	if($ev=='0') $kod.=' selected';
	$kod.='>nincs</option><option value=A';
	if($ev=='A') $kod.=' selected';
	$kod.='>A</option><option value=B';
	if($ev=='B') $kod.=' selected';
	$kod.='>B</option><option value=C';
	if($ev=='C') $kod.=' selected';
	$kod.='>C</option></select>';

//Nap
	$kod.="\n<br><br><span class=kiscim>Nap:</span>";
	$kod.="\n<br><input type=text name=nap value='$nap' size=60 class=urlap maxlength=250>";	

//Ünnep  
  	$kod.="\n<br><br><span class=kiscim>Ünnep:</span><span class=alap><br>Az adott nap ünnepe, ha van</span>";
	$kod.="\n<br><input tpye=text name=unnep value='$unnep' size=60 maxlength=250 class=urlap>";

//Szín
    $kod.="\n<br><br><span class=kiscim>Ünnep színe:</span>";
	$kod.="\n<br><select name=szing class=urlap>";
	$kod.="<option";
	if($szing=='feher') $kod.=' selected';
	$kod.=">feher</option><option";
	if($szing=='piros') $kod.=' selected';
	$kod.=">piros</option><option";
	if($szing=='zold') $kod.=' selected';
	$kod.=">zold</option><option";
	if($szing=='lila') $kod.=' selected';
	$kod.=">lila</option></select>";

    $kod.="\n<br><br><span class=kiscim>Napi gondolat (röviden -> főoldalon jelenik meg):</span>";
	$kod.="<br><textarea name=intro cols=80 rows=6 class=urlap>$intro</textarea>";

    $kod.="\n<br><br><span class=kiscim>Elmélkedés részletesebben:</span>";
	$kod.="<br><textarea name=szoveg cols=80 rows=40 class=urlap>$gondolat</textarea>";

  	$kod.="\n<br><br><span class=kiscim>Olvasmány hely:</span>";
	$kod.="\n<br><input tpye=text name=oszov_hely value='$oszov_hely' size=20 class=urlap>";
	if(!empty($oszov_hely)) {
		$tomb1=explode(',',$oszov_hely);
		$tomb2=explode('-',$tomb1[1]);
		$tomb3=explode(' ',$tomb1[0]);
		$konyv=$tomb3[0];
		$fej=$tomb3[1];
		$vers=$tomb2[0];
		$link="http://www.kereszteny.hu/biblia/showchapter.php?reftrans=1&abbook=$konyv&numch=$fej#$vers";
		$kod.="<a href=$link target=_blank class=link><img src=img/biblia.gif border=0 alt=Biblia align=absmiddle></a>";
	}

    $kod.="\n<br><br><span class=kiscim>Szentlecke hely:</span>";
	$kod.="\n<br><input tpye=text name=ujszov_hely value='$ujszov_hely' size=20 class=urlap>";
	if(!empty($ujszov_hely)) {
		$tomb1=explode(',',$ujszov_hely);
		$tomb2=explode('-',$tomb1[1]);
		$tomb3=explode(' ',$tomb1[0]);
		$konyv=$tomb3[0];
		$fej=$tomb3[1];
		$vers=$tomb2[0];
		$link="http://www.kereszteny.hu/biblia/showchapter.php?reftrans=1&abbook=$konyv&numch=$fej#$vers";
		$kod.="<a href=$link target=_blank class=link><img src=img/biblia.gif border=0 alt=Biblia align=absmiddle></a>";
	}

    $kod.="\n<br><br><span class=kiscim>Evangélium hely:</span>";
	$kod.="\n<br><input tpye=text name=evang_hely value='$evang_hely' size=20 class=urlap>";
	if(!empty($evang_hely)) {
		$tomb1=explode(',',$evang_hely);
		$tomb2=explode('-',$tomb1[1]);
		$tomb3=explode(' ',$tomb1[0]);
		$konyv=$tomb3[0];
		$fej=$tomb3[1];
		$vers=$tomb2[0];
		$link="http://www.kereszteny.hu/biblia/showchapter.php?reftrans=1&abbook=$konyv&numch=$fej#$vers";
		$kod.="<a href=$link target=_blank class=link><img src=img/biblia.gif border=0 alt=Biblia align=absmiddle></a>";
	}    

	$kod.="\n<br><br><input type=submit value=Mehet></form>";

	return $kod;   
}

function addingige() {
	global $_POST;

	$id=$_POST['id'];
	$idoszak=$_POST['idoszak'];
	$szing=$_POST['szing'];
	$ev=$_POST['ev'];
	$nap=$_POST['nap'];
	$unnep=$_POST['unnep'];
	$intro=$_POST['intro'];
	$gondolat=$_POST['szoveg'];
	$oszov_hely=$_POST['oszov_hely'];
	$ujszov_hely=$_POST['ujszov_hely'];
	$evang_hely=$_POST['evang_hely'];

	if($id>0) {
		//módosítás
		$uj=false;
		$parameter1='update';
		$parameter2=" where id='$id'";
	}
	else {
		//beszúrás
		$uj=true;
		$parameter1='insert';
		$parameter2='';
	}

	$query="$parameter1 igenaptar set szin='$szing', ev='$ev', idoszak='$idoszak', nap='$nap', oszov_hely='$oszov_hely', ujszov_hely='$ujszov_hely',  evang_hely='$evang_hely', unnep='$unnep', intro='$intro', gondolat='$gondolat' $parameter2";
	if(!mysql_query($query)) echo 'HIBA<br>'.mysql_error();
	if($uj) $id=mysql_insert_id();

	$kod.=addige($id);

	return $kod;
}

function gondolatok() {
    global $design_url,$db_name,$linkveg,$szin,$m_id,$sessid;


//Új bejegyzés
    $urlap.= "\n<div><a href=?m_id=$m_id&m_op=addige$linkveg class=link><b> - Új bejegyzés hozzáadása</b></a></div>";

//Módosításnál (vagy kiválasztja, vagy kulcsszó alapján keresi
    $urlap.= "\n<form method=post><input type=hidden name=m_op value=gondolatokmod>";
	$urlap.= "<input type=hidden name=sessid value=$sessid><input type=hidden name=m_id value=$m_id>";
    $urlap.= "<br><span class=link><b>- Meglévő bejegyzés módostása</b> (keresés):</span>";

//Teljes lista
	$urlap.="\n<br><br><span class=alap>Konkrét igenap:</span><br><select name=ige class=urlap><option value=0>Keresés a lenti mezők segítségével</option>";
	$query="select id,idoszak,ev,nap from igenaptar order by idoszak asc, ev asc, nap asc";
    if(!$lekerdez=mysql_query($query)) $kod.= '<p class=hiba>HIBA a lekérdezésnél!<br>'.mysql_error();
    while(list($gid,$gidoszak,$gev,$gnap)=mysql_fetch_row($lekerdez)) {
        $kiiras=idoszak($gidoszak);
        $kiiras.=',';
        if($gev!='' and $gev!='0') $kiiras.=" $gev év,";
        $kiiras.=" $gnap";
        $urlap.= "<option value=$gid";
        //if($ige==$gid) $urlap.= ' selected';
        $urlap.= ">$kiiras</option>";
    }
    $urlap.= '</select>';

	//Időszak (pl. Ádventi idő)
    $urlap.= '<br><br><span class=alap>Időszak: </span><br><select name=idoszak class=urlap>';
    $urlap.= '<option value=0>Nem tudom</option>';
    $urlap.= '<option value=a>Ádventi idő</option><option value=k>Karácsonyi idő</option>
         <option value=n>Nagyböjti idő</option><option value=h>Húsvéti idő</option>
         <option value=e>Évközi idő</option><option value=s>Szent ünnepe</option></select>';
    //Év (pl. A év)
    $urlap.= '<br><br><span class=alap>Év:</span><br><select name=ev class=urlap><option value=0>Nem tudom / nincs</option>
         <option value=A>A év</option><option value=B>B év</option><option value=C>C év</option>
         </select>';

    $urlap.= '<br><br><span class=alap>Kulcsszó (a leírásban keres)</span><br><input type=text name=kulcsszo size=25 class=urlap>';
    $urlap.= '<br><br><input type=submit value=Keres class=urlap>';
    $urlap.= '</form>';

	
	$kod="<p class=alcim>Gondolatok szerkesztése</p>";
	$kod.=$urlap;

	return $kod;
}

function gondolatokmod() {
    global $_POST,$_GET,$m_id,$linkveg,$db_name;

	$ige=$_POST['ige'];
	if(empty($ige)) $ige=$_GET['ige'];
	$ev=$_POST['ev'];
	if(empty($ev)) $ev=$_GET['ev'];
	$kulcsszo=$_POST['kulcsszo'];
	if(empty($kulcssszo)) $kulcsszo=$_GET['kulcsszo'];
	$idoszak=$_POST['idoszak'];
	if(empty($idoszak)) $idoszak=$_GET['idoszak'];

	//Főcím
	$kod.="<p class=alcim>Gondolatok módosítása</p>";

	$min=$_POST['min'];
	if(!isset($min)) $min=$_GET['min'];
    if(!isset($min)) $min=0;
    $leptet=30;
    $next=$min+$leptet;
    $prev=$min-$leptet;
    if($prev<0) $prev=0;
    
    $keres="select id,szin,ev,idoszak,nap,unnep from igenaptar";
    if($idoszak!='0' and $idoszak!='') $queryT[]="idoszak='$idoszak'";
    if($ev!='0' and $ev!='') $queryT[]="ev='$ev'";
    if($kulcsszo!='' and $kulcsszo!='0') $queryT[]="(intro like '%$kulcsszo%' or gondolat like '%$kulcsszo%' or oszov like '%$kulcsszo%' or ujszov like '%$kulcsszo%' or evang like '%$kulcsszo%')";
	if(is_array($queryT)) {
		$query=implode(' and ',$queryT);
		if(!empty($query)) $query=" where $query";
	}
	if($ige>0) $keres.=" where id='$ige'";
	else $keres.=$query;
    if(!$lekerdez=mysql_db_query($db_name,$keres)) echo 'HIBA<br>'.$keres.'<br>'.mysql_error();
    $mennyi=mysql_num_rows($lekerdez);
    $keres.=" limit $min,$leptet";
    $lekerdez=mysql_query($keres);
    
    $kezd=$min+1;
    if($mennyi==0) $kezd=0;
    if($mennyi>$next) $vege=$next;
    else $vege=$mennyi;
    
    $kod.= '<div class=alcim>Gondolatok módosítása</div>';
    $kod.= "<div class=alap><b>Keresés eredménye $mennyi találat</b><br>";
    $kod.= "Listázás: $kezd - $vege</div>";
    
    while(list($id,$szin,$ev1,$idoszak1,$nap1,$unnep)=mysql_fetch_row($lekerdez)) {
		$kiiras1='';
		$kiiras2='';
        if($idoszak1=='a') $kiiras1=' Ádventi';
        elseif($idoszak1=='k') $kiiras1.=' Karácsonyi';
        elseif($idoszak1=='n') $kiiras1.=' Nagyböjti';
        elseif($idoszak1=='h') $kiiras1.=' Húsvéti';
        elseif($idoszak1=='e') $kiiras1.=' Évközi';
        $kiiras1.=' idő';
        if($ev1!='0' and $ev1!='') $kiiras2=" $ev1 év, ";
        else $kiiras2=' ';
        if($nap1!='0') $kiiras2.="$nap1";
        $kod.= "<br><a href=?m_id=$m_id&m_op=addige&id=$id$linkveg class=link>- <b>$kiiras1</b>$kiiras2 ($unnep, $szin)</a>
        <a href=?m_id=$m_id&m_op=delgondolat&id=$id$linkveg><img src=img/del.jpg width=12 height=11
        alt='Gondolat törlése' border=0></a>";

    }

    //Léptetés ($tipus,$ido,$ev,$nap,$kulcsszo,$min)
    $kod.= '<p>';
    if($min>0) {
        $x=$leptet;
        $kod.= "<a href='?m_id=$m_id&m_op=gondolatokmod&idoszak=$idoszak&ev=$ev&nap=$nap&kulcsszo=$kulcsszo&min=$prev$linkveg' class=link1>Előző $x találat</a>";
    }
    if($mennyi>$next) {
        if($min>0) $kod.= ' - ';
        if($mennyi>$next+$leptet) $x=$leptet;
        else $x=$mennyi-$next;
        $kod.= "<a href='?m_id=$m_id&m_op=gondolatokmod&idoszak=$idoszak&ev=$ev&nap=$nap&kulcsszo=$kulcsszo&min=$next$linkveg' class=link1>Következő $x találat</a>";
    }

	return $kod;
}

function delgondolat() {
    global $m_id,$linkveg,$_GET;

	$id=$_GET['id'];

	$kod.="<p class=alcim>Gondolatok törlése</p>";
    
    $kod.= '<p class=hiba>FIGYELEM! Valóban törölni akarod a következő gondolatot?</p>';
    list($idoszak,$ev,$nap)=mysql_fetch_row(mysql_query("select idoszak,ev,nap from igenaptar where id='$id'"));
    if($idoszak=='a') $kiiras1.=' - Ádventi';
    elseif($idoszak=='k') $kiiras1.=' Karácsonyi';
    elseif($idoszak=='n') $kiiras1.=' Nagyböjti';
    elseif($idoszak=='h') $kiiras1.=' Húsvéti';
    elseif($idoszak=='e') $kiiras1.=' Évközi';
    $kiiras1.=' idő';
    if($ev!='0' and !empty($ev)) $kiiras2=" $ev év, ";
    else $kiiras2=' ';
    if($nap!='0') $kiiras2.="$nap";

    $kod.= "<p class=kiscim><i>$kiiras1 - $kiiras2</p>";
    $kod.= '<p class=hiba>Törlés után visszaállításra nincs lehetőség!
         <br><small>Törlés helyett adott esetben választhatod a módosítást is!</small></p>';

    $kod.= "<a href=?m_id=$m_id&m_op=deletegondolat&id=$id$linkveg class=link>Töröl</a> -
         <a href=?m_id=$m_id&m_op=addgondolat&id=$id$linkveg class=link>Módosítás</a> - <a href=?m_id=$m_id&m_op=gondolatok$linkveg class=link>Mégsem</a>";

	return $kod;
}

function deletegondolat() {
	global $_GET;

	$id=$_GET['id'];

    $query="delete from igenaptar where id='$id'";
    if(!mysql_query($query)) {
        $kod.= '<p class=hiba>HIBA a törlésnél!<br>'.mysql_error();
    }

	else $kod.=gondolatok();

	return $kod;    
}

function szentek() {
    global $m_id,$linkveg,$sessid;

	$kod.="<p class=alcim>Szentek / ünnepek hozzáadása, módosítása</p>";

//Űj bejegyzés
    $kod.= "\n<a href=?m_id=$m_id&m_op=addszent$linkveg class=link><b>- Új bejegyzés hozzáadása</b></a>";
    $kod.= "\n<br><br><div class=link><b>Meglévő bejegyzés módostása:</b></div>";

//Módosításnál (vagy kiválasztja, vagy kulcsszó alapján keresi
    $kod.= "\n<form method=post><input type=hidden name=m_op value=szentekmod>";
	$kod.= "<input type=hidden name=sessid value=$sessid>";

    $query="select id,nev,ho,nap from szentek order by ho,nap";
    if(!$lekerdez=mysql_query($query)) $kod.= '<p class=hiba>HIBA!<br>'.mysql_error();
    $kod.= '<br><br><span class=alap>Szentek neve:</span> <br><select name=szid class=urlap>';
    $kod.= '<option value=0>--- inkább kulcsszó alapján keresem ---</option>';
    while(list($szid,$sznev,$ho,$nap)=mysql_fetch_row($lekerdez)) {
        $kod.= "<option value=$szid>$sznev ($ho-$nap)</option>";
    }
    $kod.= '</select>';
    $kod.= '<br><br><span class=alap>Kulcsszó (a névben és a teljes leírásban keres)</span><br><input type=text name=kulcsszo size=25 class=urlap>';
    $kod.= '<br><br><input type=submit value=Keres class=urlap>';
    $kod.= '</form>';

	return $kod;
}

function szentekmod() {
    global $m_id,$_GET,$_POST,$linkveg;

	$szid=$_POST['szid'];
	$kulcsszo=$_POST['kulcsszo'];
	if(!isset($kulcsszo)) $kulcsszo=$_GET['kulcsszo'];

	$kod.="<p class=alcim>Szentek / ünnepek módosítása</p>";


    if($szid!=0) {
        $query="select nev from szentek where id='$szid'";
        list($nev)=mysql_fetch_row(mysql_query($query));
       $kod.= "<div><a href=?m_id=$m_id&m_op=addszent&id=$szid$linkveg class=link><b>$nev</b> - Módosítás</a> - <a href=?m_id=$m_id&m_op=delszent&id=$szid$linkveg class=link><img src=img/del.jpg border=0> Töröl</a></div>";
    }
    else {
		$min=$_GET['min'];
        if(!isset($min)) $min=0;
        $leptet=20;
        $next=$min+$leptet;
        $prev=$min-$leptet;
        if($prev<0) $prev=0;

        $query="select id,nev,ho,nap from szentek where nev like '%$kulcsszo%' or leiras like '%$kulcsszo%'";
        $query1=$query." limit $min,$leptet";

        $lekerdez=mysql_query($query);
        $mennyi=mysql_num_rows($lekerdez);
        $kezd=$min+1;
        $vege=$min+$leptet;
        if($vege>$mennyi) $vege=$mennyi;

        $kod.= "<div class=alap><b>Összesen $mennyi találat</b>
             <br>Listázás: $kezd - $vege</div>";

        $lekerdez=mysql_query($query1);
        while(list($id,$nev,$ho,$nap)=mysql_fetch_row($lekerdez)) {
            $kod.= "<br><a href=?m_id=$m_id&m_op=addszent&id=$id$linkveg class=link><b>$nev</b> ($ho-$nap) - Módosítás</a> - <a href=?m_id=$m_id&m_op=delszent&id=$id$linkveg class=link>Töröl</a>";
        }
        $kod.= '<p class=alap>';
        if($min>0) $kod.= " <a href=?m_id=$m_id&m_op=szentekmod&kulcsszo=$kulcsszo&min=$prev$linkveg class=link1>Előző</a>";
        if($mennyi>$next) $kod.= " <a href=?m_id=$m_id&m_op=szentekmod&kulcsszo=$kulcsszo&min=$next$linkveg class=link1>Következő</a>";
    }

	return $kod;
}

function delszent() {
    global $m_id,$linkveg,$_GET;

	$id=$_GET['id'];

	$kod.="<p class=alcim>Szentek / ünnepek törlése</p>";

    $kod.= '<p class=hiba>FIGYELEM! Biztosan törölni akarod a következő szentet?</p>';

    list($szent)=mysql_fetch_row(mysql_query("select nev from szentek where id='$id'"));
    $kod.= "<p class=kiscim><i>$szent</p>";
    
    $kod.= '<p class=hiba>Törlés után visszaállításra nincs lehetőség!
         <br><small>Törlés helyett adott esetben választhatod a módosítást is!</small></p>';

    $kod.= "<a href=?m_id=$m_id&m_op=deleteszent&id=$id$linkveg class=link>Töröl</a> -
         <a href=?m_id=$m_id&m_op=addszent&id=$id$linkveg class=link>Módosítás</a> - <a href=?m_id=$m_id&m_op=szentek$linkveg class=link>Mégsem</a>";

	return $kod;
}

function deleteszent() {
	global $_GET;

	$id=$_GET['id'];

    mysql_query("delete from szentek where id='$id'");
    $kod.=szentek();

	return $kod;
}


function naptar($honap,$ev) {
    global $_POST,$_GET,$linkveg,$m_id;

    $kod.= '<span class=alcim>Liturgikus naptár</span><br><span class=alap><i>Itt kell beállítani az
    aktuális liturgikus naptárnak megfelelően, hogy az adott naphoz mely szent, illetve gondolat
    tartozik.</i></span><br><br>';
    
    define("EGYNAP", (60*60*24));
    if(!checkdate($honap,1,$ev)) {
        $mostTomb=getdate();
        $honap=$mostTomb["mon"];
        $ev=$mostTomb["year"];
    }
    $kezdet=mktime(0,0,0,$honap,1,$ev);
    $elsoNapTombje=getdate($kezdet);
    if($elsoNapTombje["wday"] == 0)
       $elsoNapTombje["wday"] = 6;
    else
       $elsoNapTombje["wday"]--;

    $kod.= "<form method=post><input type=hidden name=sessid value=$sessid><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=naptar>";
    $kod.= '<select name=ev>';
    for($x=2004;$x<=2010; $x++) {
        $kod.= "<option";
        $kod.= ($x == $ev) ? " selected":"";
        $kod.= ">$x\n";
    }


    $kod.= '</select><select name=honap>';

    $honapok=Array ("Január","Február","Március","Április","Május","Június","Július",
               "Augusztus","Szeptember","Október","November","December");

    for($x=1;$x<=count($honapok); $x++) {
        $kod.= "\t<option value=$x";
        $kod.= ($x == $honap)? " selected":"";
        $kod.= '>'.$honapok[$x-1]."\n";
    }


    $kod.= '</select><input type=submit value=Mutat></form>';

    $napok = Array ("Hétfő","Kedd","Szerda","Csütörtök","Péntek","Szombat","Vasárnap");


    $kod.= '<div align=center class=alcim>'.$ev.'. '.$honapok[$honap-1].'</div><br>';
    $kod.= '<table border=1 cellpadding=2 cellspacing=0>';

    foreach ($napok as $nap) {
      if($nap=='Vasárnap') $tulajdonsag='bgcolor=#FFF9F9 class=unnep';
      else $tulajdonsag='class=link';
      $kod.= "\t<td width=14% $tulajdonsag align=center><b>$nap</b></td>\n";
    }
    $kiirando=$kezdet;

    for($szamlalo=0;$szamlalo<(6*7);$szamlalo++) {
        $napTomb = getdate($kiirando);
        $moddatum=date('Y-m-d',$kiirando);
        //$katunnep = katolikus ünnep, amikor pirossal írjuk ki az ünnepet (vasárnap és fontosabb ünnepeken)
        if(($szamlalo%7)==6) {
            $unnep=1;
            $katunnep=1;
        }
        else {
            $unnep=0;
            $katunnep=0;
        }

        if((($szamlalo)%7)==0){
            if($napTomb[mon]!=$honap)
              break;
            $kod.= '</tr><tr>';
        }
        //ünnepek:
        if($napTomb[mon]==1 and $napTomb["mday"]==1) {$unnep=1;$katunnep=1;$msg='Újév';}
        elseif($napTomb[mon]==3 and $napTomb["mday"]==15) {$unnep=1;$msg='Nemzeti ünnep';}
        elseif($napTomb[mon]==8 and $napTomb["mday"]==20) {$unnep=1;$msg='Nemzeti ünnep';}
        elseif($napTomb[mon]==10 and $napTomb["mday"]==23) {$unnep=1;$msg='Nemzeti ünnep';}
        elseif($napTomb[mon]==11 and $napTomb["mday"]==1) {$unnep=1;$katunnep=1;$msg='Mindenszentek ünnepe';}
        elseif($napTomb[mon]==12 and $napTomb["mday"]==25) {$unnep=1;$katunnep=1;$msg='Karácsony';}
        elseif($napTomb[mon]==12 and $napTomb["mday"]==26) {$unnep=1;$katunnep=1;$msg='Karácsony';}
        else $msg='';
        if($katunnep==1) $class='unnep1';
        else $class='linkkicsi';

        if($szamlalo<$elsoNapTombje["wday"] || $napTomb["mon"] != $honap) {
            $kod.= '<td><br></td>';
        }
        else {
            $kod.= "\t<td align=center valign=top";
            //Ha ünnep, akkor piros
            $kod.= $unnep==1 ? " bgcolor=#FFF9F9><a class=unnep title='$msg'":" class=link";
            $kod.= '>'.$napTomb["mday"]."</a><a href=?m_id=$m_id&m_op=modnaptar&datum=$moddatum$linkveg class=$class><br>";

            //Megnézzük, hogy van-e hozzá esemény
			$szent=0;
			$ige=0;
            $nap=$napTomb[mday];
            $datum="$ev-$honap-$nap";
            $query="select ige,szent from lnaptar where datum='$datum'";
            if(!$eredmeny=mysql_query($query))
              $kod.= '<p class=hiba>HIBA a lekérdezésnél!<br>'.mysql_error();
            if(mysql_num_rows($eredmeny)>0) {
                list($ige,$szent)=mysql_fetch_row($eredmeny);
                if($szent>0) {
                    $query_sz="select nev from szentek where id='$szent'";
                    list($sznev)=mysql_fetch_row(mysql_query($query_sz));
                    $kod.= "$sznev";
                }
                elseif($ige>0) {
                    $queryg="select idoszak,ev,nap,unnep from igenaptar where id='$ige'";
                    list($gidoszak,$gev,$gnap,$gunnep)=mysql_fetch_row(mysql_query($queryg));
                    $kiiras=idoszak($gidoszak);
                    $kiiras.=',';
                    if($gev!='' and $gev!='0') $kiiras.=" $gev év,";
                    $kiiras.=" $gnap";
					if(!empty($gunnep)) $kiiras.="$gunnep unnepe";
                    $kod.= $kiiras;
                }
            }
            if(($szent==0) and ($ige==0)) $kod.= "HOZZÁAD";
            $kod.= "</a>";
            $kiirando += EGYNAP;
            $ujnaptomb=getdate($kiirando);
            if($ujnaptomb[mday]==$napTomb[mday]) $kiirando += EGYNAP;
            $kod.= "</td>\n";
        }
    }
    $kod.= '</tr></table>';

	return $kod;
}

function modnaptar() {
    global $_GET,$linkveg,$m_id,$sid;

	$datum=$_GET['datum'];

	$query="select ige,szent from lnaptar where datum='$datum'";
	list($ige,$szent)=mysql_fetch_row(mysql_query($query));

    $kod.= '<p class=alcim>Liturgikus naptár - hozzáadás, módosítás</p>';
    $kod.= '<form method=post><input type=hidden name=m_op value=modingnaptar>';
	$kod.= "\n<input type=hidden name=sid value=$sid><input type=hidden name=m_id value=$m_id>";
    $kod.= "<input type=hidden name=id value=$id>";
    $kod.= "<div class=alap><b>Dátum:</b> <input type=text name=datum value='$datum' size=10 class=urlap> <small><font color=red>(Formátum fontos!)</font></small>";
    $kod.= '<br><br><b>Igenapot mindig kötelező választani</b>, <small>ha szent ünnepe van, akkor válassz szentet is
   - ekkor a nap ünnepét ("főcím") és a gondolatot a szent leírásából vesszük. Ha nem választasz szentet, az igenap megnevezése lesz a "főcím",
    az aznapi szent ilyenkor - ha van, de nem ünnep, csak emléknap - ez alatt jelenik meg zárójelben. Szent ünnepén az igenapot aszerint kell
    kiválasztani, hogy van-e saját olvasmánya vagy a liturgikus év időpontjához tartozó olvasmányokat olvassák a misén (ld. direktórium ! )</small><br> </div>';

//Gondolat kiválasztása
	$kod.="\n<br><span class=alap>igenapok:</span><br><select name=ige class=urlap><option value=0>Még nincs</option>";
	$query="select id,idoszak,ev,nap from igenaptar order by idoszak asc, ev asc, nap asc";
    if(!$lekerdez=mysql_query($query)) $kod.= '<p class=hiba>HIBA a lekérdezésnél!<br>'.mysql_error();
    while(list($gid,$gidoszak,$gev,$gnap)=mysql_fetch_row($lekerdez)) {
        $kiiras=idoszak($gidoszak);
        $kiiras.=',';
        if($gev!='' and $gev!='0') $kiiras.=" $gev év,";
        $kiiras.=" $gnap";
        $kod.= "<option value=$gid";
        if($ige==$gid) $kod.= ' selected';
        $kod.= ">$kiiras</option>";
    }
    $kod.= '</select>';

//Szentek kiválasztása
	$ev=date('Y');
	$kod.= "\n<br><br><span class=alap>szentek: </span><br><select name=szent class=urlap><option value=0>Nincs</option>";
    $query="select id,nev,ho,nap from szentek order by ho asc, nap asc";
    if(!$lekerdez=mysql_query($query)) $kod.= '<p class=hiba>HIBA a lekérdezésnél!<br>'.mysql_error();
    while(list($szid,$sznev,$szho,$sznap)=mysql_fetch_row($lekerdez)) {
        $kod.= "<option value=$szid";
	    if($szent==$szid) $kod.= ' selected';
        $kod.= ">($szho-$sznap) $sznev</option>";
    }
    $kod.= '</select>';
	
	$kod.= '<br><br><input type=submit value=Mehet class=urlap></form>';

	return $kod;
}

function modingnaptar() {
    global $_POST,$linkveg,$m_id;

	$datum=$_POST['datum'];
	$ige=$_POST['ige'];
	$szent=$_POST['szent'];


//Dátum ellenőrzés
    $ev = substr ("$datum", 0, 4);
    $honap = substr ("$datum", 5, 2);
    $nap = substr("$datum", 8, 2);
    if(!checkdate($honap,$nap,$ev)) {
        echo '<p class=hiba>HIBA! Nem létező dátum.</p>';
        echo '<a href=javascript:history.go(-1); class=link>Vissza</a>';
        exit;
    }

	//Ha van szent, akkor a szent színe a mérvadó, egyébként pedig a gondolat színe
	if($szent>0) {
		$query="select szin from szentek where id='$szent'";
		list($szin)=mysql_fetch_row(mysql_query($query));
	}
	else {
		$query="select szin from igenaptar where id='$ige'";
		list($szin)=mysql_fetch_row(mysql_query($query));
	}

	//Van-e már ilyen dátum:
	$lekerdez=mysql_query("select datum from lnaptar where datum='$datum'");
	if(mysql_num_rows($lekerdez)>0) $uj=false;
	else $uj=true;

    //Ha módosításról van szó
	if(!$uj) {
		if($szent==0 and $ige==0) {
			//Töröljük
			if(!mysql_query("delete from lnaptar where datum='$datum'"))
				$kod.= '<p class=hiba>HIBA a törlésnél!<br>'.mysql_error();
		}
		else {
			if(!mysql_query("update lnaptar set ige='$ige', szent='$szent', szin='$szin' where datum='$datum'"))
				$kod.= '<p class=hiba>HIBA a módosításnál!<br>'.mysql_error();
		}
    }	
    else {
        if(!mysql_query("insert lnaptar set ige='$ige', szent='$szent', szin='$szin', datum='$datum'"))
          $kod.= '<p class=hiba>HIBA a rögzítésnél!<br>'.mysql_error();

    }
    $kod.=naptar($honap,$ev);

	return $kod;
}

if($user->checkRole('igenaptar')) {

	switch($m_op) {
    
	case 'index':
        //$tartalom=igenaptar_index();
        $tartalom=naptar(date('m'),date('Y'));
        break;

    case "naptar":
		$honap=$_POST['honap'];
		$ev=$_POST['ev'];
        $tartalom=naptar($honap,$ev);
        break;
        
    case "modnaptar":
        $tartalom=modnaptar();
        break;
        
    case "modingnaptar":
        $tartalom=modingnaptar();
        break;
       
    case "szentek":
        $tartalom=szentek();
        break;

    case "szentekmod":
        $tartalom=szentekmod();
        break;
        
    case "delszent":
        $tartalom=delszent();
        break;
        
    case "deleteszent":
        $tartalom=deleteszent();
        break;
        
    case "gondolatok":
        $tartalom=gondolatok();
        break;
        
    case "gondolatokmod":
        $tartalom=gondolatokmod();
        break;
        
    case "delgondolat":
        $tartalom=delgondolat();
        break;
        
    case "deletegondolat":
        $tartalom=deletegondolat();
        break;
	
	case 'addszent':
		$id=$_GET['id'];
		$tartalom=addszent($id);
		break;

	case 'addingszent':
		$tartalom=addingszent();
		break;

	case 'addige':
		$id=$_GET['id'];
		$tartalom=addige($id);
		break;

	case 'addingige':
		$tartalom=addingige();
		break;

	}
}
else {
	$tartalom.="<p class=hiba>HIBA! A választott modul nem érhető el!</p>";
}

?>
