<?php

function alap_index($id) {

    switch ($id) {
        case 11:
            $title = "Házirend és szabályzat";
            $content = <<<EOD
				<p>A <i>Miserend</i> honlap felhasználókezelése külön vált a <a href="http://plebania.net"><i>Virtuális Plébánia</i></a> portál többi oldalától. A különböző oldalakon külön kell regisztrálni. De ha 2015 előtt regisztrált akár a <i>Miserend</i>, akár a <i>Virtuális Plébánia</i> bármelyik másik aloldalára, akkor az akkor megadott név és jelszó továbbra is érvényes itt is. (Ám megváltoztatása esetén csak itt változik meg.</p>
				<p><strong>A regisztrációra és a honlap használatára az alábbi szabályok érvényesek, kérjük olvasd el figyelmesen!</strong></p>
				<ol>
					<li style="margin: 0 0 12px 0;">A <i>Miserend</i> a <i>Virtuális Plébánia Portálcsoport</i> tagjaként magyar katolikus oldalak. Tartalmát papok és elkötelezett hívek frissítik. <strong>A honlapon megjelenő anyagok tekintetében a Katolikus Egyház hivatalos tanítása a mérvadó!</strong></li>
					<li style="m2argin: 0 0 12px 0;">Felhasználóink között természetesen korra, nemre, felekezetre való tekintet nélkül mindenkit szeretettel látunk! Azonban kérjük az 1. pont tudomásulvételét és egymás kölcsönös tiszteletbentartását!<br/><br/>
						<strong>FONTOS!</strong> Oldalunkon bárki, bármilyen szándékkal jelen lehet, a regisztráció, illetve a különböző kapcsolatok során személyes adataid mindig kellő óvatossággal kezeld! Felhasználóink publikusan megadott adatainak valóságtartalmáért nem tudunk felelősséget vállalni!</li>
					<li style="margin: 0 0 12px 0;">A regisztráció során kötelező megadni emailcímed, valamint egy bejelentkezési nevet. A választott bejelentkezési név viszont mindig egy és csak egy konkrét személyt képviselhet (szervezetet, intézményt vagy csoportot, közösséget, családot nem) és azt csak egy ember használhatja.<br/><br/>
						<strong>FONTOS!</strong> Figyelj, hogy emailcímed mindig aktuális legyen, változás esetén oldalunkon is módosítsd. Kéretlen reklámleveleket nem küldünk, harmadik félnek nem adjuk ki, de adott esetben az itt megadott címre küldünk oldalunkkal kapcsolatos értesítést, figyelmeztetést. Saját érdeked, hogy ezt mindig időben megkaphasd.</li>
					<li style="margin: 0 0 12px 0;">A <i>Miserend</i> regisztrált felhasználójaként használhatod a <i>Miserend</i> által nyújtott szolgáltatásokat, funkciókat, bővítheted az adatbázist, illetve felhasználhatod azt munkád, személyes fejlődésed vagy szórakozásod érdekében.<br/><br/>
						A <i>Miserenden</i> található információkat felhasználhatod, azokra hivatkozhatsz, azonban ez esetben kérjük megjelölni a forrást! </li>
				</ol>
EOD;
            break;

        case 12:
            $title = "Impresszum";
            $content = <<<EOD
				<h3>Üzemeltető</h3>
				<p><a href="http://vpa.hu">Virtu&aacute;lis Pl&eacute;b&aacute;nia Alap&iacute;tv&aacute;ny</a></p>
				<h3>Célunk</h3>
				<p>Önkéntes alapon szerveződő szolgáltató portál üzemeltetésével olyan kommunikációs csatorna működtetése, mely alkalmas közösségépítésre, ismeretterjesztésre, tájékoztatásra, kultúra közvetítésére és szórakoztatásra egyaránt, az evangélium szellemében, a katolikus egyház tanítása alapján.</p>
				<p>Oldalaink kizárólag önkéntesek szerkesztik, sem a közzétetelből, sem reklámból, sem az oldalon megjelenő tartalmak után semmilyen bevétel nem származik, az alapítvány nyereségre nem törekszik, munkáját nem gazdasági szolgáltatásként végzi.</p>
				<h3>Elérhetőség</h3>
            	<ul>
            		<li>Programozás: <a href='mailto:eleklaszlosj@gmail.com'>Elek László SJ</a> (<a href="http://jezsuita.hu">JTMR</a>)</li>
					<li><a href="https://play.google.com/store/apps/details?id=com.frama.miserend.hu">Android alkalmazás</a>: <a href="mailto:maczak.balazs@gmail.com">Maczák Balázs</a></li>
					<li><a href="https://itunes.apple.com/au/app/miserend/id967827488?mt=8">iOS alkalmazás</a>: <a href="mailto:horony.csaba@gmail.com">Horony Csaba</a></li>
        			<li>Tartalmi dolgok: <a href='mailto:info@miserend.hu'>info@miserend.hu</a></li>
				</ul>
EOD;
            break;

        default:
            $title = "404";
            $content = "Nincs ilyen lap. Elnézést.";
            break;
    }

    $vars = array(
        'title' => $title,
        'content' => $content,
        'template' => 'layout'
    );

    return $vars;
}

$tartalom = alap_index($_GET['fm']);
?>
