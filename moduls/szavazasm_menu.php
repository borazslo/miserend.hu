<?

function szavazas_jobbmenu() {
    global $linkveg,$db_name,$elso,$m_id,$m_op,$sid,$_POST,$_GET,$bgcolor,$fooldal_id;

	if($_POST['v_op']=='add') {
		szavazas_jobbmenu_add();
		$tartalom=szavazas_jobbmenu_eredmenytartalom();
	}
	elseif($_GET['v_op']=='eredmeny') {
		$tartalom=szavazas_jobbmenu_eredmenytartalom();
	}
	else {
		//Tartalom létrehozása
		$datum=date('Y-m-d');
		$query="select id,kerdes,tipus from szavazask where datum<='$datum' and fooldal like '%-$fooldal_id-%' order by datum desc limit 0,1";
		$lekerdez=mysql_query($query);
		list($kid,$kerdes,$ktipus)=mysql_fetch_row($lekerdez);

		$tartalom="\n<form method=post><span class=kiscim>$kerdes</span><br><img src=img/space.gif width=10 height=10><br>";
		$tartalom.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=kid value='$kid'>";
		$tartalom.="\n<input type=hidden name=sid value=$sid><input type=hidden name=m_op value=$m_op>";
		$tartalom.="\n<input type=hidden name=v_op value=add>";

		$query="select id,valasz from szavazasv where kid='$kid' order by sorszam";
		$lekerdez=mysql_query($query);
		while(list($vid,$valasz)=mysql_fetch_row($lekerdez)) {
			$valaszT[$vid]=$valasz;
		}
		$tartalom.='<table width=100% border=0 cellpadding=0 cellspacing=0>';
		if($ktipus=='m') {
			foreach($valaszT as $vid=>$valasz) {
				$tartalom.="\n<tr><td width=5% valign=top><input type=checkbox name=valasz[] value='$vid' class=urlap></td><td width=95% valign=top><span class=alap>$valasz</span></td></tr><tr><td colspan=2><img src=img/space.gif width=5 height=5></td></tr>";
			}
		}
		else {
			foreach($valaszT as $vid=>$valasz) {
				$tartalom.="\n<tr><td width=5% valign=top><input type=radio name=valasz value='$vid'></td><td width=95% valign=top><span class=alap>$valasz</span></td></tr><tr><td colspan=2><img src=img/space.gif width=5 height=5></td></tr>";
			}
		}
		$tartalom.='</table>';

		if(is_array($_GET)) {
			foreach($_GET as $x=>$y) {
				if($x!='sid' and $x!='lang') $parameterekT[]="$x=$y";
			}
			if(is_array($parameterekT)) $parameterek=implode('&',$parameterekT);
		}

		$tartalom.="\n<br><div align=center><input type=submit value=".alapnyelv('Szavazok')." class=urlap>";
		$tartalom.="\n<br><br><a href=?$parameterek&v_op=eredmeny$linkveg class=link>".alapnyelv('Eredmény megtekintése')."</a></div></form>";
	}	

	$kodT[0]="<a href=?m_id=6$linkveg class=hasabcimlink>Szavazás</a>";
	$kodT[1]=$tartalom;

	return $kodT;
}

function szavazas_jobbmenu_add() {
    global $db_name,$_POST,$_SERVER,$szavazott,$sid,$fooldal_id,$u_id;
	
	if(!strstr($szavazott,$fooldal_id)) {
		//CSAK akkor vesszük figyelembe, ha ezzel a sessionnel még nem szavazott ezen az oldalon
		//(új látogató, új böngészõablakkal új sessiont kap)
		$ip=$_SERVER['REMOTE_ADDR'];
		$kid=$_POST['kid'];
		$most=time();
		$lejar=1800; //fél órán belül nem számít új szavazásnak

		if($u_id>0) {
			//Ha belépett felhasználóként szavaz
			$modkieg=", szamlaloreg=szamlaloreg+1";
		}

		//Ellenõrzés:
		$query="select datum from szavazasell where ip='$ip' and kid='$kid'";
		$lekerdez=mysql_query($query);
		list($datum)=mysql_fetch_row($lekerdez);
		if(($most>($datum+$lejar)) or $datum==0) {
			$valasz=$_POST['valasz'];
			if(is_array($valasz)) {
				$feltetel='id='.implode(' or id=',$valasz);
				$query="update szavazasv set szamlalo=szamlalo+1 $modkieg where $feltetel";
			}
			else {
				$query="update szavazasv set szamlalo=szamlalo+1 $modkieg where id='$valasz'";
			}
			mysql_query($query);			
			if($datum>0) //Ha korábban már szavazott, most frissítjük
				mysql_query("update szavazasell set datum='$most' where ip='$ip' and kid='$kid'"); //milyen IP-rõl mikor szavazott
			else //Ha még nem szavazott, létrehozzuk az ellenõrzéshez
				mysql_query("insert szavazasell set datum='$most', ip='$ip', kid='$kid'"); //milyen IP-rõl mikor szavazott
		}
		else {
			//fél órán belül már szavaztak errõl a géprõl, de azért az idõt frissítjük, újra indul a fél óra
			mysql_query("update szavazasell set datum='$most' where ip='$ip' and kid='$kid'"); //milyen IP-rõl mikor szavazott
		}
		$ujszavazott=$szavazott.$fooldal_id;
		mysql_query("update session set szavazott='$ujszavazott' where sessid='$sid'"); //sessionhöz beállítjuk, hogy szavazott
	}
	
}

function szavazas_jobbmenu_eredmenytartalom() {
	global $linkveg,$db_name,$elso,$m_id,$m_op,$fooldal_id;

	$datum=date('Y-m-d');
	$query="select id,kerdes,tipus from szavazask where datum<='$datum' and fooldal like '%-$fooldal_id-%' order by datum desc limit 0,1";
	$lekerdez=mysql_query($query);
	list($kid,$kerdes,$ktipus)=mysql_fetch_row($lekerdez);

	$query="select valasz,szamlalo from szavazasv where kid='$kid' order by szamlalo desc, sorszam";
	$lekerdez=mysql_query($query);
	while(list($valasz,$szamlalo)=mysql_fetch_row($lekerdez)) {
		$valaszokT[]=$valasz;
        $szamlaloT[]=$szamlalo;
		$ossz=$ossz+$szamlalo;
    }

	$tartalom="\n<span class=kiscim>$kerdes</span><span class=kicsi> ($ossz ".alapnyelv('szavazat').")</span><br><br>";

	foreach($valaszokT as $id=>$valasz) {
		$tartalom.="\n<span class=alap>- $valasz</span> ";		
		$szam++;
		if($szam>7) $szam=1;
		$stat='stat'.$szam;		
		if($ossz>0) {
			$arany=$szamlaloT[$id]/$ossz;
			$ertek=140*$arany;
			if(empty($szorzo)) $szorzo=140/$ertek;
			$w=intval($ertek*$szorzo);
			$szazalek=intval($arany*100);
		}
		else $szazalek=0;
		$tartalom.="<span class=kicsi>($szamlaloT[$id] - $szazalek%)</span><br>";
		$tartalom.="<img src=img/$stat.jpg width=$w height=15><br><img src=img/space.gif widht=5 height=10><br>";		
	}

	return $tartalom;
}

function szavazas_jobbmenu_eredmeny() {
    global $linkveg,$db_name,$elso,$m_id,$m_op;

	$tartalom=szavazas_jobbmenu_eredmenytartalom();
	$adatT[0]="<a href=?m_id=6$linkveg class=hasabcimlink>Szavazás</a>";
	$kodT[1]=$tartalom;

	return $kodT;
}


switch($op) {
	case 'eredmeny':
		$hmenuT=szavazas_jobbmenu_eredmeny();
		break;

	case '1':
		$hmenuT=szavazas_jobbmenu();
		break;
	
	case '2':
		$hmenuT=szavazas_jobbmenu();
		break;
}

?>
