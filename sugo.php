<?php include("load.php"); ?>
<html>
<head>
<title>VPP - Súgó</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style TYPE="text/css">

.alap { font-family: Arial, Verdana; font-size: 10pt; text-align: justify; }

</style>
</head>

<body bgcolor="#FFFFFF" text="#000000">

<?php

$idT=explode('-',$_REQUEST['id']);

foreach($idT as $id) {
	$kod.="<div class='alap help'>";
	switch ($id) {
		case 1:
			//miserend - templom adatlap
			$kod.='<b>Megjegyzés</b> (a szerkesztéssel kapcsolatban)
<br><br>Ide akkor kell beírni, ha a feltöltésnél van valami olyan körülmény, amiről fontos, hogy minden rögzítő, aki megnyithatja az űrlapot módosításra, tudjon.
<br><br>Pl. "még ne engedélyezd, most egyeztetek a plébánossal" vagy bármi hasonló';			
			break;
		
		case 2:
			//miserend - templom adatlap
			$kod.='<b>Felelős</b>
<br><br>Annak az adatai, aki kb. negyedévente megkereshető a templom adataival kapcsolatban, tud a változásokról, naprakész információi vannak.';			
			break;
		
		case 3:
			//miserend - templom adatlap
			$kod.='<b>Templom neve</b>
 <br><br>Ide a templom valódi nevét kell beírni, ami nem mindig azonos azzal, ahogy a helybeliek hívják!
 <br><br>Ha ezt nem tudjuk, akkor lehet ismert nevet vagy plébánia nevet is beírni, de ezt mindenképp jelezzük a szerkesztői megjegyzés részben, ahol egy későbbi módosítás során esetleg utánajárhatunk a valódi névnek.';			
			break;
		
		case 4:
			//miserend - templom adatlap
			$kod.='<b>Közismert neve</b>
 <br><br>Ide azt a nevet kell megadni, akár többet is, ahogy a helybeliek ismerik a templomot.  <br>Abban az esetben, ha olyan településen van a templom, ami időközben másik településbe olvadt, de azon belül még ismerik a régi nevet, azt is ide kell rögzíteni, pl. "izbégi templom", ami egyébként már Szentendréhez tartozik.';			
			break;
		
		case 5:
			//miserend - templom adatlap
			$kod.='<b>Templom címe</b>
 <br><br>Értelemszerűen a templom (és nem a plébánia!) elérhetőségeit kell megadni. A listákból a kezdőbetüket begépelve könnyen kereshető a település.
 <br><br>Megközelítésnek egy rövid leírást célszerű beírni (ha tudjuk), hogyan kell eljutni a templomhoz, amivel az odalátogatókat segítjük.';			
			break;
		
		case 6:
			//miserend - templom adatlap
			$kod.='<b>Plébánia adatai</b>
 <br><br>Itt a templomhoz kapcsolódó plébánia elérhetőségét kell megadni, ha pl. valami ügyintézés (esküvő, ilyesmi) miatt szeretne valaki érdeklődni, akkor hol teheti meg.
 <br><br>A plébánosok és minden alkalmazott névsora nem szükséges, de ha frissíthető, akkor persze néha jó tudni, hogy ki ott a plébános.';			
			break;
		
		case 7:
			//miserend - templom adatlap
			$kod.='<b>Ünnep adatok</b>
 <br><br>Ide alapvetően a búcsút és időpontját kell beírni (pl. a templom búcsúja augusztus 20.)
 <br><br>Előfordulhat több búcsú, vagy más rendszeres ünnep, esetleg ezen ünnepek nem csak a templomot, hanem környezetét is érinthetik, ilyenkor rövid leírást is be lehet írni.';
			break;
		
		
		case 9:
			//miserend - templom adatlap
			$kod.='<b>Részletes leírás, templom története</b>
 <br><br>Ide a templom történeti leírása, összefoglalása kerülhet, illetve minden érdekesség a templommal kapcsolatban.';
			break;
		
		case 10:
			//miserend - templom adatlap
			$kod.='<b>Megjegyzés</b>
 <br><br>Ide kerülhet minden olyan, ami a templommal összefügg, fontos információ, pl. búcsú időpontja, védőszent, plébániával kapcsolatos rövid (!) "reklámok".
 <br><br>Az ide írt szöveg a templom bemutatásánál jelenik meg "jó tudni..." című dobozban.
 <br><br>FONTOS! A rendszeres alkalmak infomrációi (pl. rózsafűzér, szentségimádás, hittan) NEM ide való, annak a miserend űrlap tetején van egy megjegyzés mező!';
			break;

		case 11:
			//miserend - templom adatlap
			$kod.='<b>Képek</b>
 <br><br>Feltölthetők templomképek, egy templomhoz maximum 20 db. Fontos, hogy a fájlnevekben ne legyen ékezet vagy szóköz, valamint az azonos fájlnevű képek felülírják egymást, erre figyeljünk oda! Ilyenkor két azonos képet fogunk látni, méghozzá az utóbb feltöltöttet. Ez esetben töröljük mindkettőt és töltsük fel újra.
 <br><br>Lehetőség szerint CSAK jpg (esetleg gif) fájlokat töltsünk föl! A rendszer automatikusan kicsinyít, így a mérettel nem kell foglalkozni.
 <br><br>A feltöltött képek alatt jelölhető, hogy a kép a főoldalra (véletlenszerű válogatás) kikerüljön-e. Ha nincs kijelölve a négyzet, akkor nem teszi ki. Térképnél, kevésbé látványos templombelsőnél inkább ne jelöljük be!';
			break;

		case 12:
			//miserend - templom adatlap
			$kod.='<b>Letölthető fájlok</b>
 <br><br>Erre valószínű nem lesz szükség, de ha a templomhoz mégis kapcsolódna valami olyan dokumentum, amit érdemes fájlként megjeleníteni róla, akkor azt itt lehet feltölteni.
 <br><br>A feltöltés után a kiírt listából kimásolva lehet a szöveges (tartalmi leírás) mezőbe bemásolni, elhelyezni.';
			break;


		case 14:
			//miserend - templom adatlap
			$kod.='<b>Frissítés</b>
 <br><br>Minden templomhoz ki lesz írva, hogy mikoriak a információink, segítségképpen, hogy vajon mennyire lehet aktuális. Itt tehát akkor kell a dátumfrissítést bejelölni, ha az adatokat átnéztük és rendben találtuk. Ha csak egy helyesírási hibát javítottunk, vagy hasonló kis javítás történt, de az adatok valódiságáról nincs tudomásunk, akkor nem kell frissíteni.';
			break;

		case 15:
			//miserend - templom adatlap
			$kod.='<b>Megjelenhet:</b>
 <br><br>Itt lehet beállítani, hogy a templom valamiért (hiányos adatok, tesztelés, stb.) nem jelenhet meg, vagy új feltöltés, módosítás miatt még várakozik. 
 <br>Ha az adatok rendben vannak, akkor itt engedélyezhetjük.';
			break;

		case 16:
			//miserend - templom adatlap
			$kod.='<b>Turistautak ID</b>
 <br><br>A turistautak.hu oldal gazdái gyűjtik a templomok (GPS) koordinátáit. Ebből térképen pontosan meghatározható a templom elhelyezkedése. Nekünk nem kell külön felmérnünk, hanem az elérhető <a href=http://turistautak.hu/search.php?s=templom target=_blank>listából</a> elég kikeresnünk a templomot (ha megvan) és ráklikkelve a címsorban megjelenő id utáni értéket kell ide beírnunk. 
 <br><br>Egy példa: tardosi rk. templom linkje: http://turistautak.hu/poi.php?<b>id=6515</b>
 <br>Az űrlapba beírandó szám tehát a 6515';
			break;

		case 17:
			//user
			$kod.='<b>Becenév, megszólítás:</b>
 <br><br>Portálunkon mindenki az itt megadott (bece)nevével szerepel, hangsúlyosan ez a név látható. Így nem okoz gondot, ha egy bejelentkezési név már foglalt, lehet másikat regisztrálni, s itt meg tudjuk adni kedvenc becenevünket, ahogy szeretnénk, hogy megszólítsanak.
 
 Mivel így több azonos becenevű felhasználó is lehetséges, az azonosítás miatt (amennyiben a becenévtől különböző) valamilyen formában megjelenik a bejelentkezési név is.';
			break;

		case 18:
			//user
			$kod.='<b>Bejelentkezési név</b>
 <br><br>Ezzel a névvel tudsz belépni, ez a név azonosít, így csak olyan nevet választhatsz, amit más még nem regisztrált. Előfordulhat, hogy a beírt név már foglalt, ez esetben másikat kell választani.
 <br><br>Szóköz, idézőjel és aposztróf nem lehet a bejelentkezési névben, de javasoljuk, hogy ékezetek és speciális karakterek se legyenek, a későbbi belépések során ezek gondot okozhatnak.';
			break;

		case 19:
			//user
			$kod.='<b>Email cím</b>
 <br><br>Az itt megadott emailcímre küldjük ki a belépéshez szükséges jelszót, amit a későbbiekben a beállítások menüben tudsz majd megváltoztatni.
 <br>Továbbá ez az emailcím szolgál a kapcsolattartásra, valamint ezzel a címeddel tudsz feliratkozni különböző hírleveleinkre.
 <br><br>Emailcímed nyilvánosságáról külön dönthetsz.';
			break;

		case 20:
			//user
			$kod.='<b>Név:</b>
 <br><br>Itt megadhatod teljes neved. A mező kitöltése nem kötelező, de akár a nyilvánosságot is állíthatod, így meghatározva, hogy mely kör az, akik láthatják megadott neved, s a többiek nem.';
			break;

		case 21:
			//user
			$kod.='<b>Bemutatkozás</b>
 <br><br>Elsősorban a fórumon, de egyéb kapcsolatoknál is segítség lehet, ha pár szót írsz magadról. Nem célszerű önéletrajzot írni, de egy rövid bemutatkozás segíthet másokat, hogy megismerjenek.
 <br><br>A bemutatkozás nyilvánossága állítható, így szűrhető, hogy ne lássa mindenki.';
			break;

		case 22:
			//user
			$kod.='<b>Elérhetőség</b>
 <br><br>Itt megadhatod mások számára elérhetőségeid. Ezt a mezőt célszerű óvatosan kezelni, személyes adatokat csak megfelelő kör számára szabad engedélyezni.
 <br><br>Itt különös szerepe van a nyilvánosság beállításának!';
			break;

		case 23:
			//user
			$kod.='<b>Foglalkozás</b>
 <br><br>Itt megadhatod foglalkozásod, ami adatlapodon jelenik meg, illetve adott esetben kereshető is.
 <br><br>A nyilvánossága ennek a mezőnek is beállítható.';
			break;

		case 24:
			//user
			$kod.='<b>Születésnap</b>
 <br><br>A születési dátumot megadva a beállított nyilvánosság figyelembevételével a rendszer automatikusan kezeli azt.
 
 <br><br><b>Névnap</b>
 <br><br>A születésnaphoz hasonlóan kezelődik.';
			break;

		case 25:
			//user
			$kod.='<b>Családi állapot</b>
 <br><br>Itt állíthatod be családi állapotod, valamint meghatározható a nyilvánossága is.';
			break;

		case 26:
			//user
			$kod.='<b>Lakhely</b>
 <br><br>Itt beállítható az ország és a település. Amennyiben a listában nem lenne a keresett ország, úgy válaszd az "egyéb" megjelölést.
 <br><br>A településnél figyelj a pontos beírásra, mindig a települést és ne a településen belüli résznevet (pl. valamilyen telep, stb.) írd! A beírt településnevet a rendszer használhatja, szolgáltatások kapcsolódhatnak hozzá, melyeket hibás beírás esetén nem tudsz használni.
 <br><br>Budapest esetében a kerületeket a következő formában kell mögé írni, pl.: <u>Budapest XVI. kerület</u>';
			break;

		case 27:
			//user
			$kod.='<b>Vallás</b>
 <br><br>Amennyiben teheted, kérlek add meg vallásod, s ha nem szeretnéd, hogy mások lássák, akkor a nyilvánosságát tiltsd le. Ezt az adatot többek között statisztikához használjuk fel, de ha a nyilvánosságát engedélyezed, akkor adatlapodon is megjelenik.';
			break;

		case 28:
			//user
			$kod.='<b>Internetes elérhetőség</b>
 <br><br>Itt a skype és messenger azonosítók adhatóak meg, hogy adott esetben online fel lehessen venni a kapcsolatot egymással.
 <br><br>A programok segítségével akár élő hanggal is lehet beszélgetni az interneten, így költségkímélő megoldás a telefonhoz képest.';
			break;

		case 29:
			//hírek admin - hírek űrlap
			$kod.='<b>Megjegyzés</b> (a szerkesztéssel kapcsolatban)
 <br><br>Ide akkor kell beírni, ha a feltöltésnél van valami olyan körülmény, amiről fontos, hogy minden rögzítő, aki megnyithatja az űrlapot módosításra, tudjon.
 <br><br>Pl. "még ne engedélyezd, most egyeztetek" vagy bármi hasonló';
			break;

		case 41:
			//miserend - miseűrlap
			$kod.='<b>Kiegészítő infók</b>
 <br><br>Ide jön minden olyan kapcsolódó információ, ami nem egy konkrét miséhez tartozik, hanem rendszeres esemény. Pl. a minden héten valamelyik este szentségimádás, vagy minden hónap első hétfője családos hittan, vagy bármi hasonló, ami fontos lehet, de nem a templomhoz tartozik (pl. védőszent, ünnep), hanem a miserendet kiegészítő rendszeres információ.';
			break;

		case 46:
			//miserend adatlap
			$kod.='<h4>Nyelvek</h4>
A szentmise nyelvének azonosítója esetleg egy periódus megjelölésével. Több érték esetén vesszővel elválasztva. Például: <i>sk-1,grps</i> = minden hónap utolsó hetében szlovák nyelvű, minden páros héten görög (egyéb esetekben magyar)<br/><br/>Lehetséges nyelvek:<ul>';
		foreach (unserialize(LANGUAGES) as $name => $attr) {
		$kod .= "<li> ".$attr['abbrev']." = ".$attr['name']."</li>";
	}
$kod .= '<li>további nyelvek esetén az internetes 2 betűs végződés az irányadó!</li></ul><p><i>Ha egy magyaroszági misézőhelynél nincs megadva a nyelv, azt magyarnak tekintjük.</i></p>';

	$kod.='Lehetséges periódusok:
		<ul>';

foreach (unserialize(PERIODS) as $name => $attr) {
		$kod .= "<li> ".$attr['abbrev']." = ".$attr['description'].$attr['name']." héten</li>";
	}
	$kod .= '</ul>Ha maga a mise periódusa meg van advan, akkor nem szükséges itt is megadni a periódust. Vagyis ha nincs itt periódus érték megadva, akkor a mise periódusa érvényes.';

			break;

		case 47:
			//miserend adatlap
			$kod.='<h4>Tulajdonságok</h4>
A szentmise tulajdonságának rövidítése esetleg egy periódus megjelölésével. Több érték esetén vesszővel elválasztva. Például: <i>ifi,ige3</i> = mindig ifjúsági/egyetemista mise, de a hónap harmadik hetében csak igeliturgia.<br/><br/>

Lehetséges tulajdonságok:
	<ul>';

	foreach (unserialize(ATTRIBUTES) as $name => $attr) {
		$kod .= "<li> ".$attr['abbrev']." = ".$attr['name']."</li>";
	}
	$kod .='</ul><p><i>Ha nincs megadva ezzel ellenkező tulajdonság, akkor a római katolikus misézőhely eseménye „római katolikus szentmise”, míg egy görögkatolikus hely alapérelmezett eseménye „görögkatolikus isteni liturgia”.</i>';


	$kod.='</p>Lehetséges periódusok:
		<ul>';

foreach (unserialize(PERIODS) as $name => $attr) {
		$kod .= "<li> ".$attr['abbrev']." = ".$attr['description'].$attr['name']." héten</li>";
	}
	$kod .= '</ul>Ha maga a mise periódusa meg van advan, akkor nem szükséges itt is megadni a periódust. Vagyis ha nincs itt periódus érték megadva, akkor a mise periódusa érvényes.';
			break;

		case 48:
			//miserend adatlap
			$kod.='A <b>megjegyzés</b> rovatba minden további részletet tüntessünk fel, amit nem tudtunk a tulajdonságokhoz feljegyezni.';
			break;

		case 49:
			//miserend adatlap
			$kod.='<h4>Periódus / Különleges mierend név</h4>Minden periódusnak, más néven időszaknak, valamint minden különleges miserendnek kell legyen egy egyedi neve. Például: <i>téli miserend</i>, <i>ádventi időszak</i> vagy <i>karácsony napja</i>. Gépelés közben megjelennek a már használt nevek is. Ha egy már használt nevet választunk, akkor az időszak kezdetét és végét átmásolja onnan, így nem kell újra beállítani.';
			break;

		case 50:
			//miserend adatlap
			$kod.='<h4>Periódus határok</h4>Minden periódusnak, más néven időszaknak, be kell állítani a kezdetét és a végét. Több féleképpen adhatjuk meg a kezdő napot és a lezáró napot.<ul><li>Megadhatjuk egy konkért dátum nélküli kifejezéssel. Például: <i>első tanítási nap</i> vagy <i>Krisztus Király vasárnapja</i>. Gépelés közben megjelennek a választható kezdeti időpontok. Ha szükség lenne olyanra, ami még nincs, akkor írj nekem: eleklaszlosj@gmail.com. Azért nagyon praktikus ilyen kifejezéssel megadni egy időszak határát, mert így nem kell minden évben átírni. A miserend.hu tudja, hogy melyik évben mikor van pl. Húsvét.</li><li>Ha minden évben ugyan azon a naptári napon van az időszak váltása, akkor megadhatunk egy dátumot is. Például: <i>12-25</i>. Ilyenkor minden évben, pont ez lesz a forduló nap.</li><li>Nagy ritkán előfordulhat, hogy egy periódus/időszak csak egy adott évben létezik és más években nincsen rá szükség. Ilyen esetben meg lehet adni teljes tádummal a határt. Például: <i>2016-03-12</i>. Fontos, hogy így az adott időszaki miserend nem fog megjelenni más évben.</li></ul>Fontos, hogy nem szabad két különböző időszaknak pontosan ugyan azokat a határokat megadni, mert akkor nincs ami megkülönböztesse azokat egymástól.';
			break;

		default:
			$kod.= 'Nincs ilyen segítség.';
			break;
	}
	$kod .= "</div>";

		//$kod.="<div class='alap help'>$leiras</div>";
	
}

$kod.="<br/><div align=center><a href=javascript:close(); class=link><img src=img/bezar.gif border=0 aling=absmiddle> Bezár</a></div>";

echo $kod;


?>
</body></html>