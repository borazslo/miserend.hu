<?

include("config.inc");
dbconnect();

$gid=$_GET['gid'];
$kep=$_GET['kep'];
$next=$_GET['next'];
$prev=$_GET['prev'];

$konyvtar="kepek/galeria/$gid";

$query="select cim,fajlnev from g_kepcimek where gid='$gid' order by sorszam";
$lekerdez=mysql_db_query($db_name,$query);
while(list($cim,$fajlnev)=mysql_fetch_row($lekerdez)) {
	$cimT[]=$cim;
	$fajlT[]=$fajlnev;
}

$info=getimagesize("$konyvtar/$kep");
$maxw=600;
$maxh=500;
$maxh2=$maxh+10;
$w=$info[0];
$h=$info[1];
$ujw=$w;
$ujh=$h;

if($w>$h) $fekvo=true;
else $fekvo=false;

if($fekvo) {
	if($w>$maxw) {
		$ujw=$maxw;
		$ujh=($maxw/$w)*$h;		
	}
	if($ujh>$maxh or $h>$maxh) {
		$ujw=($maxh/$h)*$w;
		$ujh=$maxh;
		if($ujw>$maxw) {
			$ujh=($maxw/$w)*$h;
			$ujw=$maxw;
		}
	}
}
elseif(!$fekvo) {
	if($h>$maxh) {
		$ujh=$maxh;
		$ujw=($maxh/$h)*$w;
	}
	if($ujw>$maxw or $w>$maxw) {
		$ujh=($maxw/$w)*$h;
		$ujw=$maxw;
		if($ujh>$maxh) {
			$ujw=($maxh/$h)*$w;
			$ujh=$maxh;
		}
	}
}

$adatok="width=$ujw height=$ujh";

$mennyi=count($fajlT);
$mennyimax=$mennyi-1;
foreach($fajlT as $id=>$fajlnev) {
	if($fajlnev==$kep) {
		//Ezt a képet kell kitenni
		$next=$id+1;
		$prev=$id-1;
		$kepkiir="<table cellpadding=0 cellspacing=0><tr><td colspan=2 bgcolor=#F5CC4C><img src=img/space.gif width=2 height=2></td><td width=2 bgcolor=#7A3B43><img src=img/space.gif width=2 height=2></td></tr><tr><td width=2 bgcolor=#F5CC4C><img src=img/space.gif width=2 height=5></td><td><img src='$konyvtar/$fajlnev' title='$cimT[$id]' border=0 $adatok></td><td bgcolor=#7A3B43><img src=img/space.gif width=2 height=2></td></tr><tr><td bgcolor=#F5CC4C><img src=img/space.gif width=2 height=2></td><td colspan=2 bgcolor=#7A3B43><img src=img/space.gif width=2 height=2></td></tr></table>";
		if($prev>=0) $prevkep="<a href=?gid=$gid&kep=$fajlT[$prev] class=link><img src='$konyvtar/kicsi/$fajlT[$prev]' border=0 title='Elõzõ'><br>elõzõ</a> ";
		if($next<$mennyi) $nextkep=" <a href=?gid=$gid&kep=$fajlT[$next] class=link>következõ<br><img src='$konyvtar/kicsi/$fajlT[$next]' border=0 title='Következõ'></a>";
		$szoveg=$cimT[$id];
	}
}

list($gcim)=mysql_fetch_row(mysql_db_query($db_name,"select cim from galeria where id='$gid'"));

echo '<html><head><title>Magyar Kurír - Galéria</title><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2">
<link rel="stylesheet" href="design/alap/img/style.css" type="text/css"></head>
<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">';


echo "\n<table width=100% cellpadding=0 cellspacing=0>";
echo "\n<tr><td colspan=2><img src=img/space.gif width=5 height=10></td></tr>";
echo "\n<tr><td colspan=2><div align=center class=alcim>$gcim</div></td></tr>";
echo "\n<tr><td colspan=2><img src=img/space.gif width=5 height=10></td></tr>";
echo "\n<tr><td align=center bgcolor=#ECE5C8 height=$maxh2>$kepkiir</td><td width=130 align=center valign=top bgcolor=#ECE5C8>";

	echo "\n<table border=0 cellpadding=0 cellspacing=0 width=100% height=$maxh2>";
	echo "\n<tr><td height=10><img src=img/space.gif width=5 height=10></td></tr>";
	echo "\n<tr><td height=180 valign=top align=center><a href=?gid=$gid&kep=$fajlT[0] class=link><< elsõ kép</a><br><br>$prevkep</td></tr>";
	echo "\n<tr><td height=80 align=center><span class=alap>További képek</span></td></tr>";
	echo "\n<tr><td height=180 valign=bottom align=center>$nextkep<br><br><a href=?gid=$gid&kep=$fajlT[$mennyimax] class=link>utolsó kép >></a></td></tr>";
	echo "\n<tr><td height=10><img src=img/space.gif width=5 height=10></td></tr></table>";

echo "</td></tr>";
echo "\n<tr><td height=50 align=center bgcolor=#E8ECEF><table><tr><td><span class=alap>$szoveg</span></td></tr></table></td><td align=center bgcolor=#E8ECEF><a href=javascript:close(); class=link><img src=img/bezar.gif border=0 align=absmiddle> ablak bezárása</a></td></tr></table>";

?>
</body></html>