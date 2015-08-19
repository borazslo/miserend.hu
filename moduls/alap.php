<?

/*
//////////////////////////
 //      alap modul      //
//////////////////////////
*/

function alap_index() {
    global $design_url,$m,$db_name,$m_id,$am_id,$_GET,$lang;

	$fm=$_GET['fm'];
	
	switch ($fm) {
		case 12:
		$tartalomkod .= <<<EOD
		<span class=alcim>Impresszum</span>
		<p class="alap"><strong>&Uuml;zemeltető: </strong><br />
		Virtu&aacute;lis Pl&eacute;b&aacute;nia Alap&iacute;tv&aacute;ny</p>
		<p class="alap"><strong>C&eacute;lunk:</strong><br />
		&Ouml;nk&eacute;ntes alapon szerveződő szolg&aacute;ltat&oacute; port&aacute;l &uuml;zemeltet&eacute;s&eacute;vel olyan kommunik&aacute;ci&oacute;s csatorna műk&ouml;dtet&eacute;se, mely alkalmas k&ouml;z&ouml;ss&eacute;g&eacute;p&iacute;t&eacute;sre, ismeretterjeszt&eacute;sre, t&aacute;j&eacute;koztat&aacute;sra, kult&uacute;ra k&ouml;zvet&iacute;t&eacute;s&eacute;re &eacute;s sz&oacute;rakoztat&aacute;sra egyar&aacute;nt, az evang&eacute;lium szellem&eacute;ben, a katolikus egyh&aacute;z tan&iacute;t&aacute;sa alapj&aacute;n.</p>
		<p class="alap">Oldalaink kiz&aacute;r&oacute;lag &ouml;nk&eacute;ntesek szerkesztik, sem a k&ouml;zz&eacute;tetelből, sem rekl&aacute;mb&oacute;l, sem az oldalon <br />
		megjelenő tartalmak ut&aacute;n semmilyen bev&eacute;tel nem sz&aacute;rmazik, az alap&iacute;tv&aacute;ny nyeres&eacute;gre nem t&ouml;rekszik, munk&aacute;j&aacute;t nem gazdas&aacute;gi szolg&aacute;ltat&aacute;sk&eacute;nt v&eacute;gzi.</p>
EOD;
		break;
		
		default:
			$tartalomkod .= "404 - üres lap";
			break;
	}	

	$adatT[2]=$tartalomkod;
	$tipus='doboz';
	$kod=formazo($adatT,$tipus);

    return $kod;
}


switch($m_op) {
    case 'index':
        $tartalom=alap_index();
        break;

}

?>
