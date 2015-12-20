<?php

print'<?xml version="1.0" encoding="iso-8859-2"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="hu"><head><title>Linkteszt</title></head><body><p>';

$hiba=false;

//Adatbázis csatlakozás előkészítése, elindítása
if(!@include_once('config.inc')) {
$hiba=true;
$hibauzenet_prog.='<br>HIBA! A konfigurációs fájl behívásakor!';
echo 'hiba';
}
dbconnect();


//Van-e kiválasztott templom
if($_GET['templom']>0) {
$M_ID=26;
$M_OP='view';
$TID=$_GET['templom'];





################################INNEN################################

$query="select varos,orszag from templomok where id=$TID";
$lekerdez=mysql_db_query($db_name,$query);
if(list($varos,$orszag)=mysql_fetch_row($lekerdez)) {

if($orszag==12){

//Az ékezetes betűk és felesleges szóközök kezelése
$nagyvofely=eregi_replace(" {2,}"," ",$varos);
$nagyvofely=strtr($nagyvofely,array("Ă´"=>"ő","Ăµ"=>"ő","ô"=>"ő","Ĺ‘"=>"ő","Ç’"=>"ő","Ĺ"=>"Ő","Ĺ°"=>"Ű","Ĺ±"=>"ű",));
$nagyvofelylink=trim(strtolower($nagyvofely));
$nagyvofelylink=eregi_replace("Budapest ","budapest, ",$nagyvofelylink);
$nagyvofelylink=strtr($nagyvofelylink,array(" "=>"+","á"=>"a","ŕ"=>"a","ä"=>"a","é"=>"e","ě"=>"i","í"=>"i","ó"=>"o","ň"=>"o","ö"=>"o","ő"=>"o","ú"=>"u","ů"=>"u","ü"=>"u","ű"=>"u","Á"=>"a","Ŕ"=>"a","Ä"=>"a","É"=>"e","Ě"=>"i","Í"=>"i","Ó"=>"o","Ň"=>"o","Ö"=>"o","Ő"=>"o","Ú"=>"u","Ů"=>"u","Ü"=>"u","Ű"=>"u"));


//Kivételek: kétbetűs településnév, Komló/Kömlő-Komoró/Kömörő
if($nagyvofelylink=="ag"){$nagyvofelylink="7381";}
elseif($nagyvofelylink=="bo"){$nagyvofelylink="9625";}
elseif($nagyvofelylink=="or"){$nagyvofelylink="4336";}
elseif($nagyvofelylink=="se"){$nagyvofelylink="9789";}
elseif($varos=="Komló"){$nagyvofelylink="7300";}
elseif($varos=="Kömlő"){$nagyvofelylink="3372";}
elseif($varos=="Komoró"){$nagyvofelylink="4622";}
elseif($varos=="Kömörő"){$nagyvofelylink="4943";}

if(strstr($nagyvofelylink,"?")){/*Gond van a hosszú ékezetes betűkkel, ezért nincs link!*/}else{

$html_kod="<a href=\"http://nagyvofely.hu/$nagyvofelylink/templomok\" target=\"nagyvofely\">$nagyvofely összes temploma</a>";
}

}else{/*Nem Magyarországon található ez a templom, ezért nincs link!*/}

}else{/*Nincs ilyen azonosítószámmal templom, ezért nincs link!*/}

}else{/*Nincs templom azonosítószám megadva, ezért nincs link!*/}

################################IDÁIG################################







/////////////////////////////////
//html kód kiküldése a böngészőnek
/////////////////////////////////
print $html_kod;

?></p></body></html>
