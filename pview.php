<html>
<head>
<title>VPP - Hírporta</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2">
<link rel="stylesheet" href="design/alap/img/pstyle.css" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#000000">

<?php

include("config.inc");
dbconnect();

include_once('moduls/szotar/alapszotar.inc');

$id=$_GET['id'];
$kep=$_GET['kep'];

$most=date('Y-m-d H:i:s');
$query="select id,cim,intro,szoveg,datum,kulcsszo,galeria from hirek where id='$id' and ok='i' and datum<='$most'";
$lekerdez=mysql_db_query($db_name,$query);
if(mysql_num_rows($lekerdez)>0) {
	if(!mysql_db_query($db_name,"update hirek set szamlalop=szamlalop+1 where id='$id'")) echo 'HIBA!<br>'.mysql_error();
	list($hid,$hcim,$hintro,$hszoveg,$datum,$kulcsszavak,$galeria)=mysql_fetch_row($lekerdez);
	$hszoveg=str_replace('href="?',"href=\"?$linkveg&",$hszoveg); //link helyesbítése
	$hszoveg=str_replace('href="http://www.hirporta.hu/?',"href=\"http://www.hirporta.hu/?$linkveg&",$hszoveg); //link helyesbítése

	$datido=mktime(substr($datum,11,2),substr($datum,14,2),0,substr($datum,5,2),substr($datum,8,2),substr($datum,0,4));
	$ev=substr($datum,0,4);
	$ora=date('G',$datido);
	$perc=date('i',$datido);
	if($ev==date('Y')) $ev='';
	else $ev.='.';
	$ho=date('n',$datido);
	$honap=alapnyelv("ho$ho");
	$nap=date('j',$datido);
	$datumkiir="<span class=alap><i> $ev $honap $nap. $ora:$perc</i></span>";

	if(!empty($hintro)) $hintro=nl2br($hintro);
	if(is_file("kepek/hirek/$id/fokep/k1.jpg")) {
		$info=getimagesize("kepek/hirek/$id/fokep/kep.jpg");
		$w=$info[0];
		if($w>800) $w=800;
		$h=$info[1];
		if($h>600) $h=600;
		$fokep="<a href='javascript:OpenNewWindow(\"view.php?kep=kepek/hirek/$id/fokep/kep.jpg\",$w,$h)';><img src=kepek/hirek/$id/fokep/k1.jpg align=left vspace=10 hspace=10 border=0></a>";
	}

	$adatT[2]="\n<span class='cikkalcim'>$hcim</span><br>$datumkiir<br><br>$fokep";
	//if(!empty($hintro)) $adatT[2].="\n<div class=kiscimkizart>$hintro</div>";
	$adatT[2].="\n<div class=alapkizart>$hszoveg</div>";
}
else {
	$adatT[2]="\n<span class='hiba'>HIBA! A keresett hír nem található!</span>";
}

$kod='<div align=center><table width=640><tr><td>';
$kod.='<div align=center><img src=img/logoFF.gif><br><span class=kiscim>http://www.hirporta.hu</span><hr></div>';
$kod.='</td></tr><tr><td>';
if($kep=='n') {
	$kod.=strip_tags($adatT[2],'<p><br><b><i><u><strong><em><font><span><div><li><ul><table>');
}
else $kod.=$adatT[2];
$kod.='</td></tr><tr><td>';
$kod.="<hr><span class=alap>Interneten: http://www.hirporta.hu/?hir=$id</span><hr>";
$kod.='</td></tr></table></div><div align=center>';
if(strstr($adatT[2],'img src')) {
    if($kep=='n') $kod.="<a href='?kep=i&id=$id' class=link><img src=img/keppel.gif border=0 aling=absmiddle> Képekkel együtt</a> &nbsp; ";
    else $kod.="<a href='?kep=n&id=$id' class=link><img src=img/kepnelkul.gif border=0 aling=absmiddle> Kép nélküli nézet</a> &nbsp; ";
}
$kod.="<a href='javascript:print();' class=link><img src=img/print.gif border=0 aling=absmiddle> Nyomtat</a> &nbsp; <a href=javascript:close(); class=link><img src=img/bezar.gif border=0 aling=absmiddle> Bezár</a></div>";

echo $kod;


?>
</body></html>