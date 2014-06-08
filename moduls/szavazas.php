<?

function szavazas_index() {
	global $linkveg,$db_name,$elso,$m_id,$m_op,$_GET,$fooldal_id;

	$min=$_GET['min'];
	if(!isset($min) or $min<1) $min=1;

	$leptet=$_GET['leptet'];
	if(!isset($leptet)) $leptet=6;

	$prev=$min-$leptet;
	$next=$min+$leptet;


	$datum=date('Y-m-d');
	$query="select id,kerdes,tipus from szavazask where datum<='$datum' and fooldal like '%-$fooldal_id-%' order by datum desc ";
	$lekerdez=mysql_query($query);
	$mennyi=mysql_num_rows($lekerdez);
	$query.="limit $min,$leptet";
	$lekerdez=mysql_query($query);
	while(list($kid,$kerdes,$ktipus)=mysql_fetch_row($lekerdez)) {
		$query1="select valasz,szamlalo from szavazasv where kid='$kid' order by szamlalo desc";
		$lekerdez1=mysql_query($query1);
		while(list($valasz,$szamlalo)=mysql_fetch_row($lekerdez1)) {
			$valaszokT[]=$valasz;
			$szamlaloT[]=$szamlalo;
			$ossz=$ossz+$szamlalo;
		}
		$tartalomT[$kid]="\n<span class=kiscim>$kerdes</span><span class=kicsi> ($ossz ".alapnyelv('szavazat').")</span><br><br>";

		foreach($valaszokT as $id=>$valasz) {
			$tartalomT[$kid].="\n<span class=alap>- $valasz</span> ";		
			$szam++;
			if($szam>7) $szam=1;
			$stat='stat'.$szam;		
			$arany=$szamlaloT[$id]/$ossz;
			$ertek=200*$arany;
			if(empty($szorzo)) $szorzo=200/$ertek;
			$w=intval($ertek*$szorzo);
			$szazalek=intval($arany*100);
			$tartalomT[$kid].="<span class=kicsi>($szamlaloT[$id] - $szazalek%)</span><br>";
			$tartalomT[$kid].="<img src=img/$stat.jpg width=$w height=15><br><img src=img/space.gif widht=5 height=10><br>";		
		}
		$valaszokT='';
		$szamlaloT='';
		$szorzo=0;
		$ossz=0;
		$szam=0;
	}

	$tartalom='<table width=100%><tr>';
	foreach($tartalomT as $ertek) {
		if($szam>0 and $szam%2==0) $tartalom.='</tr><tr>';
		$szam++;
		$tartalom.="<td valign=top width=50%>$ertek</td>";
	}
	$tartalom.='</tr></table>';

	if($min>1) {
		//Visszaléptetés
		$visszalink="<a href=?m_id=$m_id&min=$prev$linkveg class=link>Vissza</a>";
	}

	if($mennyi>$leptet+1) {
		//előre léptetés
		$tovabblink="<a href=?m_id=$m_id&min=$next$linkveg class=link>Tovább</a>";
	}

	$tartalom.="<br>$visszalink";
	if(!empty($visszalink) and !empty($tovabblink)) $tartalom.= ' - ';
	$tartalom.=$tovabblink;

	if($mennyi<2) $tartalom="<span class=alap>Még nincs lejárt szavazás</span>";

	//Főcím
	$adatT[2]="<span class=alcim>Korábbi szavazások eredményei</span><br><br>".$tartalom;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);	

    return $kod;
}



switch($m_op) {
    case 'index':
        $tartalom=szavazas_index();
        break;
	
}

?>
