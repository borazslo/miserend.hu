<?php

$kod='<html><head><title>Magyar Kurír</title><meta http-equiv="Content-Type" content="text/html; charset=windows-1250"><style type="text/css"><!--';
$kod.="\n .cim {  font-family: Verdana; font-size: 11pt; font-weight: bold; color: #394873}";
$kod.="\n .alap {  font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #000000}";
$kod.="\n .kiscim {  font-family: Verdana; font-size: 11pt; font-weight: bold; color: #C26238}";
$kod.="\n .kicsi {  font-family: Arial, Helvetica, sans-serif; font-size: 8pt; color: #000000}";
$kod.="\n.link {  font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #000000; text-decoration: none}";
$kod.="\n.link:visited { color: #000000}";
$kod.="\n.link:hover { color: #394873}";
$kod.="\n.linkb {  font-family: Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: bold; color: #000000; text-decoration: none}";
$kod.="\n.linkb:visited { color: #000000}";
$kod.="\n.linkb:hover { color: #394873}";
$kod.="\n.kislinkb {  font-family: Arial, Helvetica, sans-serif; font-size: 8pt; font-weight: bold; color: #C26238; text-decoration: none}";
$kod.="\n.kislinkb:visited { color: #394873}";
$kod.="\n.kislinkb:hover { color: #000000}";
$kod.='--></style></head><body bgcolor="#FFFFFF" text="#000000">';

include("config.inc");
dbconnect();

include_once('moduls/szotar/alapszotar.inc');

$most=date('Y-m-d H:i:s');
$now=time();
$tegnap=date('Y-m-d H:i:s',$now-86400);

$query="select id,cim,intro,szoveg,datum,hirlevel from hirek where ok='i' and megjelenhet like '%kurir%' and datum<='$most' and datum>='$tegnap' order by datum desc";
if(!$lekerdez=mysql_db_query($db_name,$query)) echo "HIBA!<br>$query<br>".mysql_error();
while(list($id,$cim,$intro,$szoveg,$datum,$hirlevel)=mysql_fetch_row($lekerdez)) {
	$datido=mktime(substr($datum,11,2),substr($datum,14,2),0,substr($datum,5,2),substr($datum,8,2),substr($datum,0,4));
	$ev=substr($datum,0,4);
	$ora=date('G',$datido);
	$perc=date('i',$datido);
	if($ev==date('Y')) $ev='';
	else $ev.='. ';
	$ho=date('n',$datido);
	$honap=alapnyelv("ho$ho");
	$nap=date('j',$datido);
	$honap=alapnyelv("ho$ho");
	$ho_nap="$honap $nap. ";
	$datumkiir="$ev$ho_nap$ora:$perc";
	$kepkiir='';

	$szoveg=str_replace('href="?','href="http://www.magyarkurir.hu/?',$szoveg);
	$szoveg=str_replace('href="www','href="http://www',$szoveg);
	$szoveg=str_replace('src="kepek','src="http://www.magyarkurir.hu/kepek',$szoveg);

	if($hirlevel=='c') {
		$hcT[]="\n<tr><td valign=top>• </td><td valign=top><a href=http://www.magyarkurir.hu/?m_id=1&m_op=view&id=$id class=linkb>$cim</a> <span class=kicsi>($datumkiir)</span><a href=http://www.magyarkurir.hu/?m_id=1&m_op=view&id=$id class=kislinkb> >>> tovább</a></td></tr>";
	}
	elseif($hirlevel=='i') {
		$img="kepek/hirek/$id/fokep/n.jpg";
		if(is_file($img)) {
			$wh=getimagesize($img);
			$kepkiir="<img src=http://www.magyarkurir.hu/$img $wh[3] align=left vspace=3 hspace=10>";
		}
		$hiT[]="\n<tr><td colspan=2><a href=http://www.magyarkurir.hu/?m_id=1&m_op=view&id=$id class=linkb>$cim </a><span class=kicsi> ($datumkiir)</span><br>$kepkiir<span class=alap>$intro</a><br><div align=right><a href=http://www.magyarkurir.hu/?m_id=1&m_op=view&id=$id class=kislinkb>>>> tovább</a></div></td></tr><tr><td colspan=2><hr></td></tr>";
	}
	elseif($hirlevel=='t') {
		if(is_file("kepek/hirek/$id/fokep/n.jpg")) {
			$wh=getimagesize("kepek/hirek/$id/fokep/n.jpg");
			$kepkiir="<img src=http://www.magyarkurir.hu/kepek/hirek/$id/fokep/n.jpg $wh[3] align=left>";
		}
		if(!empty($intro)) $intro.='<br><br>';
		$htT[]="\n<tr><td colspan=2><hr><a href=http://www.magyarkurir.hu/?m_id=1&m_op=view&id=$id class=linkb>- $cim</a> <span class=kicsi>($datumkiir)</span><br>$kepkiir<span class=alap><br><b>$intro</b>$szoveg</span></td></tr>";
	}
}

$maidatum=date('Y. m-d');
$ev=date('Y');
$ho=date('n');
$honap=alapnyelv("ho$ho");
$nap=date('j');
$maidatum="$ev. $honap $nap.";

$kod.="\n<table width=650 cellspacing=0><tr><td colspan=2><span class=cim>Magyar Kurír - Hírlevél, $maidatum - </span><a href=http://www.magyarkurir.hu class=linkb>www.magyarkurir.hu</a><hr>";

if(is_array($hiT)) {
	$kod.="\n<br><span class=kiscim>Kiemelt híreink:</span><br><br></td></tr>";
	foreach($hiT as $ertek) {
		$kod.=$ertek;
	}
}

if(is_array($hcT)) {
	$kod.="\n<tr><td colspan=2><br><span class=kiscim>Magyar Kurír percről percre:</span><br><br></td></tr>";
	foreach($hcT as $ertek) {
		$kod.=$ertek;
	}
}

if(is_array($htT)) {
	foreach($htT as $ertek) {
		$kod.=$ertek;
	}
}

//lábléc
$kod.="\n<tr><td colspan=2><br><hr><span class=alap><b>Magyar Kurír Szerkesztőség</b>";
$kod.="\n<br>Levélcím: 1364 Budapest, Pf. 41., <br>Telefon: 479-20-20 Fax: 479-20-21";
$kod.="\n<br><br><b>Amennyiben le kívánja mondani napi hírlevelünket</b>, ";
$kod.="\n<br>küldjön egy levelet a következő e-mail címre: <a href='mailto:kurir-lemondas@hcbc.hu?subject=Hírlevél lemondás' class=link>kurir-lemondas@hcbc.hu</a>";
$kod.="\n<br>A levél tárgyához csak ennyit írjon: Hírlevél lemondás</span>";

$kod.='</td></tr></table></body></html>';

////////////Emailküldés
$From='web@magyarkurir.hu';
$Subject="Hírlevél $maidatum";
$To='kurir@katolikus.hu';
$To1='fjk@vipmail.hu';
$To2='feher@hcbc.hu';
$To3='tajti.robert@katradio.hu';
$To4='g@florka.hu';
$To5='hirszerk@katradio.hu';

		$OB="----=_OuterBoundary";

		$headers ="MIME-Version: 1.0\n";
		$headers.="From: ".$From."\n";
		$headers.="Reply-To: ".$From."\n";
		$headers.="Content-Type: multipart/related; boundary=\"".$OB."\"";

		$Msg ="  This message is in MIME format.  The first part should be readable text,\n  while the remaining parts are likely unreadable without MIME-aware tools.\n  Send mail to mime@docserver.cac.washington.edu for more info.\n";

	    $Msg.="\n--".$OB."\n";
		$Msg.="Content-Type: text/html; charset=\"windows-1250\"\n";
		$Msg.=$kod."\n";

		$Msg.= "\n--".$OB."\n";

	    $Msg.="--".$OB."--\n";

		mail ($To, $Subject, $Msg, $headers);
		mail ($To1, $Subject, $Msg, $headers);
		mail ($To2, $Subject, $Msg, $headers);
		mail ($To3, $Subject, $Msg, $headers);
		mail ($To4, $Subject, $Msg, $headers);
		mail ($To5, $Subject, $Msg, $headers);

?>
