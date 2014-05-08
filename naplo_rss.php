<?php
include("config.inc");
dbconnect();

	/* jogok ellenőrzése */
	$sid=$_GET['sid'];
	$query="select login,jogok from session where sessid='$sid'";
	$lekerdez=mysql_query($query);
	list($login,$jogok)=mysql_fetch_row($lekerdez);
	if(!strstr($jogok,'miserend')) {
			echo $header.$kidob.$footer; 	
			exit();
		}

	/* szűrő betöltése */
	foreach(array('egyhazmegye','allapot','kkulcsszo','sort') as $var) {
			if(isset($_REQUEST[$var]) AND $_REQUEST[$var] != '') $$var = $_REQUEST[$var];
	}
	$min = 0; $leptet = 10;
	
	/* lekérdezések: ~ moduls/admin_miserend.php line 825-858 */
		if($egyhazmegye!='mind' and isset($egyhazmegye)) {
		$ehmT=explode('-',$egyhazmegye);
		if($ehmT[1]=='0')	$feltetelT[]="egyhazmegye='$ehmT[0]'";
		else $feltetelT[]="espereskerulet='$ehmT[1]'";
	}
	if(!empty($kulcsszo)) $feltetelT[]="(nev like '%$kulcsszo%' or varos like '%$kulcsszo%' or ismertnev like '%$kulcsszo%' or letrehozta like '%$kulcsszo%')";
	if(!empty($allapot)) {
		if($allapot=='e') $feltetelT[]="eszrevetel='i'";
		elseif($allapot=='ef') $feltetelT[]="eszrevetel='f'";
		else $feltetelT[]="ok='$allapot'";
	}
	if(is_array($feltetelT)) $feltetel=' where '.implode(' and ',$feltetelT);

	//Misék lekérdezése
	$querym="select distinct(templom) from misek where torolte=''";
	if(!$lekerdezm=mysql_db_query($db_name,$querym)) echo "HIBA!<br>$querym<br>".mysql_error();
	while(list($templom)=mysql_fetch_row($lekerdezm)) {
		$vanmiseT[$templom]=true;
	}

	//Észrevételek lekérdezése
	$querye="select * from eszrevetelek where hol='templomok'";
	if(!$lekerdeze=mysql_db_query($db_name,$querye)) echo "HIBA!<br>$querym<br>".mysql_error();
	while($eszrevetel=mysql_fetch_array($lekerdeze)) {
		$vaneszrevetelT[$eszrevetel['hol_id']]=true;
		$eszrevetelT[$eszrevetel['hol_id']]=$eszrevetel;
	}

	$query="select id,nev,ismertnev,varos,ok,eszrevetel from templomok $feltetel order by $sort";
	$lekerdez=mysql_db_query($db_name,$query);
	$mennyi=mysql_num_rows($lekerdez);
	if($mennyi>$leptet) {
		$query.=" limit $min,$leptet";
		$lekerdez=mysql_db_query($db_name,$query);
	}
	/**/
	$item = array();
	while(list($tid,$tnev,$tismert,$tvaros,$tok,$teszrevetel)=mysql_fetch_row($lekerdez)) {
		foreach(array('tid','tnev','tismert','tvaros','tok','teszrevetel') as $var) $$var = iconv('ISO-8859-2','UTF-8',$$var);
		$kiir = '';
		
		$jelzes='';
		if($teszrevetel=='i') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";		
		elseif($teszrevetel=='f') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";		
		elseif($vaneszrevetelT[$tid]) $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomag1.gif title='Észrevételek!' align=absmiddle border=0></a> ";		
		if(!$vanmiseT[$tid]) {
			$jelzes.="<img src=img/lampa.gif title='Nincs hozzá mise!' align=absmiddle> ";
		}		
		//Jelzés beállítása -> lampa = nincs kategorizalva, ora = varakozik ok=n, tilos = megjelenhet X, jegyzettömb - szerkesztés alatt (megnyitva)
		//if(!empty($megnyitva)) $jelzes.="<img src=img/edit.gif title='Megnyitva: $megnyitva' align=absmiddle> ";
		//if(empty($rovatkat)) $jelzes.="<img src=img/lampa.gif title='Nincs kategórizálva!' align=absmiddle> ";
		//if(!strstr($megjelenhet,'kurir')) $jelzes.="<img src=img/tilos.gif title='Megjelenés nincs beállítva!' align=absmiddle> ";
		//if($ok!='i') $jelzes.="<img src=img/ora.gif title='Feltöltött hír, áttekintésre vár!' align=absmiddle> ";
		if($tok=='n') $jelzes.="<img src=img/tilos.gif title='Nem engedélyezett!' align=absmiddle> ";
		elseif($tok=='f') $jelzes.="<img src=img/ora.gif title='Feltöltött/módosított templom, áttekintésre vár!' align=absmiddle> ";
		
		$title = $tnev;
		if($tismert != '') $title .= " (".$tismert.", ".$tvaros.")"; else $title .= " (".$tvaros.")";
		
		$kiir .= "\n$jelzes <a href=?m_id=$m_id&m_op=addtemplom&tid=$tid$linkveg class=felsomenulink title='$tismert'><b>- ".$tnev."</b><font color=#8D317C> ($tvaros)</font></a> - <a href=?m_id=$m_id&m_op=addmise&tid=$tid$linkveg class=felsomenulink><img src=img/mise_edit.png title='misék' align=absmiddle border=0>szentmise</a> - <a href=?m_id=$m_id&m_op=deltemplom&tid=$tid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";
		
		if($vaneszrevetelT[$tid]) {
			$kiir .= "\n".iconv('ISO-8859-2','UTF-8',$eszrevetelT[$tid]['leiras'])."<br>";
			$date = $eszrevetelT[$tid]['datum'];
		} else $date = date();
		
		$items[] = array(
			'title' => $title,
			'link' => 'http://miserend.hu/?templom='.$tid,
			'description' => $kiir,
			'date' => $date);
	}
	
	
	/* kiír */
	  header("Content-Type: application/rss+xml; charset=UTF-8");
	  $rssfeed = '<?xml version="1.0" encoding="UTF-8"?>';

	$rssfeed .= '<rss version="2.0">';
	$rssfeed .= '<channel>';
	$rssfeed .= '<title>Miserend - admin</title>';
	$rssfeed .= '<link>http://miserend.hu</link>';
	$rssfeed .= '<description>Private feed of '.$login.'</description>';
	$rssfeed .= '<language>hu</language>';
	$rssfeed .= '<copyright>VPA</copyright>';

	 foreach($items as $item) {
        
 
        $rssfeed .= '<item>';
        $rssfeed .= '<title>' . $item['title'] . '</title>';
        $rssfeed .= '<description>' . $item['description'] . '</description>';
        $rssfeed .= '<link>' . $item['link'] . '</link>';
        $rssfeed .= '<pubDate>' . date("D, d M Y H:i:s O", strtotime($item['date'])) . '</pubDate>';
        $rssfeed .= '</item>';
    }
 
    $rssfeed .= '</channel>';
    $rssfeed .= '</rss>';
 
    echo $rssfeed;
?>
