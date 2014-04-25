<?php

function stat_index() {
	global $linkveg,$m_id,$db_name,$_GET,$_POST,$sessid;

	$datum=$_POST['datum'];
	if(empty($datum)) $datum=$_GET['datum'];
	if(empty($datum)) $datum=date('Y-m-d',time()-86400);

//Napi statisztikák
	$query="select tipus,nev,mennyi from statisztika where datum='$datum' order by tipus,mennyi desc";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($tipus,$nev,$mennyi)=mysql_fetch_row($lekerdez)) {
		$statT[$tipus][]="<span class=alap>$nev: $mennyi</span>";
	}

//Top 10 magyarhír
	$query="select id,cim,szamlalo from hirek where szamlalo>0 and nyelv='hu' order by szamlalo desc limit 0,10";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$cim,$szamlalo)=mysql_fetch_row($lekerdez)) {
		$hirT[]="<li><a href=?m_id=1&m_op=view&id=$id&sessid=$sessid class=link>$cim</a><span class=kiscim>: $szamlalo</span></li>";
	}

//Top 10 NEMmagyarhír
	$hirT[]="-------------------";
	$query="select id,cim,szamlalo,nyelv from hirek where szamlalo>0 and nyelv!='hu' order by szamlalo desc limit 0,10";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$cim,$szamlalo,$nyelv)=mysql_fetch_row($lekerdez)) {
		$zaszlo="<img src=img/zaszlok/m_$nyelv.jpg align='absmiddle'>";		
		$hirT[]="<li>$zaszlo <a href=?m_id=1&m_op=view&id=$id&sessid=$sessid class=link>$cim</a><span class=kiscim>: $szamlalo</span></li>";
	}

//Top10 továbbküldött hír
	$query="select id,cim,send from hirek where send>0 order by send desc limit 0,10";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$cim,$szamlalo)=mysql_fetch_row($lekerdez)) {
		$hirsT[]="<li><a href=?m_id=1&m_op=view&id=$id&sessid=$sessid class=link>$cim</a><span class=kiscim>: $szamlalo</span></li>";
	}

//Top10 galéria
	$query="select id,cim,szamlalo from galeria order by szamlalo desc limit 0,10";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$cim,$szamlalo)=mysql_fetch_row($lekerdez)) {
		$galeriaT[]="<li><a href=?m_id=11&m_op=view&gid=$id&sessid=$sessid class=link>$cim</a><span class=kiscim>: $szamlalo</span></li>";
	}

//ûrlap
	$stat.="\n<form method=post><input type=hidden name=m_id value=$m_id>";
	$stat.="\n<input type=text name=datum value='$datum' size=10 maxlength=10 class=urlap><input type=submit value=Mutat class=urlap></form>";

//Kiírások
	$stat.="\n<table border=1 cellspacing=0 width=100%><tr><td valign=top width=34%>";
	$stat.="\n<span class=kiscim>Modul statisztika:</span>";
	foreach($statT['modul'] as $ertek) {
		$stat.="<br>$ertek";
	}
	$stat.="\n</td><td valign=top width=33%>";
	$stat.="\n<span class=kiscim>Rovat statisztika:</span>";
	foreach($statT['rovat'] as $ertek) {
		$stat.="<br>$ertek";
	}
	$stat.="\n</td><td width=33% valign=top>";
	$stat.="\n<span class=kiscim>Napi hírstatisztika:</span>";
	foreach($statT['hir'] as $ertek) {
		$stat.="<br>$ertek";
	}
	$stat.="</td></tr><tr><td valign=top>";
	$stat.="\n<span class=kiscim>Fõkategória statisztika:</span>";
	foreach($statT['fokat'] as $ertek) {
		$stat.="<br>$ertek";
	}
	$stat.="\n</td><td valign=top>";
	$stat.="\n<span class=kiscim>Kategória statisztika:</span>";
	foreach($statT['kat'] as $ertek) {
		$stat.="<br>$ertek";
	}
	$stat.="\n</td><td valign=top>";
	$stat.="\n<span class=kiscim>Alkategória statisztika:</span>";
	foreach($statT['alkat'] as $ertek) {
		$stat.="<br>$ertek";
	}
	$stat.="\n</td></tr></table><hr>";
	
	$stat.="\n<table border=1 cellspacing=0 width=100%><tr><td valign=top width=34%>";
	$stat.="\n<span class=kiscim>Top 10 hír:</span>";
	foreach($hirT as $ertek) {
		$stat.="$ertek";
	}
	$stat.="\n</td><td valign=top width=33%>";
	$stat.="\n<span class=kiscim>Top10 továbbküldött hír:</span>";
	foreach($hirsT as $ertek) {
		$stat.="$ertek";
	}
	$stat.="\n</td><td width=33% valign=top>";
	$stat.="\n<span class=kiscim>Top10 galéria:</span>";
	foreach($galeriaT as $ertek) {
		$stat.="$ertek";
	}
	$stat.="</td></tr></table>";

	$adatT[2]="<span class=alcim>Statisztika</span><br><br>".$stat;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}


/*
//Jogosultság ellenõrzése
if(strstr($u_jogok,'reklam')) {
*/
switch($m_op) {
    case 'index':
        $tartalom=stat_index();
        break;
}
/*
}
else {
	$tartalom="\n<span class=hiba>HIBA! Nincs hozzá jogosultságod!</span>";
}
*/
?>
