<?php

include("config.inc");
dbconnect();

$query="select nev,ismertnev,varos,cim,plebania from templomok where orszag=12 and plebania!=''";
if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();

while(list($nev,$ismertnev,$varos,$cim,$plebania)=mysql_fetch_row($lekerdez)) {
	$plebania=str_replace("\n","",$plebania);
	$plebania=str_replace("<br>","",$plebania);
	$adatokT[]="$nev;$ismertnev;$varos;$cim;$plebania";
}
$adatok=implode("\n",$adatokT);
$adatok1=implode("<br>",$adatokT);
$adatok1=str_replace("\n","",$adatok1);
$adatok1=str_replace("<b>Plébánia:</b>","",$adatok1);
$adatok=str_replace('<br>',"\n\n",$adatok1);

echo "<textarea cols=80 rows=50>$adatok</textarea>";


?>
