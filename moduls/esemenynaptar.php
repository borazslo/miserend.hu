<?

function esemenynaptar_index() {
	global $linkveg,$db_name,$m_id,$m_op,$_GET,$_POST,$design_url,$design;

	if(!isset($desin)) $design='alap';

	$honap=$_POST['honap'];
	if(!isset($honap)) $honap=$_GET['honap'];
	$ev=$_POST['ev'];
	if(!isset($ev)) $ev=$_GET['ev'];

    define("EGYNAP", (60*60*24));
	$szoveg='';
	$imgDIR='img';

    if(!checkdate($honap,1,$ev)) {
        $mostTomb=getdate();
        $honap=$mostTomb["mon"];
        $ev=$mostTomb["year"];
    }

    $kezdet=mktime(0,0,0,$honap,1,$ev);
    $elsoNapTombje=getdate($kezdet);
    if($elsoNapTombje["wday"] == 0)
       $elsoNapTombje["wday"] = 6;
    else
       $elsoNapTombje["wday"]--;

    $szoveg.= '<div align=left><form method=post>';
    $szoveg.= "<input type=hidden name=m value=$m><input type=hidden name=fm value=$fm><input type=hidden name=op value=naptar>";
    $szoveg.= '<select name=ev class=urlap>';
	$max=date('Y')+1;
    for($x=2005;$x<=$max; $x++) {
        $szoveg.= "<option";
        $szoveg.= ($x == $ev) ? " selected":"";
        $szoveg.= ">$x\n";
    }
    $szoveg.= '</select><select name=honap class=urlap>';

    $honapok=Array ("Január","Február","Március","Április","Május","Június","Július",
               "Augusztus","Szeptember","Október","November","December");

    for($x=1;$x<=count($honapok); $x++) {
        $szoveg.= "\t<option value=$x";
        $szoveg.= ($x == $honap)? " selected":"";
        $szoveg.= '>'.$honapok[$x-1]."\n";
    }
    $szoveg.= "</select><input type=hidden name=lang value=$lang>
         <input type=submit value=Mutat class=urlap></form></div>";

    $napok = Array ("Hétfõ","Kedd","Szerda","Csütörtök","Péntek","Szombat","Vasárnap");

    $szoveg.= '<table width=100% border=0 cellspacing=1 cellpadding=0 align=center bgcolor=#0D4081>
          <tr bgcolor=#E5B1A3 valign=middle align=center>
          <td colspan=2 height=30 bgcolor=#FFFAE4>';
    $szoveg.= '<div align=center class=alcim>'.$ev.'. '.($honapok[$honap-1]).'</div>';
    $szoveg.= '</td></tr>';
    $kiirando=$kezdet;
    $napszamlalo=0;

	//ünnepek adatbázisból
	$kezd=date('Y-m-d',$kezdet);
	if(strlen($honap)==1) $honapkiir='0'.$honap;
	else $honapkiir=$honap;
	$veg="$ev-$honapkiir-31";
	$query="select datum,unnep,szabadnap from unnepnaptar where datum>'$kezd' and datum<'$veg'";
	if(!$lekerdez=mysql_query($query)) echo "HIBA!<br>$query<br>".mysql_error();
	while(list($datum,$unnep,$szabadnap)=mysql_fetch_row($lekerdez)) {
		$unnep_ho=substr($datum,5,2);
		$unnep_nap=substr($datum,8,2);
		$unnepdatumT[$unnep_nap]=$unnep;
		$szabadnapT[$unnep_nap]=$szabadnap;
	}

	//események adatbázisból 
	$kezdeleje=date('Y-m-',$kezdet);
	for($i=1;$i<=31;$i++) {
		if($i<10) $j='0'.$i;
		else $j=$i;
		$feltetelT[]="aktualis like '%$kezdeleje$j%'";
	}	
	if(is_array($feltetelT)) $feltetel='and ('.implode(' or ',$feltetelT).')';

	$query="select id,cim,aktualis,tol from hirek where aktualis!='' and ok='i' $feltetel  order by aktualis,cim";
    if(!$eredmeny=mysql_query($query))
        echo '<p class=hiba>HIBA a lekérdezésnél!<br>'.mysql_error();
    if(mysql_num_rows($eredmeny)>0) {
        while(list($id,$cim,$aktualis,$tol)=mysql_fetch_row($eredmeny)) {
			$cimT[$id]=$cim;
			$aktualisT=explode('+',$aktualis); //több dátum is lehetséges
			if(is_array($aktualisT)) {
				foreach($aktualisT as $datumok) {
					$vanesemenyT[$datumok][]=$id;	
					if($datumok==$tol) {
						$tolT[$id][$datumok]=$tol;
						$tolkezdT[$id]=$tol;
					}
					elseif($tol!='0000-00-00' and !empty($tol)) {
						$tolT[$id][$datumok]='ig';
					}
				}				
			}
        }
    }

	//Határidõs hírek
	$query="select id,cim,hatarido from hirek where hatarido>='$kezd' and hatarido<='$veg' and ok='i' order by hatarido,cim";
    if(!$eredmeny=mysql_query($query))
        echo '<p class=hiba>HIBA a lekérdezésnél!<br>'.mysql_error();
	while(list($id,$cim,$hatarido)=mysql_fetch_row($eredmeny)) {
		$hatcimT[$id]=$cim;
		$vanhataridoT[$hatarido][]=$id;
	}

    for($szamlalo=0;$szamlalo<42;$szamlalo++) {
        if($napszamlalo>6) $napszamlalo=0;
        $napTomb = getdate($kiirando);
        //$katunnep = katolikus ünnep, amikor pirossal írjuk ki az ünnepet (vasárnap és fontosabb ünnepeken)

        if(($szamlalo%7)==6) $unnep=1;
        else $unnep=0;

        //Ha már új hónap jönne, akkor megszakítjuk!
        if($napTomb[mon]!=$honap) break;
        
        //ünnepek:
        if($napTomb[mon]==1 and $napTomb["mday"]==1) {$unnep=1;$msg='Újév';}
        elseif($napTomb[mon]==3 and $napTomb["mday"]==15) {$unnep=1;$msg='Nemzeti ünnep';}
		elseif($napTomb[mon]==5 and $napTomb["mday"]==1) {$unnep=1;$msg='Munka ünnepe';}
        elseif($napTomb[mon]==8 and $napTomb["mday"]==20) {$unnep=1;$msg='Nemzeti ünnep';}
        elseif($napTomb[mon]==10 and $napTomb["mday"]==23) {$unnep=1;$msg='Nemzeti ünnep';}
        elseif($napTomb[mon]==11 and $napTomb["mday"]==1) {$unnep=1;$msg='Mindenszentek ünnepe';}
        elseif($napTomb[mon]==12 and $napTomb["mday"]==25) {$unnep=1;$msg='Karácsony';}
        elseif($napTomb[mon]==12 and $napTomb["mday"]==26) {$unnep=1;$msg='Karácsony';}
        else $msg='';

		//mozgó ünnepek (adatbázisból)
		$napnap=$napTomb["mday"];
		if(strlen($napnap)==1) $napnap='0'.$napnap;
		if($szabadnapT[$napnap]=='i') {$unnep=1; $msg=$unnepdatumT[$napnap];}

        if($szamlalo<$elsoNapTombje["wday"] || $napTomb["mon"] != $honap) {
            $napszamlalo++;
        }
        else {
            $szoveg.= "\t<tr><td width=100";
			if(strlen($honap)==1) $honap='0'.$honap;
            $datum=$ev.'-'.$honap.'-'.$napnap;

            //Ha ünnep, akkor piros
            $szoveg.= $unnep==1 ? " bgcolor=#FFFAE4><a class=unnep title='$msg'":" bgcolor=#ECE5C8><a class=link";
            $szoveg.= " href=?m_id=15&m_op=view&date=$datum$linkveg><img src=$imgDIR/space.gif width=10 height=1 border=0>".$napTomb["mday"].'. ';
            
            $szoveg.= $napok[$napszamlalo].'</a></td><td valign=top class=alap';
            $szoveg.= $unnep==1 ? " bgcolor=#FFFAE4>":" bgcolor=#FFFFFF>";

            //Megnézzük, hogy van-e hozzá határidõ és esemény
			$napunk=$napTomb['mday'];
			if(strlen($napunk)==1) $napunk='0'.$napunk;
			$datum=date('Y-m-',$kezdet).$napunk;

			//Határidõk
			$vanhataridoTT=$vanhataridoT[$datum];
			if(is_array($vanhataridoTT)) {
				foreach($vanhataridoTT as $idk) {
					$szoveg.= "<a href=?hir=$idk$linkveg class=linkkiemelt title='Határidõ'><img src=img/naptar.png border=0 align=absmiddle title='Határidõ!'> $hatcimT[$idk]</a><br>";
				}
			}

			//Események
			$vanesemenyTT=$vanesemenyT[$datum];
			if(is_array($vanesemenyTT)) {
				foreach($vanesemenyTT as $idk) {
					if($tolT[$idk][$datum]==$datum) { //tól-ig elsõ napja						
						$szoveg.= "<a href=?hir=$idk$linkveg class=link><img src=$design_url/img/sbarna_negyzet.jpg border=0> $cimT[$idk] </a>";
						$jszam++;
						if($jszam>15) $jszam=1;
						$jszamT[$idk]=$jszam;
						$szoveg.="<img src=img/jelzes$jszamT[$idk].png border=0 align=absmiddle title='a program további napjait zászló jelzi'>";
						$szoveg.='<br>';
					}
					elseif($tolT[$idk][$datum]=='ig') { //tól-ig folytatás
						if(empty($jszamT[$idk])) {
							$jszam++;
							if($jszam>15) $jszam=1;
							$jszamT[$idk]=$jszam;
						}
						$jelzettprogram.= "<a href=?hir=$idk$linkveg class=link title='$cimT[$idk] (program elsõ napja: $tolkezdT[$idk])'><img src=img/jelzes$jszamT[$idk].png border=0></a>";
					}
					else { //sima program
						$szoveg.= "<a href=?hir=$idk$linkveg class=link><img src=$design_url/img/sbarna_negyzet.jpg border=0> $cimT[$idk]</a><br>";
					}					
				}
				if(!empty($jelzettprogram)) $szoveg.="<div class=kicsi>többnapos programok: ".$jelzettprogram.'</div>';
				$jelzettprogram='';
			}
            else $szoveg.= '&nbsp;';

			if($napok[$napszamlalo]=='Vasárnap') {
				$szoveg.="<tr><td colspan=2 bgcolor=#AF815D><img src=img/space.gif width=4 height=4></td></tr>";
			}

            $kiirando += EGYNAP;
            $napszamlalo++;
            
            //Ellenõrizzük, hogy az óraállításoknál ne legyen kétszer ugyanaz a nap!
            $ujnaptomb=getdate($kiirando);
            if($ujnaptomb[mday]==$napTomb[mday]) $kiirando += EGYNAP;
            $szoveg.="</td></tr>\n";
        }
    }
    $szoveg.='</table><br>';

	$kovhonap=$honap+1;
	$kovev=$ev;
	if($kovhonap>12) {
		$kovhonap=1;
		$kovev=$ev+1;
	}

	$szoveg.="<div align=left><a href=?m_id=$m_id&ev=$kovev&honap=$kovhonap$linkveg class=link>Következõ hónap</a></div";
	
	$tartalom=$szoveg;


	$adatT[2]="<span class=alcim>Eseménynaptár</span><br><br>".$tartalom;
	$tipus='doboz';
	$tartalom=formazo($adatT,$tipus);	

	//Közeli határidõk keresése
	$ma=date('Y-m-d');
	$egynap=86400;
	$hetvege=date('Y-m-d',(time()+(7*$egynap)));
	$query="select id,cim,hatarido from hirek where ok='i' and hatarido>='$ma' and hatarido<='$hetvege' order by hatarido limit 0,10";
	$lekerdez=mysql_query($query);
	while(list($id,$cim,$hatarido)=mysql_fetch_row($lekerdez)) {
		if($hatarido==$ma) {
			$hataridokiir='';
			$nap='<font color=red><b>ma!</b></font>';
		}
		else {
			$datido=mktime(0,0,0,substr($hatarido,5,2),substr($hatarido,8,2),substr($hatarido,0,4));
			$nap=date('w',$datido);
			$nap=alapnyelv("nap$nap");
		    $hataridokiir=str_replace('-','.',$hatarido).'.';
		    if(substr($hatarido,0,4)==date('Y')) $hataridokiir=substr($hataridokiir,5);
		}
	    $hataridokT[]="<td valign=top><img src=img/space.gif width=8 height=5><br><img src=$design_url/img/sbarna_negyzet.jpg width=6 height=7></td><td valign=top><a href=?hir=$id$linkveg class=focimlink_sbarna>$cim</a><span class=kicsi><i> (határidõ: $nap)</i></span></td>";			
	}

	if(is_array($hataridokT) and count($hataridokT)>0) $hataridok='<table cellpadding=0 cellspacing=2>'.implode('<tr><td colspan=2><img src=img/space.gif width=5 height=3></td></tr>',$hataridokT).'</table>';
	else $hataridok="<span class=alap>a következõ napokban nincs határidõ</span>";

	$tmpl_file = $design_url.'/sablon_naptar.htm';
	$thefile = implode("", file($tmpl_file));
	$thefile = addslashes($thefile);
	$thefile = "\$r_file=\"".$thefile."\";";
	eval($thefile);

	$kod = $r_file;

    return $kod;
}

function esemenynaptar_view() {
	global $db_name,$design_url,$_GET,$linkveg;
	$date=$_GET['date'];
	$ev=substr($date,0,4);
	$honap=substr($date,5,2);
	$nap=substr($date,8,2);

    $szoveg.= '<table width=100% border=0 cellspacing=1 cellpadding=0 align=center bgcolor=#0D4081><tr>';
	
	$ido=mktime(0,0,0,$honap,$nap,$ev);
	$honapnev=alapnyelv("ho$honap");
	$napszam=date('w',$ido);
	$napnev=alapnyelv("nap$napszam");	
	if($nap[0]=='0') $nap=$nap[1];

	if($napszam==0) $unnep=true;
	else $unnep=false;

    //ünnepek:
	if($honap=='01' and $nap=='01') {$unnep=true;$unnepkiir='Újév';}
	elseif($honap=='03' and $nap=='15') {$unnep=true;$unnepkiir='Nemzeti ünnep';}
	elseif($honap=='05' and $nap=='01') {$unnep=true;$unnepkiir='Munka ünnepe';}
	elseif($honap=='08' and $nap=='20') {$unnep=true;$unnepkiir='Nemzeti ünnep';}
	elseif($honap=='10' and $nap=='23') {$unnep=true;$unnepkiir='Nemzeti ünnep';}
	elseif($honap=='11' and $nap=='01') {$unnep=true;$unnepkiir='Mindenszentek ünnepe';}
	elseif($honap=='12' and $nap=='25') {$unnep=true;$unnepkiir='Karácsony';}
	elseif($honap=='12' and $nap=='26') {$unnep=true;$unnepkiir='Karácsony';}

	//ünnepek adatbázisból
	$query="select unnep,szabadnap from unnepnaptar where datum='$date'";
	if(!$lekerdez=mysql_query($query)) echo "HIBA!<br>$query<br>".mysql_error();
	list($unnepkiir1,$szabadnap)=mysql_fetch_row($lekerdez);
	if($szabadnap=='i') $unnep=true;
	if(!empty($unnepkiir1)) $unnepkiir=$unnepkiir1;

    $szoveg.='<td colspan=2 height=30';
	if($unnep) $szoveg.=' bgcolor=#d45a7a>';	
	else $szoveg.=' bgcolor=#FFFAE4>';
	$szoveg.= '<div align=center class=alcim>'.$ev.". $honapnev $nap. <i>$napnev</i></div>";
	if(!empty($unnepkiir)) $szoveg.="<img src=img/space.gif width=5 height=5><div align=center class=alap>$unnepkiir</div>";
    $szoveg.= '</td></tr>';
    
	$query="select datum,unnep,szabadnap from unnepnaptar where datum='$date'";
	if(!$lekerdez=mysql_query($query)) echo "HIBA!<br>$query<br>".mysql_error();
	list($datum,$unnep,$szabadnap)=mysql_fetch_row($lekerdez);

	//Határidõs hírek
	$query="select id,cim from hirek where hatarido='$date' and ok='i' order by hatarido,cim";
    if(!$eredmeny=mysql_query($query))
        echo '<p class=hiba>HIBA a lekérdezésnél!<br>'.mysql_error();
	if(mysql_num_rows($eredmeny)>0) {
		$szoveg.="\n<tr><td bgcolor=#ffffff colspan=2><span class=linkkiemelt><img src=img/space.gif width=5 height=4><br>HATÁRIDÕK EZEN A NAPON:</span>";
		while(list($id,$cim)=mysql_fetch_row($eredmeny)) {
			$szoveg.="\n<br><a href=?hir=$id$linkveg class=linkkiemelt title='Határidõ!'><img src=img/naptar.png border=0 align=absmiddle title='Határidõ!'> $cim</a>";
		}
		$szoveg.='<BR>&nbsp;</td></tr>';
	}


	//események adatbázisból 
	$query="select id,cim,intro,rovatkat,aktualis,tol,orszag,megye,varos,egyhazmegye,espereskerulet,hatarido,szervezotipus,szervezonev,fizetos from hirek where aktualis!='' and aktualis like '%$date%' and ok='i' order by cim";
    if(!$eredmeny=mysql_query($query))
        echo '<p class=hiba>HIBA a lekérdezésnél!<br>'.mysql_error();
	if(mysql_num_rows($eredmeny)>0) {
		//////////////////////////////////////////////////////////////////////////////////////////
		$query="select id,nev from egyhazmegye where ok='i' order by sorrend";
		$lekerdez=mysql_query($query);
		while(list($id,$nev)=mysql_fetch_row($lekerdez)) {
			$ehmT[$id]=$nev;
		}

		$query="select id,ehm,nev from espereskerulet";
		$lekerdez=mysql_query($query);
		while(list($id,$ehm,$nev)=mysql_fetch_row($lekerdez)) {
			$espkerT[$id]=$nev;
		}

		$query="select id,nev from orszagok";
		$lekerdez=mysql_query($query);
		while(list($id,$nev)=mysql_fetch_row($lekerdez)) {
			$orszagT[$id]=$nev;
		}

		$query="select id,megyenev,orszag from megye";
		$lekerdez=mysql_query($query);
		while(list($id,$nev,$orszag)=mysql_fetch_row($lekerdez)) {
			$megyeT[$id]=$nev;
		}

		$query="select id,nev from rovatkat where ok='i'";
		$lekerdez=mysql_query($query);
		while(list($id,$nev)=mysql_fetch_row($lekerdez)) {
			$rovatkatT[$id]=$nev;
		}

		$query="select id,nev from szervezotipus";
		$lekerdez=mysql_query($query);
		while(list($id,$nev)=mysql_fetch_row($lekerdez)) {
			$szervezoT[$id]=$nev;
		}
		//////////////////////////////////////////////////////////////////////////////////////////

		$szoveg.="<tr><td bgcolor=#F1E6D2 valign=top><img src=img/space.gif width=5 height=4><br><span class=focimlink_piros>A NAP ESEMÉNYEI:</span>";
	    while(list($id,$cim,$intro,$rovatkat,$aktualis,$tol,$orszag,$megye,$varos,$egyhazmegye,$espereskerulet,$hatarido,$szervezotipus,$szervezonev,$fizetos)=mysql_fetch_row($eredmeny)) {
	    		$tobbnapos=false;
			$info='';
			$txt='';
			if(!empty($hatarido) and $hatarido!='0000-00-00') $info.="<div class=linkkiemelt><u>Határidõ:</u> $hatarido</div>";
			if(!empty($szervezonev)) $info.="<div class=kicsi><u>Szervezõ:</u> <b>$szervezonev</b></div>";
			if(!empty($szervezotipus)) $info.="<div class=kicsi><u>Típus:</u> <b>$szervezoT[$szervezotipus]</b></div>";

			$rovatkat=str_replace('--','!',$rovatkat);
			$rovatkat=str_replace('-','',$rovatkat);
			$rovatT=explode('!',$rovatkat);
			$rovatokT='';
			foreach($rovatT as $idk) {
				if($idk<7) $rovatokT[]="<b>$rovatkatT[$idk]</b>";
				else $rovatokT[]=$rovatkatT[$idk];
			}
			if(is_array($rovatokT)) {
				$rovatok=implode(', ',$rovatokT);
				$info.="<div class=kicsi><u>Kategóriák:</u> $rovatok</div>";
			}
			
			if(!empty($varos)) $info.="<div class=kicsi><u>Helyszín:</u> <b><a title='$megyeT[$megye] megye'>$varos</a></b></div>";
			elseif(!empty($megye)) $info.="<div class=kicsi><u>Helyszín:</u> <b>$megyeT[$megye]</b> megye</div>";
			elseif(!empty($orszag)) $info.="<div class=kicsi><u>Helyszín:</u> <b>$orszagT[$orszag]</b></div>";

			if(!empty($egyhazmegye)) $info.="<div class=kicsi><u>Egyházmegye:</u> <b>$ehmT[$egyhazmegye]</b></div>";
			if(!empty($espereskerulet)) $info.="<div class=kicsi><u>Espereskerület:</u> <b>$espkerT[$espereskerulet]</b></div>";

			if($fizetos=='i') $info.="<div class=kicsi><u>A program fizetõs</u></div>";
			elseif($fizetos=='n') $info.="<div class=kicsi><u>A program ingyenes</u></div>";
			


			$txt.="\n<table cellpadding=4><tr><td valign=top width=10><img src=img/space.gif width=5 height=5><br><img src=$design_url/img/sbarna_negyzet.jpg border=0></td><td valign=top";
			if(empty($info)) $txt.=' colspan=2';
			else $txt.=' width=350';
			$txt.="><a href=?hir=$id$linkveg class=focimlink_sbarna> $cim </a>";
			if($tol==$date) $txt.="<img src=img/jelzes1.png border=0 align=absmiddle title='többnapos program'>";
			elseif($tol!='0000-00-00') {
				$tobbnapos=true;
				$tolkiir=substr($tol,0,4).'. ';
				$tolkiir.=alapnyelv('ho'.substr($tol,5,2));
				$tolnap=substr($tol,8,2);
				if($tolnap[0]=='0') $tolnap=$tolnap[1];
				$tolkiir.=' '.$tolnap.'.';
				
				$txt.="<br><img src=img/jelzes6.png border=0 align=absmiddle title='többnapos program'><span class=kicsi> <b>Többnapos esemény, kezdõnap: </b><a href=?m_id=15&m_op=view&date=$tol class=linkkicsi><u><b>$tolkiir</b></u></a></span>";
			}
			$txt.="<br><div class=alapkizart>$intro</div>";

			if(!empty($info)) $txt.="</td><td width=150 class=naptarinfo valign=top>$info";
			
			$txt.="</td></tr></table>";
			$txt.="<div align=center><img src=img/vonal1.gif width=530 height=6></div>";
			if(!$tobbnapos) $szoveg1.=$txt;
			else $szoveg2.=$txt; //többnapos események a végére
		}
		$szoveg.=$szoveg1.$szoveg2."<br>&nbsp;</td></tr>";
	}
	else {
		$szoveg.="<tr><td bgcolor=#F1E6D2 valign=top><span class=focimlink_piros><br>&nbsp; Erre a napra nincs esemény adatbázisunkban.</span><br><br>";
		$szoveg.="</td></tr>";
	}



    $szoveg.='</table><br>';

	//Elõzõ / következõ nap!!!
	///////////////////////////


	$tartalom=$szoveg;


	$adatT[2]="<span class=alcim>Eseménynaptár</span><br><br>".$tartalom;
	$tipus='doboz';
	$tartalom=formazo($adatT,$tipus);	

	//Közeli határidõk keresése
	$ma=date('Y-m-d');
	$egynap=86400;
	$hetvege=date('Y-m-d',(time()+(7*$egynap)));
	$query="select id,cim,hatarido from hirek where ok='i' and hatarido>='$ma' and hatarido<='$hetvege' order by hatarido limit 0,10";
	$lekerdez=mysql_query($query);
	while(list($id,$cim,$hatarido)=mysql_fetch_row($lekerdez)) {
		if($hatarido==$ma) {
			$hataridokiir='';
			$nap='<font color=red><b>ma!</b></font>';
		}
		else {
			$datido=mktime(0,0,0,substr($hatarido,5,2),substr($hatarido,8,2),substr($hatarido,0,4));
			$nap=date('w',$datido);
			$nap=alapnyelv("nap$nap");
		    $hataridokiir=str_replace('-','.',$hatarido).'.';
		    if(substr($hatarido,0,4)==date('Y')) $hataridokiir=substr($hataridokiir,5);
		}
	    $hataridokT[]="<td valign=top><img src=img/space.gif width=8 height=5><br><img src=$design_url/img/sbarna_negyzet.jpg width=6 height=7></td><td valign=top><a href=?hir=$id$linkveg class=focimlink_sbarna>$cim</a><span class=kicsi><i> (határidõ: $nap)</i></span></td>";			
	}

	if(is_array($hataridokT) and count($hataridokT)>0) $hataridok='<table cellpadding=0 cellspacing=2>'.implode('<tr><td colspan=2><img src=img/space.gif width=5 height=3></td></tr>',$hataridokT).'</table>';
	else $hataridok="<span class=alap>a következõ napokban nincs határidõ</span>";

	$tmpl_file = $design_url.'/sablon_naptar.htm';
	$thefile = implode("", file($tmpl_file));
	$thefile = addslashes($thefile);
	$thefile = "\$r_file=\"".$thefile."\";";
	eval($thefile);

	$kod = $r_file;

	return $kod;
}

switch($m_op) {
    case 'index':
        $tartalom=esemenynaptar_index();
        break;

	case 'view':
        $tartalom=esemenynaptar_view();
        break;
	
}

?>
