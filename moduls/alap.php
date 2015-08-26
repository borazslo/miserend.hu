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
		case 11:
			$tartalomkod.= <<<EOD
				<span class=alcim>Házirend és szabályzat</span>
				<p class="alap">A <i>Miserend</i> honlap felhasználókezelése külön vált a <a href="http://plebania.net"><i>Virtuális Plébánia</i></a> portál többi oldalától. A különböző oldalakon külön kell regisztrálni. De ha 2015 előtt regisztrált akár a <i>Miserend</i>, akár a <i>Virtuális Plébánia</i> bármelyik másik aloldalára, akkor az akkor megadott név és jelszó továbbra is érvényes itt is. (Ám megváltoztatása esetén csak itt változik meg.</p>
				<p class="alap"><strong>A regisztrációra és a honlap használatára az alábbi szabályok érvényesek, kérjük olvasd el figyelmesen!</strong></p>
				<ol>
					<li class="alap" style="margin: 0 0 12px 0;"><A <i>Miserend</i> a <i>Virtuális Plébánia Portálcsoport</i> tagjaként magyar katolikus oldalak. Tartalmát papok és elkötelezett hívek frissítik. <strong>A honlapon megjelenő anyagok tekintetében a Katolikus Egyház hivatalos tanítása a mérvadó!</strong></li>
					<li class="alap" style="margin: 0 0 12px 0;">Felhasználóink között természetesen korra, nemre, felekezetre való tekintet nélkül mindenkit szeretettel látunk! Azonban kérjük az 1. pont tudomásulvételét és egymás kölcsönös tiszteletbentartását!<br/><br/>
						<strong>FONTOS!</strong> Oldalunkon bárki, bármilyen szándékkal jelen lehet, a regisztráció, illetve a különböző kapcsolatok során személyes adataid mindig kellő óvatossággal kezeld! Felhasználóink publikusan megadott adatainak valóságtartalmáért nem tudunk felelősséget vállalni!</li>
					<li class="alap" style="margin: 0 0 12px 0;">A regisztráció során kötelező megadni emailcímed, valamint egy bejelentkezési nevet. A választott bejelentkezési név viszont mindig egy és csak egy konkrét személyt képviselhet (szervezetet, intézményt vagy csoportot, közösséget, családot nem) és azt csak egy ember használhatja.<br/><br/>
						<strong>FONTOS!</strong> Figyelj, hogy emailcímed mindig aktuális legyen, változás esetén oldalunkon is módosítsd. Kéretlen reklámleveleket nem küldünk, harmadik félnek nem adjuk ki, de adott esetben az itt megadott címre küldünk oldalunkkal kapcsolatos értesítést, figyelmeztetést. Saját érdeked, hogy ezt mindig időben megkaphasd.</li>
					<li class="alap" style="margin: 0 0 12px 0;">A <i>Miserend</i> regisztrált felhasználójaként használhatod a <i>Miserend</i> által nyújtott szolgáltatásokat, funkciókat, bővítheted az adatbázist, illetve felhasználhatod azt munkád, személyes fejlődésed vagy szórakozásod érdekében.<br/><br/>
						A <i>Miserenden</i> található információkat felhasználhatod, azokra hivatkozhatsz, azonban ez esetben kérjük megjelölni a forrást! </li>
				</ol>
EOD;
		break;

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
