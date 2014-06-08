<?

function url_ell() {
// URL ellenőrzése
/////////////////////////////////
// fooldal tábla:
// id int(2) auto_increment primary
// cim varchar(30) [A főoldal címe, amit pl. a fejlécbe kiír a böngésző]
// kod varchar(30) [kód ékezet nékül, ami a főoldalhoz kapcsolódó könyvtárakat, vagy ilyesmiket azonosíthat]
// domain varchar(20) [a főoldal domainje - ebből azonosítjuk]
// design varchar(30) [az alapértelmezésben beállított design]
// bekerult datetime [amikor az oldal elindult]
/////////////////////////////////

// domain: $SERVER_NAME
// a domain utáni URL: $REQUEST_URI

    global $_SERVER, $db_name, $hiba, $hibauzenet, $hibauzenet_prog, $_GET, $_POST;
    
	$fooldal_id=$_REQUEST['fooldal_id'];
    if(!empty($fooldal_id)) {
        //Átugrasztjuk arra a főoldalra
        $query="select domain,ugras from fooldal where id='$fooldal_id'";
        $lekerdez=mysql_query($query);
        list($domain,$ugras)=mysql_fetch_row($lekerdez);

        if(!empty($ugras)) $ujlink=$ugras;
        else $ujlink=$domain;
        
        if(isset($_POST['fooldal_id']))
            foreach($_POST as $kulcs=>$ertek) {
                if($kulcs!='fooldal_id') $parameterT[]="$kulcs=$ertek";
            }
        if(isset($_GET['fooldal_id']))
            foreach($_GET as $kulcs=>$ertek) {
                if($kulcs!='fooldal_id') $parameterT[]="$kulcs=$ertek";
            }
        if(is_array($parameterT)) $parameterek=implode('&',$parameterT);
        
        header("Location: http://$ujlink?$parameterek");
    }


    $teljes_domain=$_SERVER['SERVER_NAME'];  // pl. www.plebania.net
    if(substr_count($teljes_domain, '.')>1) {      // lebalább 2 pontnál van aldomain
        $domain2=strstr($teljes_domain,'.');     //pl. .plebania.net  !!!FIGYELEM! www.aldomain.plebania.net -nél aldomain.plebania.net lesz a domain!!!
        $karakterszam=strlen($domain2);
        $aldomain_karakterszam=strlen($teljes_domain)-$karakterszam;
        $domain=substr($domain2,1,$karakterszam);     //pl. plebania.net
        $aldomain=substr($teljes_domain,0,$aldomain_karakterszam); //pl www
        if($aldomain=='www') $aldomain='';  //A www-t nem számítjuk bele
    }
    else {
        $domain=$teljes_domain;
        $aldomain='';
    }
    //Megnézzük, hogy aldomainnel van-e főoldal
    if(!empty($aldomain)) $domainell="$aldomain.$domain";
    else $domainell=$domain;
    $query="select id,cim,design,nyitomodul,ugras,domain from fooldal where domain='$domainell' and ok='i'";
    if(!$lekerdez=mysql_query($query)) {
        //Ha a lekérdezés nem sikerült...
        $hiba=true;
        $hibauzenet.='Az oldal beazonosításánál hiba történt.';
        $hibauzenet_prog.="\n\nHIBA az adatbázis lekérdezésnél (ell.inc #114 [url_ell]):\n" . mysql_error();
    }
    else if(mysql_num_rows($lekerdez) < 1 ) {
        //Ha nincs beállítva ez a domain, akkor a miserend.hu-t hozza be. Pont.
        if($_POST['admin']!=0 or $_GET['admin']!=0) $adminoldal = true;
        $urlT = array (10, 'Miserend', 'miserend', $aldomain, 26, $adminoldal);
        return $urlT;
        //$hiba=true;
        //$hibauzenet.='Az oldal beazonosításánál hiba történt.';
        //$hibauzenet_prog.="\n\nNINCS az adatbázisban regisztrálva az '$domainell' (ell.inc #114 [url_ell]):\n" . mysql_error();
    }
    else {
        $van=false;
        if(mysql_num_rows($lekerdez)==0) {
            $van=false;
            //Megnézzük még aldomain nélkül is
            //(ez esetben csak nyitómodul van az aldomainhez, nem önálló oldal)
            if(!empty($aldomain)) {
                $domainell=$domain;
                $query="select id,cim,kod,design,nyitomodul,ugras,domain from fooldal where domain='$domainell' and ok='i'";
                $lekerdez=mysql_query($query);
                
                if(mysql_num_rows($lekerdez)>0) $van=true;
            }
        }
        else $van=true;
        
        if(!$van) {
            //HIBA, rossz helyen akarjuk megnyitni az oldalt
            //ez esetben átirányítjuk az elsőként beállított főoldalunkra
            list($ujdomain)=mysql_fetch_row(mysql_query("select domain from fooldal order by sorrend"));
            header("Location: http://www.$ujdomain");
            exit;
        }
        else {
            list($fooldal_id,$fooldal_cim,$fooldal_design,$nyitomodul,$fooldal_ugras,$domain)=mysql_fetch_row($lekerdez);
            if(!empty($fooldal_ugras)) {
				//Ha másik oldalra kell átugratni a domainről
                header("Location: $fooldal_ugras");
                exit;
            }

			//???
            if($_POST['admin']!=0 or $_GET['admin']!=0) $adminoldal=true;

            //Esetleges aldomain esetén megnézzük a nyitómodult
            if(!empty($aldomain)) {
                $query="select nyitomodul,ugras from fooldal_aldomain where f_id='$fooldal_id' and aldomain='$aldomain'";
                $lekerdez=mysql_query($query);
				if($lekerdez) {
					list($nyitom,$ugras)=mysql_fetch_row($lekerdez);
					if(!empty($ugras)) {
						header("Location: $ugras");
						exit;
					}
					if(!empty($nyitom)) $nyitomodul=$nyitom;
					if(!empty($_POST['m_id']) or !empty($_GET['m_id'])) $nyitomodul='';
				}
            }
            
            $urlT = array ($fooldal_id, $fooldal_cim, $fooldal_design, $aldomain, $nyitomodul, $adminoldal);
            return $urlT;
        }
    }
}



function modul_ell($fooldal_id,$aldomain) {
//Behívandó modulok ellenőrzése
/////////////////////////////////
// fooldal_modulok tábla:
// id int(3) auto_increment primary
// fooldal_id int(2) [Melyik főoldalhoz tartozik]
// hova enum(f,b,t,j,l) [Hova kerül - fejléc, balmenü, tartalom, jobbmenü, lábléc]
// tipus enum(a,d) [Típusa - állandó (a) vagy alapértelmezett (d), melyet az egyes oldalak átírhatnak.
// sorrend int(4) [Ha több modul is behívásra kerül, itt lehet beállítani a sorrendet]
// modul int(3) [modul kódja]
/////////////////////////////////
// A fent behívott alapértelmezett modulokat az url paraméterei módosíthatják!

    global $hiba, $hibauzenet, $hibauzenet_prog, $html_kod;
    
    $query="select hova,tipus,sorrend,modul from fooldal_modulok where fooldal_id='$fooldal_id' order by hova, sorrend";
    if(!$lekerdez=mysql_query($query)) {
        //Ha a lekérdezés nem sikerült...
        $hiba=true;
        $hibauzenet.='';
        $hibauzenet_prog.="\n\nHIBA az adatbázis lekérdezésnél (ell.inc #125 [modul_ell]):\n" . mysql_error();
    }
    else {
        while(list($fm_hova,$fm_tipus,$fm_sorrend,$fm_modul)=mysql_fetch_row($lekerdez)) {
            if($fm_hova=='f') $modulList['f'][]=$fm_modul;
            elseif($fm_hova=='b') $modulList['b'][]=$fm_modul;
            elseif($fm_hova=='t') $modulList['b'][]=$fm_modul;
            elseif($fm_hova=='j') $modulList['b'][]=$fm_modul;
            elseif($fm_hova=='l') $modulList['b'][]=$fm_modul;
        }
        //Itt még ellenőrizni kell, hogy az URL adatai alapján változik-e valami!
        return $modulList;
    }
}

?>
