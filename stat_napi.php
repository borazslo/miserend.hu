<?

include_once('config.inc');
dbconnect();

$ma=date('Y-m-d');

//modulok
$query="select id,nev,szamlalo from modulok where szamlalo>0 and zart='0'";
if(!$lekerdez=mysql_db_query($db_name,$query)) echo "HIBA!<br>$query<br>".mysql_error();
while(list($id,$nev,$szamlalo)=mysql_fetch_row($lekerdez)) {
	list($tegapi)=mysql_fetch_row(mysql_db_query($db_name,"select mennyi from statisztika where melyik='$id' and tipus='modul' order by mennyi desc"));
	$mennyi=$szamlalo-$tegnapi;
	mysql_db_query($db_name,"insert statisztika set datum='$ma', melyik='$id', tipus='modul', nev='$nev', mennyi='$mennyi'");
}

//Rovatok
$query="select id,nev,szamlalo from rovatok where szamlalo>0 and ok='i'";
$lekerdez=mysql_db_query($db_name,$query);
while(list($id,$nev,$szamlalo)=mysql_fetch_row($lekerdez)) {
	list($tegapi)=mysql_fetch_row(mysql_db_query($db_name,"select mennyi from statisztika where melyik='$id' and tipus='rovat' order by mennyi desc"));
	$mennyi=$szamlalo-$tegnapi;
	mysql_db_query($db_name,"insert statisztika set datum='$ma', melyik='$id', tipus='rovat', nev='$nev', mennyi='$mennyi'");
}

//Fõkategóriák
$query="select id,nev,szamlalo from fokat where szamlalo>0 and ok='i'";
$lekerdez=mysql_db_query($db_name,$query);
while(list($id,$nev,$szamlalo)=mysql_fetch_row($lekerdez)) {
	list($tegapi)=mysql_fetch_row(mysql_db_query($db_name,"select mennyi from statisztika where melyik='$id' and tipus='fokat' order by mennyi desc"));
	$mennyi=$szamlalo-$tegnapi;
	mysql_db_query($db_name,"insert statisztika set datum='$ma', melyik='$id', tipus='fokat', nev='$nev', mennyi='$mennyi'");
}

//Kategóriák
$query="select id,nev,szamlalo from kat where szamlalo>0 and ok='i'";
$lekerdez=mysql_db_query($db_name,$query);
while(list($id,$nev,$szamlalo)=mysql_fetch_row($lekerdez)) {
	list($tegapi)=mysql_fetch_row(mysql_db_query($db_name,"select mennyi from statisztika where melyik='$id' and tipus='kat' order by mennyi desc"));
	$mennyi=$szamlalo-$tegnapi;
	mysql_db_query($db_name,"insert statisztika set datum='$ma', melyik='$id', tipus='kat', nev='$nev', mennyi='$mennyi'");
}

//Alkategóriák
$query="select id,nev,szamlalo from alkat where szamlalo>0 and ok='i'";
$lekerdez=mysql_db_query($db_name,$query);
while(list($id,$nev,$szamlalo)=mysql_fetch_row($lekerdez)) {
	list($tegapi)=mysql_fetch_row(mysql_db_query($db_name,"select mennyi from statisztika where melyik='$id' and tipus='alkat' order by mennyi desc"));
	$mennyi=$szamlalo-$tegnapi;
	mysql_db_query($db_name,"insert statisztika set datum='$ma', melyik='$id', tipus='alkat', nev='$nev', mennyi='$mennyi'");
}

//Hírek
$query="select id,cim,napiszamlalo from hirek where napiszamlalo>0 and nyelv='hu' order by napiszamlalo desc limit 0,10";
$lekerdez=mysql_db_query($db_name,$query);
while(list($id,$cim,$szamlalo)=mysql_fetch_row($lekerdez)) {
	mysql_db_query($db_name,"insert statisztika set datum='$ma', melyik='$id', tipus='hir', nev='$cim', mennyi='$szamlalo'");
}

mysql_db_query($db_name,"update hirek set napiszamlalo=0");




?>
