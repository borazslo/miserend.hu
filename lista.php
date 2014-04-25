<?

/////////////////////////////////
// Fõ változók:
// $hiba (true,false)
// $hibauzenet (hibaüzenet a látogatónak)
// $hibauzenet_prog (hibaüzenet a programozónak)
// $html_kod (html kód, amit a végén kiírunk)
/////////////////////////////////

/////////////////////////////////
//Alapadatok beállítása (config)
/////////////////////////////////
    $hiba=false;

//Adatbázis csatlakozás elõkészítése, elindítása
    if(!@include_once('config.inc')) {
        $hiba=true;
        $hibauzenet_prog.='<br>HIBA! A konfigurációs fájl behívásakor!';
    }
    dbconnect();
        
echo "\n<h2>Kép nélküli templomok listája (2006.06.18.)</h2>";
$query="select id,nev,varos,orszag,cim from templomok where ok='i' order by orszag,varos";
$lekerdez=mysql_db_query($db_name,$query);
while(list($id,$nev,$varos,$orszag,$cim)=mysql_fetch_row($lekerdez)) {
	if($orszag!=$orszagell) {
		list($orszagnev)=mysql_fetch_row(mysql_db_query($db_name,"select nev from orszagok where id='$orszag'"));
		echo "\n<br><hr>$orszagnev<hr>";
		$orszagell=$orszag;
	}
	$kepek=mysql_db_query($db_name,"select id from kepek where kat='templomok' and kid='$id'");
	if(mysql_num_rows($kepek)==0) {
		$a++;
		echo "\n$a. <a href=http://www.miserend.hu/?templom=$id>$nev ($varos)</a><small> [$cim]</small><br>";
	}
}

?>
