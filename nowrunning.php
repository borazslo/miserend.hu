<?php

function r_dbconnect() {
    global $hiba,$hibauzenet,$hibauzenet_prog,$r_db_name;
    
    $r_db_host = "localhost";
    $r_db_uname = "mkr";
    $r_db_upass = "rudankinga";
    $r_db_name = "radio";

    if(!@mysql_connect($r_db_host, $r_db_uname, $r_db_upass)) {
        $hiba=true;
        $hibauzenet.='Elnézést kérünk, a szolgáltatás jelenleg nem érhetõ el.';
        $idopont=date('Y.m.d. H:i.s');
        $hibauzenet_prog.="Adatbázisszerverhez nem lehet csatlakozni!\n".mysql_error()."\n$idopont";
    }
}

r_dbconnect();

function aktualis_musor() {
	global $r_db_name,$linkveg,$_GET;

	$datum=date('Y-m-d');
	$nap=substr($datum,8,2);
	$ho=substr($datum,5,2);
	$ev=substr($datum,0,4);
	$holnap  = mktime (0,0,0,$ho,$nap+1,$ev);
	$tegnap  = mktime (0,0,0,$ho,$nap-1,$ev);
	$masnap=date('Y-m-d',$holnap);
	$elozonap=date('Y-m-d',$tegnap);

//	$elohangszoro="<span class=mkr_musor> <a href=http://katolikusradio.hu:8080/ramgen/hallgatas.smi class=link><img src=img/real.gif border=0 align=absmiddle alt='Online hallgatás (Real)'></a> <a href=http://katolikusradio.hu/hallgatas.m3u><img src=img/mp3.gif border=0 align=absmiddle alt='Online hallgatás (MP3)'></a></span>";
/*	$elohangszoro="<span class=mkr_musor> <a href=http://katolikusradio.hu:8080/ramgen/hallgatas.smi class=link><img src=img/real.gif border=0 align=absmiddle alt='élõ adás (Real)'></a>
  <a href=http://katolikusradio.hu/listen-hi.m3u><img src=img/mp3.gif border=0 align=absmiddle alt='élõ adás (MP3)'></a>
  <a href=http://katolikusradio.hu/listenogg-hi.m3u><img src=img/xifish_sm.gif border=0 align=absmiddle alt='élõ adás (Ogg Vorbis)'></span>";
*/
	$elohangszoro="<span class=mkr_musor> <a href=http://katolikusradio.hu:8080/ramgen/hallgatas.smi class=link title='élõ adás (Real)'><img src=img/real.gif border=0 align=absmiddle></a>
  <a href=http://katolikusradio.hu/listen-hi.m3u title='élõ adás (MP3)'><img src=img/mp3.gif border=0 align=absmiddle></a>
  <a href=http://katolikusradio.hu/listenogg-hi.m3u title='élõ adás (Ogg Vorbis)'><img src=img/xifish_sm.gif border=0 align=absmiddle></a></span>";


	$max=$_GET['max'];
	if(!isset($max)) $max=7; //Aktuális mûsorlistában lévõ mûsorok száma

	$query="select id,datum,kezdido,cim,kezdomusor,leiras,munkatarsak,musorid from musor where ok='i' and  (datum='$masnap' or datum='$elozonap' or datum='$datum') order by datum,kezdido";
	if(!$lekerdez=mysql_db_query($r_db_name,$query)) echo 'HIBA!<br>'.mysql_error();
	while(list($id,$mdatum,$kezdido,$cim,$kezdomusor,$leiras,$munkatarsak,$musorid)=mysql_fetch_row($lekerdez)) {

			if($kezdido[0]==0) $kezdes='<img src=img/space.gif widht=7 height=3>'.substr($kezdido,1,4);
			else $kezdes=substr($kezdido,0,5);

			$most=time();
			$mnap=substr($mdatum,8,2);
			$mho=substr($mdatum,5,2);
			$mev=substr($mdatum,0,4);
			$mora=substr($kezdido,0,2);
			$mperc=substr($kezdido,3,2);
			$musoridopont=mktime ($mora,$mperc,0,$mho,$mnap,$mev);

			$mennyi=count($linkT);
			
			if($mennyi<=$max) {
				if($musoridopont>$most and $musormegy) {
					//Az elõzõ ciklusban értékelt mûsor most megy
					$idopont=str_replace('mkr_musor','mkr_musormost',$idopont);
					$musorcim=str_replace('mkr_musor','mkr_musormost',$musorcim);
					$linkT[]=$idopont.'<td valign=top>'.$musorcim.'<br>'.$elohangszoro.'</td></tr><tr><td colspan=2><img src=img/space.gif width=5 height=10></td></tr>';
				}
				elseif($musoridopont>$most) {
					//Az elõzõ és ezen mûsor se megy még
					$linkT[]=$idopont.'<td valign=top>'.$musorcim.'</td></tr>';
				}
				if($kezdomusor=='i' and $mennyi>0) {
					$linkT[]='<tr><td colspan=2><span class=mkr_musor>------------------------</span></td></tr>';
				}
			}

			
			$idopont="\n<tr><td valign=top width=20><div align=right class=mkr_musor>$kezdes</div></td>";
			
			if($musoridopont<$most) {
				$musormegy=true;
				//Ha már lejárt mûsor, vagy épp most megy
			}
			else {
				$musormegy=false;
			}

			if(!empty($leiras) or !empty($munkatarsak) or !empty($musorid)) {
				$musorcim="<a href=http://www.katolikusradio.hu/?m_id=4&m_op=viewmusor&id=$id$linkveg class=mkr_musor target=_blank>$cim</a>";
			}
			else {
				$musorcim="<span class=mkr_musor>$cim</span>";
			}			
	}
	$mennyi=count($linkT);
	if($mennyi<=$max) {
		$linkT[]=$idopont.'<td valign=top>'.$musorcim.'</td></tr>';
	}
	
	$tartalom.='<div align=center><table width=90%>'.implode('',$linkT).'</table></div>';

	return $tartalom;
}




$tabla="<table cellpadding=0 cellspacing=1 border=0 bgcolor=#ccccff><tr><td><table border=0 bgcolor=#ffffff><tr><td background=http://www.katolikusradio.hu/kislogo2.jpg cellspacing=0 cellpadding=5><div align=center><a href=http://www.katolikusradio.hu target=_blank class=mkr_musormost>A Magyar Katolikus<br>Rádió mûsora</a></div><br>";

$tablavege="</td></tr></table>";

$focim="<br><div align=center><a href=http://www.katolikusradio.hu target=_blank class=mkr_musormost>A Magyar Katolikus<br>Rádió mûsora</a></div><br>";

//return $tabla.aktualis_musor().$tablavege;
return aktualis_musor();

?>
