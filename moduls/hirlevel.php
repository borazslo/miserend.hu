<?

/*
//////////////////////////
 //      alap modul      //
//////////////////////////
*/

function hirlevel_index() {
    global $design_url,$m,$db_name,$m_id,$am_id,$_GET,$_POST,$lang,$linkveg;

	$email=$_POST['email'];
	
    $tartalomkod="<p class=alcim>Hírlevél - feliratkozás</p>";

	if(!empty($email)) {
		//Email ellenőrzés
		if(!strstr($email,'@')) $emailhiba=true;

		if(!$emailhiba) {
			$tartalomkod.="<p class=alap>Köszöntjük a listán! Feliratkozási kérelmét azonnal feldolgozzuk. Hogy nehogy tréfából valaki más akarjon az Ön emailcímével feliratkozni, ezért feliratkozási kérelmét mégegyszer jóvá kell hagyni. A jóváhagyás módjáról körülbelül 10-15 percen belül az <b>előbb megadott</b> <font color=red>($email)</font> e-mail címre tájékoztatást küldünk!</p>";

			$to='';
			//mail($to,$targy,$szoveg,"From: $email"); //mail küldése a communio szerverre
		}
		else  {
			$tartalomkod.="<p class=alap><font color=red>HIBA!</font><br>Ön valószínűleg elírta emailcímét! Kérem nézze meg újra!";
			$tartalomkod.='<form method=post>';
			$tartalomkod.="<input type=hidden name=m_id value=$m_id>";
			$tartalomkod.="<input type=text name=email value='$email' size=25 class='urlap'><br><input type=submit value=Feliratkozás class=urlap>";
			$tartalomkod.='</form>';
		}
	}
	else {
		$tartalomkod.="<p class=alap><b>Kedves Látogatónk!</b><br><br>Önnek lehetősége van feliratkozni hírlevelünkre...</p>";
		$tartalomkod.='<form method=post>';
		$tartalomkod.="<input type=hidden name=m_id value=$m_id>";
		$tartalomkod.="<input type=text name=email value='emailcím' size=25 class='urlap'><br><input type=submit value=Feliratkozás class=urlap>";
		$tartalomkod.='</form>';
	}


	$adatT[2]=$tartalomkod;
	$tipus='doboz';
	$kod=formazo($adatT,$tipus);

    return $kod;
}


switch($m_op) {
    case 'index':
        $tartalom=hirlevel_index();
        break;

}

?>
