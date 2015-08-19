<?php

function regisztracio_index() {
	global $linkveg,$m_id,$_GET,$db_name;

	$fm=$_GET['fm'];
	if($fm == 11) {
		$tartalom.= <<<EOD
		<span class=alcim>Regisztráció</span>
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
		$tartalom .= "<center><span class='cim'><a href=?m_id=$m_id&m_op=add class=cim>Elolvastam, elfogadom, regisztrálok - tovább</a></span></center><br/><br/>";


	}
	else $tartalom .= "404 - üres lap";

	$adatT[2]=$tartalom;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);
	
	return $kod;
}

function regisztracio_atvett() {
	global $user,$m_id,$m_op;

	$tartalom.="\n<span class=alcim>Első belépés</span><br><br><span class=alap><b>Kedves ".$user->login."!<br>Szeretettel köszöntünk honlapunkon!</b><br><br>";
	$tartalom.="\nA Virtuális Plébánia korábbi adatbázisából megtartottuk a felhasználóneved, emailcímed és ha beírtad, akkor a neved. Kérünk, hogy most az első belépés alkalmával nézd át új portálunk regisztrációs részét, ellenőrizd a megtartott adatokat, s ha jónak látod, megadhatsz további adatokat is, melyek megjelenését is többféleképpen beállíthatod.<br><br><b>Köszönjük, hogy időt szánsz rá!</b></span><br><br>";

	$tartalom.="<a href=?m_id=$m_id&m_op=add class=kismenulink>Tovább</a><br>";
	$adatT[2]=$tartalom;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);

	return $kod;
}

function regisztracio_add() {
	global $sessid,$m_id,$db_name,$user;

	$optionT=array('0'=>'bárki','i'=>'ismerős','b'=>'barát','n'=>'senki');

$urlap.= <<<EOD
<!--<script>
 $(function() {
    $( "#accordion" ).accordion({
      collapsible: true
    });
  });
  </script>
  <div id="accordion" class="alapj">
  <span class='kiscim'>Kötelező adatok:</span>
  <div>
    <p>
    Mauris mauris ante, blandit et, ultrices a, suscipit eget, quam. Integer
    ut neque. Vivamus nisi metus, molestie vel, gravida in, condimentum sit
    amet, nunc. Nam a nibh. Donec suscipit eros. Nam mi. Proin viverra leo ut
    odio. Curabitur malesuada. Vestibulum a velit eu ante scelerisque vulputate.
    </p>
  </div>
  <h3>Önkéntesség</h3>
  <div>
  </div>
  <h3>Egyéb személyes adatok</h3>
  <div>
  </div>
</div>-->
EOD;
	$urlap.="\n<form method=post>";
	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sessid value=$sessid>";
	$urlap.="\n<input type=hidden name=m_op value=adding>";
	$urlap.="\n<table cellpadding=8 cellspacnig=1 bgcolor=#efefef>";

//Bejelentkezési név	
	$urlap.="<tr><td valign=top bgcolor=#FFFFFF>";
	if($user->loggedin) {
		$urlap.="\n<span class=kiscim>Bejelentkezési név: </span><br><span class=alap>(Nem módosítható!)</span><br><input type=text name=ulogin readonly value='".$user->login."' class=urlap size=20 maxlength=20>";

		$query="select email,becenev,nev,kontakt,szuldatum,nevnap,msn,skype,nem,csaladiallapot,foglalkozas,magamrol,vallas,orszag,varos,nyilvanos, volunteer from user where uid='".$user->uid."' and ok='i'";
		$lekerdez=mysql_db_query($db_name,$query);
		list($email,$becenev,$nev,$kontakt,$szuldatum,$nevnap,$msn,$skype,$nem,$csaladiallapot,$foglalkozas,$magamrol,$vallas,$orszag,$varos,$nyilvanos,$volunteer)=mysql_fetch_row($lekerdez);

		$urlap.="\n<br><br><span class=kiscim>Jelszó (jelenlegi): </span><br><span class=alap>(FONTOS! Minden módosításhoz meg kell adni!)</span><br><input type=password name=oldjelszo class=urlap size=20 maxlength=20>";
		
		$urlap.="\n<br><br><span class=kiscim>Új jelszó: </span><br><span class=alap>(Csak a jelenlegi módosítása esetén.)</span><br><input type=password name=ujjelszo1 class=urlap size=20 maxlength=20>";
		$urlap.="\n<br><br><span class=kiscim>Új jelszó mégegyszer: </span><br><input type=password name=ujjelszo2 class=urlap size=20 maxlength=20>";
	}
	else {
		$urlap.="\n<span class=kiscim>Bejelentkezési név: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=18',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><input type=text name=ulogin value='$ulogin' class=urlap size=20 maxlength=20";
		$urlap.="><br><span class=alap>(Lehetőség szerint ékezet és speciális karakterek nélkül, maximum 20 betű. Szóköz, idézőjel és aposztróf NEM lehet benne! Ez a név azonosít, ezzel tudsz majd belépni, de alább lehetőség van külön becenév megadására is.)</span>";
	}
	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Bárki láthatja!</td></tr>";

//Becenév
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Becenév, megszólítás: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=17',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><input type=text name=becenev class=urlap maxlength=100 size=40 value='$becenev'><br><span class=alap>(Ide keresztnevet, vagy becenevet célszerű írni. Alapvetően ezen a néven jelensz meg oldalunkon, az azonosításhoz mellette kicsiben jelezzük a bejelentkezési neved is.)</span>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Bárki láthatja!</td></tr>";


//Email
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Email cím: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=19',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><input type=text name=email class=urlap maxlength=100 size=40 value='$email'><br><span class=alap>(Erre a címre küldjük ki a jelszót. A regisztrációhoz szükséges egy valós emailcím! Elküldés előtt kérjük ellenőrizd!)</span>";

//Email2
	$urlap.="\n<br><br><span class=kiscim>Email cím mégegyszer: </span><br><input type=text name=email2 class=urlap maxlength=100 size=40 value='$email'>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[email]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"email-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";

//Név
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Név: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=20',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><input type=text name=nev class=urlap maxlength=100 size=40 value='$nev'>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[nev]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"nev-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";

//Nem
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Nem: </span><br><input type=radio name=nem class=urlap value=f";
	if($nem=='f') $urlap.=" checked";
	$urlap.="><span class=alap>férfi</span> <input type=radio name=nem value=n class=urlap";
	if($nem=='n') $urlap.=" checked";
	$urlap.="><span class=alap>nő</span>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[nem]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"nev-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";

//Bemutatkozás	
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Rövid bemutatkozás: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=21',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><span class=alap>(amennyiben szívesen megosztanál valamit másokkal is)</span><br><textarea name=magamrol class=urlap cols=60 rows=8>$magamrol</textarea>";	

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[magamrol]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"magamrol-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";

//Nem
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Önkéntesség: </span><br><input type=checkbox name=volunteer class=urlap value=1";
	if($volunteer == 1) $urlap.=" checked";
	$urlap.="><span class=alap>Vállalom, hogy hetente hét régen frissült templom miserendjét megpróbálom megtudakolni és megosztani a többiekkel.</span>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Nyilvános</span></td></tr>";

//Lakhely
	if(empty($orszag)) $orszag='Magyarország';
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Lakhely: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=26',200,430);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><span class=alap>- ország: </span><select name=orszag class=urlap>";	
	$query="select nev from orszagok where ok='i' order by nev";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($onev)=mysql_fetch_row($lekerdez)) {
		$urlap.="<option value='$onev'";
		if($onev==$orszag) $urlap.=' selected';
		$urlap.=">$onev</option>";
	}
	$urlap.="</select><br><img src=img/space.gif width=5 height=8><br><span class=alap>- település: </span><input type=text name=varos value='$varos' class=urlap size=40 maxlength=50><br><span class=alap>(Kérlek pontosan és nagy kezdőbetűvel írd be a települést!)</span>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[orszag]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"kontakt-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select><br><img src=img/space.gif width=5 height=8><br><select name='nyilvanosT[varos]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"kontakt-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";

//Kontakt
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Elérhetőség: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=22',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><span class=alap>(ha valamely elérhetőséged szeretnéd megadni)</span><br><textarea name=kontakt class=urlap cols=60 rows=4>$kontakt</textarea>";	

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[kontakt]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"kontakt-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";

//MSN, Skype
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Internetes elérhetőség: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=28',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><span class=alap>- Skype (<a href=http://www.skype.hu/index2.php target=_blank class=link><small>www.skype.hu</small></a>) </span><input type=text name=skype class=urlap maxlength=50 size=20 value='$skype'>";
	$urlap.="<br><img src=img/space.gif width=5 height=7><br><span class=alap>- MSN Messenger (<a href=http://messenger.msn.com target=_blank class=link><small>messenger.msn.com</small></a>) </span><input type=text name=msn class=urlap maxlength=50 size=20 value='$msn'>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[skype]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"skype-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select><br><img src=img/space.gif width=5 height=7><br><select name='nyilvanosT[msn]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"msn-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";

//Foglalkozás
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Foglalkozás: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=23',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><input type=text name=foglalkozas class=urlap maxlength=100 size=40 value='$foglalkozas'>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[foglalkozas]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"foglalkozas-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";

//Születésnap
	if(empty($szuldatum)) $szuldatum='0000-00-00';
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Születésnap: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=24',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><input type=text name=szuldatum class=urlap maxlength=10 size=10 value='$szuldatum'><br><span class=alap>(Fontos a formátum: év-hónap-nap => 0000-00-00)</span>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[szuldatum]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"szuldatum-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";

//Névnap
	if(empty($nevnap)) $nevnap='00-00';
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Névnap: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=24',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><input type=text name=nevnap class=urlap maxlength=5 size=5 value='$nevnap'><br><span class=alap>(Fontos a formátum: hónap-nap => 00-00)</span>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[nevnap]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"nevnap-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";

//Családi állapot
	$csaladiallapotT=array('titok','egyedülálló', 'kapcsolatban', 'házas', 'elvált', 'özvegy', 'pap/szerzetes');
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Családi állapot: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=25',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><select name=csaladiallapot class=urlap>";
	foreach($csaladiallapotT as $ertek) {
		$urlap.="<option value=$ertek";
		if($csaladiallapot==$ertek) $urlap.=' selected';
		$urlap.=">$ertek</option>";
	}
	$urlap.="</select>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[csaladiallapot]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"csaladiallapot-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";

//Vallás
	$csaladiallapotT=array('titok','római katolikus', 'görög katolikus', 'evangélikus', 'református', 'baptista', 'izraelita', 'görög ortodox','pünkösdi','szabadkeresztény','Jehova tanúja','Hit gyülekezete','egyéb');
	$urlap.="\n<tr><td valign=top bgcolor=#FFFFFF><span class=kiscim>Vallás: </span><a href=\"javascript:OpenNewWindow('sugo.php?id=27',200,350);\"><img src=img/help.png border=0 title='Súgó' align=top></a><br><select name=vallas class=urlap>";
	foreach($csaladiallapotT as $ertek) {
		$urlap.="<option value='$ertek'";
		if($vallas==$ertek) $urlap.=' selected';
		$urlap.=">$ertek</option>";
	}
	$urlap.="</select>";

	$urlap.="\n</td><td valign=top bgcolor=#FFFFFF><span class=alap>Láthatja: </span><select name='nyilvanosT[vallas]' class=urlap>";
	foreach($optionT as $x=>$y) {
		$urlap.="<option value=$x";
		if(strstr($nyilvanos,"vallas-$x")) $urlap.=' selected';
		$urlap.=">$y</option>";
	}
	$urlap.="</select></td></tr>";
	
	$urlap.="\n</table>";

	$urlap.='<br><br><input type=submit value=Mehet class=urlap></form>';

	if($user->loggedin) $tartalom="<span class=alcim>Adatok módosítása</span><br><br>".$urlap;
	else $tartalom="<span class=alcim>Regisztráció</span><br><br>".$urlap;

	$adatT[2]=$tartalom;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);

	return $kod;
}

function regisztracio_adding() {
	global $_POST,$_FILES,$_SERVER,$db_name,$user,$sid;

	$ip=$_SERVER['REMOTE_ADDR'];
    $host = gethostbyaddr($ip);
	
	$nev=$_POST['nev'];
	$becenev=$_POST['becenev'];
	$email=$_POST['email'];
	$email2=$_POST['email2'];
	$oldjelszo=$_POST['oldjelszo'];
	$ujjelszo1=$_POST['ujjelszo1'];
	$ujjelszo2=$_POST['ujjelszo2'];
	$login=$_POST['ulogin'];
	$kontakt=$_POST['kontakt'];
	$szuldatum=$_POST['szuldatum'];
	$nevnap=$_POST['nevnap'];
	$nem=$_POST['nem'];
	if(empty($nem)) $nem=0;
	$magamrol=$_POST['magamrol'];
	$foglalkozas=$_POST['foglalkozas'];
	$vallas=$_POST['vallas'];
	$orszag=$_POST['orszag'];
	$varos=$_POST['varos'];
	$skype=$_POST['skype'];
	$msn=$_POST['msn'];
	$csaladiallapot=$_POST['csaladiallapot'];
	$nyilvanosT=$_POST['nyilvanosT'];
	$volunteer=$_POST['volunteer'];

	if(is_array($nyilvanosT)) {
		foreach($nyilvanosT as $kulcs=>$ertek) {
			$nyilvanosT2[]="$kulcs-$ertek";
		}
	}
	if(is_array($nyilvanosT2)) $nyilvanos=implode('*',$nyilvanosT2);

	$most=date('Y-m-d H:i:s');
	$hiba=false;

	if($user->loggedin) {
		//módosítás
		$query="select jelszo from user where uid='".$use->uid."'";
		$lekerdez=mysql_db_query($db_name,$query);
		list($jelszo)=mysql_fetch_row($lekerdez);

		if($ujjelszo1!=$ujjelszo2) {
			$hiba=true;
			$hibauzenet.="<span class=hiba>HIBA! A megadott két jelszó nem egyezik!</span><br>";
		}
		$oldjelszo=base64_encode($oldjelszo);
		if($oldjelszo!=$jelszo) {
			$hiba=true;
			$hibauzenet.="<span class=hiba>HIBA! A megadott jelszó hibás!</span><br>";
		}
		if(!$hiba) {
			if(!empty($ujjelszo1)) $ujjelszo=", jelszo='".base64_encode($ujjelszo1)."'";
			$query="update user set becenev='$becenev', nev='$nev', email='$email', kontakt='$kontakt', szuldatum='$szuldatum', nevnap='$nevnap', skype='$skype', msn='$msn', nem='$nem', csaladiallapot='$csaladiallapot', foglalkozas='$foglalkozas', magamrol='$magamrol', vallas='$vallas', orszag='$orszag', varos='$varos', nyilvanos='$nyilvanos', regip='$ip ($host)', atvett='n', volunteer='$volunteer' $ujjelszo where uid='".$user->uid."'";
			if(!mysql_db_query($db_name,$query)) echo "HIBA!<br>$query<br>".mysql_error();

			$tartalom="<span class=alcim>Adatok módosítása</span><br><br><span class=alap>Az adatok módosítása sikerrel járt.</span>";
		}
		else {
			$tartalom="<span class=alcim>Adatok módosítása</span><br><br>$hibauzenet<br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";
		}
	}
	else {
		//Új regisztráció

		if(empty($login)) {
			$hiba=true;
			$hibauzenet.="<span class=hiba>HIBA! Nem lett megadva felhasználónév!</span><br>";
		}
		if(empty($email)) {
			$hiba=true;
			$hibauzenet.="<span class=hiba>HIBA! Nem lett megadva emailcím!</span><br>";
		}
		if($email!=$email2) {
			$hiba=true;
			$hibauzenet.="<span class=hiba>HIBA! A beírt emailcímek nem egyeznek!</span><br>";
		}
		//Login ellenőrzése
		$login=str_replace(' ','',$login); //szóköz törlése, ha lenne benne
		$login=str_replace("&nbsp;",'',$login); //szóköz törlése, ha lenne benne
		$login=str_replace('"','',$login); //idézőjel törlése, ha lenne benne
		$login=str_replace("'",'',$login); //aposztróf törlése, ha lenne benne
		$login=strip_tags($login); //mindenféle html formázást is törlünk
		$query="select uid from user where login='$login'";
		$lekerdez=mysql_db_query($db_name,$query);
		if(mysql_num_rows($lekerdez)>0) {
			$hiba=true;
			$hibauzenet.="<span class=hiba>HIBA! Ez a bejelentkezési név már foglalt, kérjük válassz másikat!</span><br>";
		}

		if(!$hiba) {
			//Jelszó generálás
			$szam1=mt_rand(0,99);
			$jelszo1 = str_shuffle($login).$szam1;
			$jelszo=base64_encode($jelszo1);

			$query="insert user set nev='$nev', email='$email', kontakt='$kontakt', jelszo='$jelszo', ok='i', jogok='$jogok', login='$login', letrehozta='".$user->login."', regdatum='$most', volunteer='$volunteer'";
			if(!mysql_db_query($db_name,$query)) echo "HIBA!<br>$query<br>".mysql_error();

			//email küldése
			$targy='Regisztráció - Virtuális Plébánia Portál';
			$szoveg="Köszöntünk a Virtuális Plébánia Portál felhasználói között!";
			$szoveg.="\n\nA belépéshez szükséges jelszó: $jelszo1";
			$szoveg.="\nA belépést követően a BEÁLLÍTÁSOK menüben lehet a jelszót megváltoztatni.";
			$szoveg.="\n\nVPP \nwww.plebania.net";
			mail($email,$targy,$szoveg,"From: info@plebania.net");
		
			$tartalom="<span class=alcim>Regisztráció</span><br><br><span class=alap><b>Isten hozott!</b><br><br>A belépéshez szükséges kódot elküldtük a megadott emailcímre ($email), ami a belépést követően megváltoztatható. <br><b>Ha pár órán belül nem érkezne meg, valószínűleg hibás emailcímet adtál meg.</b>";
		}
		else {
			$tartalom="<span class=alcim>Regisztráció</span><br><br>$hibauzenet<br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";
		}
	}

	$adatT[2]=$tartalom;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);


	return $kod;
}

function regisztracio_jelszo() {
	global $db_name,$m_id;
	
	$cim='<span class=alcim>Jelszó emlékeztető</span><br><br>';
	$szoveg='<span class=alap>Az alábbi két adat közül legalább az egyik kitöltése alapján a rendszer megpróbál azonosítani és elküldi a megadott (regisztrált!) email címre a jelszót.</span><br><br>';
	$szoveg.="\n<form method=post><input type=hidden name=m_op value=jelszokuld><input type=hidden name=m_id value=$m_id><span class=alap>Felhasználónév: </span> <input type=text name=lnev size=18 class=urlap><br><span class=alap>Emailcím: </span> <input type=text name=mail size=25 class=urlap><br><br><input type=submit value='Kérem a jelszót'></form>";

	$adatT[2]=$cim.$szoveg;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);

	return $kod;
}


function regisztracio_jelszokuld() {
	global $db_name,$_POST;

	$lnev=$_POST['lnev'];
	$mail=$_POST['mail'];

	$cim='<span class=alcim>Jelszó emlékeztető</span><br><br>';
	
	if(!empty($lnev)) $feltetelT[]="login='$lnev'";
	if(!empty($mail)) $feltetelT[]="email='$mail'";
	if(is_array($feltetelT)) $feltetel=implode(' and ',$feltetelT);

	if(!empty($feltetel)) {
		$query="select login,jelszo,email from user where $feltetel";
		$lekerdez=mysql_db_query($db_name,$query);
		if(mysql_num_rows($lekerdez)>0) {
			list($loginnev,$jelszo,$email)=mysql_fetch_row($lekerdez);
			$jelszokiir=base64_decode($jelszo);
			$targy="Jelszó emlékeztető - Virtuális Plébánia Portál";
			$txt="Kedves $loginnev";
			$txt.="\n\nKérésedre küldjük a bejelentkezéshez szükséges jelszót:";
			$txt.="\n$jelszokiir";
			$txt.="\n\nVPP \nhttp://www.plebania.net";
			
			mail($email,$targy,$txt,"From: info@plebania.net");
			
			$szoveg="<span class=alap>A jelszót elküldtük a regisztrált emailcímre</span>";
		}
		else {
			$szoveg='<span class=alap>A megadott adatok alapján nem találtunk felhasználót.</span><br><br><a href=javascript:history.go(-1); class=link>Vissza</a>';
		}
	}
	else {
		$szoveg='<span class=alap>Nem lett kitöltve adat!</span><br><br><a href=javascript:history.go(-1); class=link>Vissza</a>';
	}

	$adatT[2]=$cim.$szoveg;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);


	return $kod;
}

switch($m_op) {
    case 'index':
        $tartalom=regisztracio_index();
        break;

	case 'atvett':
		$tartalom=regisztracio_atvett();
		break;

	case 'add':
		$uid=$_GET['uid'];
        $tartalom=regisztracio_add();
        break;

    case 'adding':
        $tartalom=regisztracio_adding();
        break;

	case 'jelszo':
		$tartalom=regisztracio_jelszo();
		break;

	case 'jelszokuld':
		$tartalom=regisztracio_jelszokuld();
		break;

}

?>