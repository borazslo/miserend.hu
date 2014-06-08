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
        //!! Fordítoss sorrend!
        $blocks[] = array('include'=>$modul_url.'/chat_menu.php','op'=>$helyzet,'mid'=>$mid,'bgcolor'=>'#ECE5C8','header'=>array('bgcolor'=>'#F5CC4C'));			
        $blocks[] = array('include'=>$modul_url.'/admin_menu.php','op'=>$helyzet,'mid'=>$mid,'bgcolor'=>'#ECE5C8','header'=>array('bgcolor'=>'#F5CC4C'));			
        
     }
     $keret = array();
     foreach($blocks as $block) {
        $op = $block['op'];
        
        if(@include_once($block['include'])) {
            $a=$helyzet;
            if($a>1) $a=0;
            $vars = array('content'=>$hmenuT[1],'bgcolor' => $tablabgT[$a]);            
            if(!empty($hmenuT[0])) {
                $vars['header'] = array('content'=>$hmenuT[0],'bgcolor'=>$fejlecbgT[$a]);
            }
            // Áthozott értékek betöltése
            foreach($block as $key => $value) {
                if(is_array($value)) {
                    foreach($value as $k => $v)
                        $vars[$key][$k] = $v; }
                else  $vars[$key] = $value;
            }
            
            $a++;		
			$hmenuT='';		        
        } else {
            $vars = array('content'=>"<font color='red' size='-3'>HIBA! file.".$block['include'].". mysql.oldalkeret</font>");
        }
        $keret[] = $vars;                    
    }    
	return $keret;
}

function formazo($adatT,$tipus) {
	global $design_url,$szin,$design;

	if(!isset($design)) $design='alap';

    if($tipus == 'doboz') return $adatT[2];
    
	$cim=$adatT[0];
	$cimlink=$adatT[1];
	$tartalom=$adatT[2];
	$tartalom2=$adatT[3]; //híreknél 2. hasáb
	$tovabb=$adatT[4]; //híreknél "cikk bővebben"
	$tovabblink=$adatT[5]; //általában a $cimlink
		
    $tmpl_file = $design_url.'/'.$tipus.'.htm';
    echo $tmpl_file;
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
        $item = array('id'=>$id,'title'=>$menucim,'domain'=>$domain);
		$item['link'] = "?fooldal_id=$id&sid=$sid";
		if($id==$fooldal_id) $item['aktiv'] = '1';
        $items[] = $item;		
	}	 
    $kod = array('items' => $items, 'hol'=>$hol, 'design_url' => $design_url);
    return $kod;
}

function design(&$vars) {
    global $design_url,$db_name,$tartalom,$m_oldalsablon,$balkeret,$jobbkeret,$onload,$sid,$linkveg,$u_id,$u_login,$u_jogok,$belepve,$loginhiba,$script,$meta,$titlekieg;

    global $twig;

    if(!is_array($meta)) $vars['meta'][] = $meta;
    else $vars['meta'] = $meta;

    if(!is_array($script)) $vars['script'][] = $script;
    else $vars['script'] = $script;
    
	if(!isset($design)) $design='alap';
    
    $vars['pagetitle'] = 'VPP - miserend';
    if(isset($titlekieg)) $vars['pagetitle'] = preg_replace("/^( - )/i","",$titlekieg)." | ".$vars['pagetitle'];
    
	$top=alapnyelv('top');
	
	$nyelvlinkT=langmenu();
	$enlink=$nyelvlinkT[0];
	$delink=$nyelvlinkT[1];
	$hulink=$nyelvlinkT[2];

	if(!$belepve) {
		if(empty($vars['body']['onload'])) $vars['body']['onload']='onload="fokusz();"';
		else $vars['body']['onload']="onload=\"".$vars['body']['onload']." fokusz();\"";
	}
	elseif(!empty($vars['body']['onload'])) {
		$vars['body']['onload']="onload=\"".$vars['body']['onload'].";\"";
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
	* A Google Analytics kimenő linkjeit követő funkció.
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
    $vars['bottom']['left']['content'] = $impresszumlink."<br/>".$emaillink_lablec;
    
	$vars['bottom']['right']['content'] = "<a href=http://www.b-gs.hu class=implink title='BGS artPart' target=_blank>design</a><br><a href=http://www.florka.hu class=implink title='Florka Kft.' target=_blank>programozás</a>";

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
    $vars['nevnap'] = $nevnap;
    
//Loginűrlap
	if($belepve) {
        $vars['login']['loggedin'] = true;
		//Ha bent van
        $vars['login']['vars'] = array('linkveg' => $linkveg, 'design_url' => $design_url,'u_login'=>$u_login);
    }
	else {
		//Belépés
        $vars['login']['vars'] = array('linkveg' => $linkveg, 'design_url' => $design_url,'login'=>$_POST['login'],'loginhiba'=>$loginhiba,'sid'=>$sid);       
	}


//Főmenü
	$vars['mainmenu']['top'] = fomenu('felso');    
	$vars['mainmenu']['bottom'] = fomenu('also');


//Főhasáb összeállítása
    $vars['content'] = $tartalom;

//jobbkeret összeállítása
	$vars['sidebar']['right']['blocks'] = keret(2);

//Balkeret összeállítása
	$vars['sidebar']['left']['blocks'] = keret(1);
//Adminbal    
    if($belepve and !empty($u_jogok)) {
        foreach(keret(0) as $block)
            array_unshift($vars['sidebar']['left']['blocks'],$block);
    }

    $sablon = "page";
    if($m_oldalsablon == 'aloldal') $sablon .= '_subadminpage';
    elseif(!empty($m_oldalsablon) AND $m_oldalsablon != 'alap') $sablon .= '_'.$m_oldalsablon;

    return $twig->render($sablon.'.html',$vars);


	
}



?>
