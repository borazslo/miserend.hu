<?

function loginurlap($belephiba) {
    global $_POST,$design_url,$sid,$linkveg;

	$bal="<span class=alcim>Felhasználói oldal</span>";
	$bal.="<br><br><span class=alap>Ezen oldal megtekintéséhez kérlek lépj be!<br>Ha még nincs felhasználóneved, <a href=?m_id=28&fm=11$linkveg class=felsomenulink>itt</a> tudsz regisztrálni egyet. </span>";
	
	$adatT[2]=$bal;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);	
	
    return $kod;
}
///////////////////////////////////////////////////////////////////////////////////////////////////

function keret($helyzet) {
	global $db_name,$m_id,$belepve,$modul_url,$lang,$elso,$szavazott,$_SERVER,$design_url,$design,$fooldal_id;
	if(!isset($design)) $design='alap';
	//$ip=$_SERVER['REMOTE_ADDR'];

	if(!$belepve) $feltetel=" and zart=0";
	$nyelv=$lang;
	if(empty($nyelv)) $nyelv='hu';

	$tmpl_file = $design_url.'/hasabdoboz.htm';
	$tablabgT=array('#D1DDE9','#F4F2F5');
	$fejlecbgT=array('#053D78','#8D317C');
	
	if($helyzet>0) {
		$query="select modul_id,fajlnev from oldalkeret where helyzet='$helyzet' and fooldal_id='$fooldal_id' $feltetel order by sorrend";
		if(!$lekerdez=mysql_query($query)) echo "HIBA!<br>$query<br>".mysql_error();
		while(list($mid,$fajl)=mysql_fetch_row($lekerdez)) {				
			$include=$modul_url.'/'.$fajl.'_menu.php';
			$op=$helyzet;
			include_once($include);
			if(!empty($hmenuT[0])) {
				//Csak, ha van megadva cím
				$a=$helyzet;
				if($a>1) $a=0;

				$hasabcim=$hmenuT[0];
				$hasabtartalom=$hmenuT[1];
				$tablabg=$tablabgT[$a];
				$fejlecbg=$fejlecbgT[$a];
				$a++;
	
				$thefile = implode("", file($tmpl_file));
				$thefile = addslashes($thefile);
				$thefile = "\$r_file=\"".$thefile."\";";
			    eval($thefile);
    
				$keret .= "\n".$r_file;
				$hmenuT='';
			}
			else {
				$keret .= "\n".$hmenuT[1];
				$hmenuT='';
			}
			$keret.="\n<img src=img/space.gif width=5 height=4><br>";

		}
	}
	else {
		//Ha a $helyzet=0, akkor admin menü
		$op=$helyzet;
		include_once("$modul_url/admin_menu.php");
		if(!empty($hmenuT[0])) {
			//Csak, ha van megadva cím
			$hasabcim=$hmenuT[0];
			$hasabtartalom=$hmenuT[1];
			$tablabg="#ECE5C8";
			$fejlecbg="#F5CC4C";

			$thefile = implode("", file($tmpl_file));
			$thefile = addslashes($thefile);
			$thefile = "\$r_file=\"".$thefile."\";";
		    eval($thefile);
   
			$keret .= "\n".$r_file;
			$hmenuT='';
		}
		$keret.="\n<img src=img/space.gif width=5 height=4><br>";

		include_once("$modul_url/chat_menu.php");
		if(!empty($hmenuT[0])) {
			//Csak, ha van megadva cím
			$hasabcim=$hmenuT[0];
			$hasabtartalom=$hmenuT[1];
			$tablabg="#ECE5C8";
			$fejlecbg="#F5CC4C";

			$thefile = implode("", file($tmpl_file));
			$thefile = addslashes($thefile);
			$thefile = "\$r_file=\"".$thefile."\";";
		    eval($thefile);
   
			$keret .= "\n".$r_file;
			$hmenuT='';
		}
		$keret.="\n<img src=img/space.gif width=5 height=4><br>";
	}

	Return $keret;
}

function formazo($adatT,$tipus) {
	global $design_url,$szin,$design;

	if(!isset($design)) $design='alap';

	$cim=$adatT[0];
	$cimlink=$adatT[1];
	$tartalom=$adatT[2];
	$tartalom2=$adatT[3]; //híreknél 2. hasáb
	$tovabb=$adatT[4]; //híreknél "cikk bõvebben"
	$tovabblink=$adatT[5]; //általában a $cimlink
		
    $tmpl_file = $design_url.'/'.$tipus.'.htm';

    $thefile = implode("", file($tmpl_file));
    $thefile = addslashes($thefile);
    $thefile = "\$r_file=\"".$thefile."\";";
    eval($thefile);
    
    return $kod = $r_file;
}

function langmenu() {
	global $lang,$sessid;

	if($lang=='en') {
		$enlink='<span class=szurkelink>english</span>';
	}
	elseif($lang=='de') {
		$delink='<span class=szurkelink>deutsch</span>';
	}
	else {
		$hulink='<span class=szurkelink>magyar</span>';
	}

	if(empty($enlink)) $enlink="<a href='?lang=en&sessid=$sessid' class='kismenulink'>english</a>";
	if(empty($delink)) $delink="<a href='?lang=de&sessid=$sessid' class='kismenulink'>deutsch</a>";
	if(empty($hulink)) $hulink="<a href='?lang=hu&sessid=$sessid' class='kismenulink'>magyar</a>";

	$nyelvlinkT=array($enlink,$delink,$hulink);

	Return $nyelvlinkT;	
}

function fomenu($hol) {
	global $db_name,$fooldal_id,$m_id,$linkveg,$lang,$design_url,$sid;

	$kod.='<table width="95%" height="34" border="0" cellspacing="0" cellpadding="0"><tr>';
	$terkoz['felso']="<img src=img/space.gif width=5 height=4><br>";
	$terkoz['also']="<img src=img/space.gif width=5 height=14><br>";

	$query="select id,menucim,domain from fooldal where ok='i' and menucim!='' order by menusorrend";
	$lekerdez=mysql_query($query);
	while(list($id,$menucim,$domain)=mysql_fetch_row($lekerdez)) {
		$link="?fooldal_id=$id&sid=$sid";
		if($id==$fooldal_id) $aktiv='1';
		else $aktiv='';
		$kod.="<td background='$design_url/img/".$hol."menuhatter$aktiv.jpg' align=center valign=top>$terkoz[$hol]<a href='$link' title='$domain' class='".$hol."menulink'>$menucim</a></td>";
	}
	$kod.='</tr></table>';
 
    return $kod;
}

function design() {
    global $design_url,$db_name,$tartalom,$m_oldalsablon,$balkeret,$jobbkeret,$onload,$sid,$linkveg,$u_id,$u_login,$u_jogok,$belepve,$loginhiba,$script,$meta,$titlekieg;

	if(!isset($design)) $design='alap';
    $title='VPP - országos miserend'.$titlekieg;
	$top=alapnyelv('top');
	
	$nyelvlinkT=langmenu();
	$enlink=$nyelvlinkT[0];
	$delink=$nyelvlinkT[1];
	$hulink=$nyelvlinkT[2];

	if(!$belepve) {
		if(empty($onload)) $onload='onload="fokusz();"';
		else $onload="onload=\"$onload fokusz();\"";
	}
	elseif(!empty($onload)) {
		$onload="onload=\"$onload;\"";
	}

//Scriptek////////////////////////////////////
	$script.="\n".'<script language="JavaScript" type="text/javascript">
	<!--
	function fokusz() {
      document.loginurlap.login.focus();
	}
  
	function OpenPrintWindow(url, x, y) {
      var options = "toolbar=no,menubar=yes,scrollbars=yes,resizable=yes,width=" + x + ",height=" + y;
      msgWindow=window.open(url,"", options);
	}
  
	function OpenNewWindow(url, x, y) {
      var options = "toolbar=no,menubar=no,scrollbars=no,resizable=yes,width=" + x + ",height=" + y;
      msgWindow=window.open(url,"", options);
	}

	function OpenScrollWindow(url, x, y) {
      var options = "toolbar=no,menubar=no,scrollbars=yes,resizable=yes,width=" + x + ",height=" + y;
      msgWindow=window.open(url,"", options);
	}

	function UnCryptMailto(s) {
		var n=0;
		var r="";
		for(var i=0;i<s.length;i++) { 
			n=s.charCodeAt(i); 
			if (n>=8364) {n = 128;}
			r += String.fromCharCode(n-(2)); 
		}
		return r;
	}

	function EnCryptMailto(s) {
		var n=0;
		var r="";
		for(var i=0;i<s.length;i++) { 
			n=s.charCodeAt(i); 
			if (n>=8364) {n = 128;}
			r += String.fromCharCode(n+(2)); 
		}
		return r;
	}
	
	function linkTo_UnCryptMailto(s)	{
		location.href=UnCryptMailto(s);
	}

	// -->
	</script>';
	
	$script.="\n".'<script language="JavaScript" type="text/javascript">
	/**
	* A Google Analytics kimenõ linkjeit követõ funkció.
	* A függvény argumentuma egy érvényes URL string, és a függvény ezt a stringet használja
	* az esemény címkéjeként.
	*/
	var trackOutboundLink = function(url) {
	ga(\'send\', \'event\', \'outbound\', \'click\', url, {\'hitCallback\':
		function () {
		document.location = url;
		}
	});
	}
	</script>';
////////////////////////////////////////////

	$emaillink_lablec="<A HREF=\"javascript:linkTo_UnCryptMailto('ocknvq%3CkphqBokugtgpf0jw');\" class=emllink>info<img src=img/kukaclent.gif align=absmiddle border=0>miserend.hu</a>";
	

//Impresszum link
	$impkiir=alapnyelv('Impresszum');
	$impfm=alapnyelv('impfm');
	$impresszumlink="<a href=?m_id=17&fm=12$linkveg class=implink>$impkiir</a>";

	$keszitok="<a href=http://www.b-gs.hu class=implink title='BGS artPart' target=_blank>design</a><br><a href=http://www.florka.hu class=implink title='Florka Kft.' target=_blank>programozás</a>";

//Névnap
	$ho=date('n');
	$honap=alapnyelv("ho$ho");
	$nap=date('j');
	$napszam=date('w');
	$napnev=alapnyelv("nap$napszam");
	$datumkiir="$honap $nap. $napnev";					  
	
	//Névnapok
	$datumn=date('md');
	$query="select nevnap from nevnaptar where datum='$datumn'";
	$lekerdez=mysql_query($query);
	list($nevnapok)=mysql_fetch_row($lekerdez);

	//Ünnepnapok
	$datumu=date('Y-m-d');
	$query="select unnep from unnepnaptar where datum='$datumu'";
	$lekerdez=mysql_query($query);
	list($unnepnapok)=mysql_fetch_row($lekerdez);

	$nevnap="$datumkiir<br>";
	if(!empty($unnepnapok)) $nevnap.="$unnepnapok, ";
	$nevnap.=$nevnapok;

//Loginûrlap
	if($belepve) {
		//Ha bent van
		$loginurlap="\n<table cellpadding=0 cellspacing=0 width=100% height=44><tr>";
		$loginurlap.="<td><div align=left class=kicsi><img src=img/space.gif width=4 height=4>Belépve:</div><div align=center class=alcim>$u_login</div></td>";
		$loginurlap.="<td><img src=img/space.gif width=10 height=2><br><a href=?m_id=28&m_op=add$linkveg class=loginlink><img src=$design_url/img/negyzet.jpg border=0 align=absmiddle> Beállítások</a><br><a href=?kilep=1$linkveg class=loginlink><img src=$design_url/img/negyzet.jpg border=0 align=absmiddle> Kilépés</a></td></tr></table>";
	}
	else {
		//Belépés
		$loginurlap="\n<table cellpadding=0 cellspacing=0 border=0 width=100% height=44><form method=post name=loginurlap><input type=hidden name=kilep value=0><tr>";
		$loginurlap.="\n<td><img src=img/space.gif width=8 height=5></td>";
		$loginurlap.="\n<td><img src=img/space.gif width=5 height=3><br><span class=loginlink>Felhasználónév:</span><br><img src=img/space.gif width=5 height=3><br><input type=text name=login value='".$_POST['login']."' size=16 class=loginurlap><br><img src=img/space.gif width=5 height=3></td>";

		$loginurlap.="\n<td><img src=img/space.gif width=8 height=5></td>";
		
		$loginurlap.="\n<td><img src=img/space.gif width=5 height=3><br><span class=loginlink>&nbsp; Jelszó:</span><br><img src=img/space.gif width=5 height=3><br><input type=password name=passw size=16 class=loginurlap><br><img src=img/space.gif width=5 height=3></td>";
		
		$loginurlap.="\n<td><img src=img/space.gif width=8 height=5></td>";
        
		$loginurlap.="<td rowspan=2 width=65 align=center>";
		if(!empty($_POST['login'])) {
			$loginurlap.="<span class=loginlink><font color=red>$loginhiba</font></span><br><img src=img/space.gif width=5 height=3><br>";
		}
		$loginurlap.="<input type=image border=0 src=$design_url/img/belepesgomb.jpg align=absmiddle></td>";
		
		$loginurlap.="\n</tr><tr>";
		$loginurlap.="\n<td><img src=img/space.gif width=8 height=5></td>";
		$loginurlap.="\n<td><a href=?m_id=28&fm=11$linkveg class=loginlink>Regisztráció <img src=$design_url/img/negyzet.jpg align=absmiddle border=0></a><br><img src=img/space.gif width=5 height=3></td>";
		$loginurlap.="\n<td><img src=img/space.gif width=8 height=5></td>";
		$loginurlap.="\n<td><a href=?m_id=28&m_op=jelszo$linkveg class=loginlink title='Jelszó emlékeztetõ'>Jelszó emlékeztetõ <img src=$design_url/img/negyzet.jpg border=0 align=absmiddle></a><br><img src=img/space.gif width=5 height=3></td>";
		$loginurlap.="\n<td><img src=img/space.gif width=8 height=5></td>";
		
		$loginurlap.="</tr><input type=hidden name=sid value=$sid></form></table>";
	}


//Fõmenü
	$felsomenu=fomenu('felso');
	$alsomenu=fomenu('also');


//Fõhasáb összeállítása
	$fohasab=$tartalom;

//Adminbal
	if($belepve and !empty($u_jogok)) $adminbal=keret(0);

//jobbkeret összeállítása
	$jobbhasab=keret(2);

//Balkeret összeállítása
	$balhasab=$adminbal.keret(1);


    if(empty($m_oldalsablon)) {
		$m_oldalsablon='alap';
    }
	$sablon="sablon_$m_oldalsablon.htm";

	$tmpl_file = $design_url.'/'.$sablon;

    $thefile = implode("", file($tmpl_file));
    $thefile = addslashes($thefile);
    $thefile = "\$r_file=\"".$thefile."\";";
    eval($thefile);
    
    return $html_kod = $r_file;
}



?>
