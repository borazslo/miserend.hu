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
    global $twig;

	if(!$belepve) $feltetel=" and zart=0";
	$nyelv=$lang;
	if(empty($nyelv)) $nyelv='hu';

	$tablabgT=array('#D1DDE9','#F4F2F5');
	$fejlecbgT=array('#053D78','#8D317C');
    
    $blocks = array();
	if($helyzet>0) {
		$query="SELECT modul_id,fajlnev FROM oldalkeret WHERE helyzet='$helyzet' and fooldal_id='$fooldal_id' $feltetel order by sorrend";
		if(!$lekerdez=mysql_query($query)) echo "HIBA!<br>$query<br>".mysql_error();
		while(list($mid,$fajl)=mysql_fetch_row($lekerdez)) {
            $blocks[] = array('include'=>$modul_url.'/'.$fajl.'_menu.php','op'=>$helyzet,'mid'=>$mid);
        }
     } else {
		//Ha a $helyzet=0, akkor admin menü
        $blocks[] = array('include'=>$modul_url.'/admin_menu.php','op'=>$helyzet,'mid'=>$mid,'bgcolor'=>'#ECE5C8','header'=>array('bgcolor'=>'#F5CC4C'));			
        $blocks[] = array('include'=>$modul_url.'/chat_menu.php','op'=>$helyzet,'mid'=>$mid,'bgcolor'=>'#ECE5C8','header'=>array('bgcolor'=>'#F5CC4C'));			
     }
     $keret = '';
     foreach($blocks as $block) {
        $op = $block['op'];
        
        if(@include_once($block['include'])) {
            $a=$helyzet;
            if($a>1) $a=0;
            $hmenuT[1] = iconv('ISO-8859-2','UTF-8',$hmenuT[1]);
            $variables = array('content'=>$hmenuT[1],'bgcolor' => $tablabgT[$a]);            
            if(!empty($hmenuT[0])) {
                $hmenuT[0] = iconv('ISO-8859-2','UTF-8',$hmenuT[0]);                
                $variables['header'] = array('content'=>$hmenuT[0],'bgcolor'=>$fejlecbgT[$a]);
            }
            // Áthozott értékek betöltése
            foreach($block as $key => $value) {
                if(is_array($value)) {
                    foreach($value as $k => $v)
                        $variables[$key][$k] = $v; }
                else  $variables[$key] = $value;
            }
            
            $a++;		
			$hmenuT='';		        
        } else {
            $variables = array('content'=>"<font color='red' size='-3'>HIBA! file.".$block['include'].". mysql.oldalkeret</font>");
        }
        $keret .= "\n".iconv('UTF-8','ISO-8859-2',$twig->render('block.html',$variables));                    
    }    
	return $keret;
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
    global $twig;
	
    $items = array();
	$query="select id,menucim,domain from fooldal where ok='i' and menucim!='' order by menusorrend";
	$lekerdez=mysql_query($query);
	while(list($id,$menucim,$domain)=mysql_fetch_row($lekerdez)) {
        $menucim = iconv('ISO-8859-2','UTF-8',$menucim);
        $item = array('id'=>$id,'title'=>$menucim,'domain'=>$domain);
		$item['link'] = "?fooldal_id=$id&sid=$sid";
		if($id==$fooldal_id) $item['aktiv'] = '1';
        $items[] = $item;		
	}	 
    $kod = iconv('UTF-8','ISO-8859-2',$twig->render('menu_main.html',array('items' => $items, 'hol'=>$hol, 'design_url' => $design_url)));
    return $kod;
}

function design() {
    global $design_url,$db_name,$tartalom,$m_oldalsablon,$balkeret,$jobbkeret,$onload,$sid,$linkveg,$u_id,$u_login,$u_jogok,$belepve,$loginhiba,$script,$meta,$titlekieg;

     
    global $twig;
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
        $u_login = iconv('ISO-8859-2','UTF-8',$u_login);      
        $loginurlap = iconv('UTF-8','ISO-8859-2',$twig->render('login_loggedin.html',array('linkveg' => $linkveg, 'design_url' => $design_url,'u_login'=>$u_login)));
    }
	else {
		//Belépés
        $loginhiba = iconv('ISO-8859-2','UTF-8',$loginhiba);
        $loginurlap = iconv('UTF-8','ISO-8859-2',$twig->render('login_login.html',array('linkveg' => $linkveg, 'design_url' => $design_url,'login'=>$_POST['login'],'loginhiba'=>$loginhiba,'sid'=>$sid)));
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
