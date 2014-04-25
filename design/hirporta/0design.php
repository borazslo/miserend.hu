<?

function kicsinyites($forras,$kimenet,$max) {
    if(($hiba=exec("convert -geometry $max".'x'."$max $forras $kimenet")) != '') echo "HIBA!: $hiba";
    
}

function kicsinyites1($forras,$kimenet,$max) {

			if(!isset($max)) $max=120;    # maximum size of 1 side of the picture.

			$src_img=ImagecreateFromJpeg($forras);

			$oh = imagesy($src_img);  # original height
			$ow = imagesx($src_img);  # original width

			$new_h = $oh;
			$new_w = $ow;

			if($oh > $max || $ow > $max){
		       $r = $oh/$ow;
		       $new_h = ($oh > $ow) ? $max : $max*$r;
		       $new_w = $new_h/$r;
			}

			// note TrueColor does 256 and not.. 8
			$dst_img = ImageCreateTrueColor($new_w,$new_h);

			ImageCopyResized($dst_img, $src_img, 0,0,0,0, $new_w, $new_h, ImageSX($src_img), ImageSY($src_img));
			ImageJpeg($dst_img, "$kimenet");
}

function loginurlap($belephiba) {
    global $_POST,$design_url,$sessid;

    if(!empty($sessid)) $session="<input type=hidden name=sessid value='$sessid'>";
    foreach($_POST as $kulcs=>$ertek) {
        if($kulcs!='login' and $kulcs!='passw') {
            $adat.="<input type='hidden' name='$kulcs' value='$ertek'>";
        }
    }

	$bal.='<span class=alcim>Belépés</span><br><br>';	

    $bal.='<form name="urlap" method="post"><span class="alap">Bejelentkezési név:</span><br>';
    $bal.='<input type="text" class="urlap" size="20" name="login" value="'.$_POST['login'].'">';
    $bal.=$session;
    $bal.=$adat;
    $bal.='<br><br><span class="alap">Jelszó:</span><br><input type="password" class="urlap" size="20" name="passw">';
    $bal.="\n<br><br><input type=submit value=Belépés class=urlap></form>";
    
    if(!empty($belephiba)) $bal.="<span class=hiba>HIBA!<br>$belephiba</span>";
	
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
	
	if($helyzet>0) $query="select modul_id,fajlnev from oldalkeret where helyzet='$helyzet' and fooldal_id='$fooldal_id' $feltetel order by sorrend";
	else $query="select modul_id,fajlnev from oldalkeret where helyzet='1' and zart>'0' order by sorrend";
	if(!$lekerdez=mysql_db_query($db_name,$query)) echo "HIBA!<br>$query<br>".mysql_error();
	while(list($mid,$fajl)=mysql_fetch_row($lekerdez)) {				
		$include=$modul_url.'/'.$fajl.'_menu.php';
		//if($mid==7 and $szavazott) $op='eredmeny';
		//else $op=$helyzet;
		$op=$helyzet;
		include_once($include);
		
		$keret.=$hmenu;
		if(!empty($hmenu)) {
			if($helyzet==2) {
				$keret.='<table width="100%" border="0" cellspacing="0" cellpadding="0" background="'."$design_url/$design/img/jobbhatter.jpg".'"><tr><td>&nbsp;</td></tr></table>';
			}
		}		
		$hmenu='';

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
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$menucim,$domain)=mysql_fetch_row($lekerdez)) {
		$link="?fooldal_id=$id&sid=$sid";
		if($id==$fooldal_id) $aktiv='1';
		else $aktiv='';
		$kod.="<td background='$design_url/img/".$hol."menuhatter$aktiv.jpg' align=center valign=top>$terkoz[$hol]<a href='$link' title='$domain' class='".$hol."menulink'>$menucim</a></td>";
	}
	$kod.='</tr></table>';
 
    return $kod;
}

function almenu() {
	global $db_name,$m_id,$linkveg,$lang,$design_url,$szin,$design,$_GET,$_POST;

	if(!isset($design)) $design='alap';
	if(empty($lang)) $lang='hu';

	$rovat=$_POST['rovat'];
	if(!isset($rovat)) $rovat=$_GET['rovat'];
	if(!isset($rovat) and $m_id==1) $rovat=1;

	$fokat=$_POST['fokat'];
	if(!isset($fokat)) $fokat=$_GET['fokat'];

	$kat=$_POST['kat'];
	if(!isset($kat)) $kat=$_GET['kat'];

	$alkat=$_POST['alkat'];
	if(!isset($alkat)) $alkat=$_GET['alkat'];

	if($rovat>0) {
		list($rnev,$menuben)=mysql_fetch_row(mysql_db_query($db_name,"select nev,menuben from rovatkat where id='$rovat'"));
		if($menuben=='b' or $menuben=='j') {
			$menu.='<tr> 
          <td width="5" bgcolor="#C3C2BD"><img src="'."$design_url/$design/img/space.gif".'" width="5" height="5"></td>
          <td width="12"><img src="'."$design_url/$design/img/kgolyo_d.jpg".'" width="9" height="10"></td>
          <td width="163" height="18"><span class=kiscim>'.$rnev.'</span></td>
        </tr><tr>
		  <td width="5" bgcolor="#C3C2BD"><img src="'."$design_url/$design/img/space.gif".'" width="5" height="5"></td>
		  <td colspan=2><img src="'."$design_url/$design/img/csik1_balrovat.jpg".'" width="175" height="5"></td>
		</tr>';
		}

	$query="select id, nev from rovatkat where rovat='$rovat' and fokat=0 and menuben='b' and ok='i' and lang='$lang' order by sorszam"; //fõmenü
	if(!$lekerdez=mysql_db_query($db_name,$query)) echo "HIBA!<br>$query<br>".mysql_error();
	while(list($id,$nev)=mysql_fetch_row($lekerdez)) {		
		if($id==$fokat) { //Ha a fõkategória ki van választva (aktív)
			$aktiv='aktiv';
			$szin='k';
			$query_m="select id, nev from rovatkat where fokat='$id' and kat=0 and ok='i' and menuben='b' order by sorszam"; //menü lekérdezése
			if(!$lekerdez_m=mysql_db_query($db_name,$query_m)) echo "HIBA!<br>$query<br>".mysql_error();
			while(list($mid,$mnev)=mysql_fetch_row($lekerdez_m)) {
				if($kat==$mid) { //Ha a kategória (is) ki van választva (aktív)
					$maktiv='aktiv';
					$query_am="select id, nev from rovatkat where kat='$mid' and ok='i' and menuben='b' order by sorszam"; //Almenü lekérdezése
					if(!$lekerdez_am=mysql_db_query($db_name,$query_am)) echo "HIBA!<br>$query<br>".mysql_error();
					while(list($amid,$amnev)=mysql_fetch_row($lekerdez_am)) {
						if($alkat==$amid) { //Ha az almenü is aktív
							$amaktiv='aktiv';
						}
						else {
							$amaktiv='';
						}
						$aamenu.="<tr> 
							<td width='5' bgcolor='#C3C2BD'><img src='$design_url/$design/img/space.gif' width='5' height='5'></td>
							<td width='12'><img src='$design_url/$design/img/space.gif' width='9' height='10'></td>
							<td width='163' height='18'>&nbsp; <a href='?rovat=$rovat&fokat=$id&kat=$mid&alkat=$amid$linkveg' class='".$amaktiv."kismenulink'>$amnev</a></td>
							</tr>";
					}
				}
				else {
					$maktiv='';
					$aamenu='';
				}
				$amenu.="<tr> 
					<td width='5' bgcolor='#C3C2BD'><img src='$design_url/$design/img/space.gif' width='5' height='5'></td>
					<td width='12'><img src='$design_url/$design/img/space.gif' width='9' height='10'></td>
					<td width='163' height='18'><a href='?rovat=$rovat&fokat=$id&kat=$mid$linkveg' class='".$maktiv."kismenulink'>$mnev</a></td>
					</tr>";
				$amenu.=$aamenu;
			}
			
		}
		else {
			$aktiv='';
			$szin='s';
			$amenu='';
			$aamenu='';
		}
		$menu.="<tr> 
          <td width='5' bgcolor='#C3C2BD'><img src='$design_url/$design/img/space.gif' width='5' height='5'></td>
          <td width='12'><img src='$design_url/$design/img/".$szin."golyo_d.jpg' width='9' height='10'></td>
          <td width='163' height='18'><a href='?rovat=$rovat&fokat=$id$linkveg' class='".$aktiv."almenulink'>$nev</a></td>
        </tr>";
		 $menu.=$amenu;
	}
	if(!empty($menu)) {
		$sortav='<tr><td width="5" bgcolor="#C3C2BD"><img src="'."$design_url/$design/img/space.gif".'" width="5" height="5"></td><td colspan=2><img src=img/space.gif width=5 height=5></td></tr>';

		$a_menu='<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">';
		$a_menu.=$sortav;
		$a_menu.=$menu;
		$a_menu.=$sortav;
		$a_menu.='</table>';
	}
	}
	return $a_menu;
}

function design() {
    global $design_url,$db_name,$tartalom,$m_oldalsablon,$balkeret,$jobbkeret,$onload,$sid,$linkveg,$u_id,$u_login,$belepve,$loginhiba;

	if(!isset($design)) $design='alap';
    $title=alapnyelv('title');	
	$top=alapnyelv('top');
	
	$nyelvlinkT=langmenu();
	$enlink=$nyelvlinkT[0];
	$delink=$nyelvlinkT[1];
	$hulink=$nyelvlinkT[2];

	$onload='onload=fokusz();';

//Scriptek////////////////////////////////////
	$script='<script language="JavaScript" type="text/javascript">
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
	
	function linkTo_UnCryptMailto(s)	{
		location.href=UnCryptMailto(s);
	}

	// -->
	</script>';
////////////////////////////////////////////

	$emaillink_lablec="<A HREF=\"javascript:linkTo_UnCryptMailto('ocknvq%3CjktdgmwnfguBmcvqnkmwu0jw');\" class=emllink>info<img src=img/kukaclent.gif align=absmiddle border=0>miserend.hu</a>";
	

//Impresszum link
	$impkiir=alapnyelv('Impresszum');
	$impfm=alapnyelv('impfm');
	$impresszumlink="<a href=?m_id=17&fm=$impfm$linkveg class=implink>$impkiir</a>";

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
	$lekerdez=mysql_db_query($db_name,$query);
	list($nevnapok)=mysql_fetch_row($lekerdez);

	//Ünnepnapok
	$datumu=date('Y-m-d');
	$query="select unnep from unnepnaptar where datum='$datumu'";
	$lekerdez=mysql_db_query($db_name,$query);
	list($unnepnapok)=mysql_fetch_row($lekerdez);

	$nevnap="$datumkiir<br>";
	if(!empty($unnepnapok)) $nevnap.="$unnepnapok, ";
	$nevnap.=$nevnapok;

//Loginûrlap
	if($belepve) {
		//Ha bent van
		$loginurlap="\n<table cellpadding=0 cellspacing=0 width=100% height=44><tr>";
		$loginurlap.="<td><div align=left class=kicsi><img src=img/space.gif width=4 height=4>Belépve:</div><div align=center class=alcim>$u_login</div></td>";
		$loginurlap.="<td><img src=img/space.gif width=10 height=2><br><a href=?m_id=22&m_op=add$linkveg class=loginlink><img src=$design_url/img/negyzet.jpg border=0 align=absmiddle> Beállítások</a><br><a href=?kilep=1$linkveg class=loginlink><img src=$design_url/img/negyzet.jpg border=0 align=absmiddle> Kilépés</a></td></tr></table>";
	}
	else {
		//Belépés
		$loginurlap="\n<table cellpadding=0 cellspacing=0 border=0 width=100% height=44><form method=post name=loginurlap><input type=hidden name=kilep value=0><tr>";
		$loginurlap.="<td><img src=img/space.gif width=8 height=5></td>";
		$loginurlap.="<td><img src=img/space.gif width=5 height=3><br><span class=loginlink>Felhasználónév:</span><br><img src=img/space.gif width=5 height=3><br><input type=text name=login value='".$_POST['login']."' size=16 class=loginurlap><br><img src=img/space.gif width=5 height=3><br><a href=?m_id=22&fm=31$linkveg class=loginlink>Regisztráció <img src=$design_url/img/negyzet.jpg align=absmiddle border=0></a><br><img src=img/space.gif width=5 height=3></td>";
		$loginurlap.="<td><img src=img/space.gif width=8 height=5></td>";
		$loginurlap.="<td><img src=img/space.gif width=5 height=3><br><span class=loginlink>&nbsp; Jelszó:</span><br><img src=img/space.gif width=5 height=3><br><input type=password name=passw size=16 class=loginurlap><br><img src=img/space.gif width=5 height=3><br><a href=?m_id=22&m_op=jelszo$linkveg class=loginlink title='Jelszó emlékeztetõ'>Jelszó emlékeztetõ <img src=$design_url/img/negyzet.jpg border=0 align=absmiddle></a><br><img src=img/space.gif width=5 height=3></td>";
		$loginurlap.="<td><img src=img/space.gif width=8 height=5></td>";
        $loginurlap.="<td width=65 align=center>";
		if(!empty($_POST['login'])) {
			$loginurlap.="<span class=loginlink><font color=red>$loginhiba</font></span><br><img src=img/space.gif width=5 height=3><br>";
		}
		$loginurlap.="<input type=image border=0 src=$design_url/img/belepesgomb.jpg align=absmiddle></td>";
		$loginurlap.="<input type=hidden name=sid value=$sid></form></table>";
	}


//Fõmenü
	$felsomenu=fomenu('felso');
	$alsomenu=fomenu('also');


//Fõhasáb összeállítása
	$fohasab=$tartalom;

//Adminbal
//	if($belepve) $adminbal=keret(0);

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
