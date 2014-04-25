<?

function url_ell() {
// URL ellenõrzése
/////////////////////////////////
// fooldal tábla:
// id int(2) auto_increment primary
// cim varchar(30) [A fõoldal címe, amit pl. a fejlécbe kiír a böngészõ]
// kod varchar(30) [kód ékezet nékül, ami a fõoldalhoz kapcsolódó könyvtárakat, vagy ilyesmiket azonosíthat]
// domain varchar(20) [a fõoldal domainje - ebbõl azonosítjuk]
// design varchar(30) [az alapértelmezésben beállított design]
// bekerult datetime [amikor az oldal elindult]
/////////////////////////////////

// domain: $SERVER_NAME
// a domain utáni URL: $REQUEST_URI

    global $_SERVER, $db_name, $hiba, $hibauzenet, $hibauzenet_prog, $_GET, $_POST;
    
	$fooldal_id=$_POST['fooldal_id'];
    if(!empty($fooldal_id)) {
        //Átugrasztjuk arra a fõoldalra
        $query="select domain,ugras from fooldal where id='$fooldal_id'";
        $lekerdez=mysql_query($query);
        list($domain,$ugras)=mysql_fetch_row($lekerdez);

        if(!empty($ugras)) $ujlink=$ugras;
        else $ujlink=$domain;
        
        foreach($_POST as $kulcs=>$ertek) {
            if($kulcs!='fooldal_id') $parameterT[]="$kulcs=$ertek";
        }
        if(is_array($parameterT)) $parameterek=implode('&',$parameterT);
        
        header("Location: http://$ujlink?$parameterek");
    }

    $fooldal_id=$_GET['fooldal_id'];
    if(!empty($fooldal_id)) {
        //Átugrasztjuk arra a fõoldalra
        $query="select domain,ugras from fooldal where id='$fooldal_id'";
        $lekerdez=mysql_query($query);
        list($domain,$ugras)=mysql_fetch_row($lekerdez);

        if(!empty($ugras)) $ujlink=$ugras;
        else $ujlink=$domain;
        
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
    //Megnézzük, hogy aldomainnel van-e fõoldal
    if(!empty($aldomain)) $domainell="$aldomain.$domain";
    else $domainell=$domain;
    $query="select id,cim,design,nyitomodul,ugras,domain from fooldal where domain='$domainell' and ok='i'";
    if(!$lekerdez=mysql_query($query)) {
        //Ha a lekérdezés nem sikerült...
        $hiba=true;
        $hibauzenet.='Az oldal beazonosításánál hiba történt.';
        $hibauzenet_prog.="\n\nHIBA az adatbázis lekérdezésnél (ell.inc #114 [url_ell]):\n" . mysql_error();
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
            //ez esetben átirányítjuk az elsõként beállított fõoldalunkra
            list($ujdomain)=mysql_fetch_row(mysql_query("select domain from fooldal order by sorrend"));
            header("Location: http://www.$ujdomain");
            exit;
        }
        else {
            list($fooldal_id,$fooldal_cim,$fooldal_design,$nyitomodul,$fooldal_ugras,$domain)=mysql_fetch_row($lekerdez);
            if(!empty($fooldal_ugras)) {
				//Ha másik oldalra kell átugratni a domainrõl
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

function extra_ell($fooldal_id) {
//Extra alkalom ellenõrzése
/////////////////////////////////
// id int(3) auto_increment primary
// tol datetime [amikortól érvényes]
// ig datetime [ameddig érvényes]
// uzenet text [üzenet szövege, ami kiírásra kerül]
// tipus enum(s,bn) [esemény típusa: semmi nem jön be, csak az üzenet, vagy belépés nincs, csak üzenet + egyéb oldalak]
// fooldalak varchar(50) [mely fõoldalakat érinti az esemény]
//
// FIGYELEM! Egy idõszakban egy oldalhoz CSAK EGY esemény lehetséges
/////////////////////////////////

    global $hiba, $hibauzenet, $hibauzenet_prog, $db_name;

    $most=date('Y-m-d H:i:s');
    $fooldal="and fooldalak like '%-".$fooldal_id."-%'";

    $query="select uzenet,tipus from extra_alkalom where tol<'$most' and ig>'$most' $fooldal";
    if(!$lekerdez=mysql_query($query)) {
        //Ha a lekérdezés nem sikerült...
        $hiba=true;
        $hibauzenet.='HIBA az adatbázis lekérdezésnél. A szolgáltatás jelenleg nem érhetõ el.';
        $hibauzenet_prog.="\n\nHIBA az adatbázis lekérdezésnél (ell.inc #109 [extra_ell]):\n" . mysql_error();
    }
    else {
        list($extra_uzenet,$extra_tipus)=mysql_fetch_row($lekerdez);
        //Egy idõszakban egy oldalhoz CSAK EGY esemény lehetséges
        $extraT = array ($extra_uzenet, $extra_tipus);
        return $extraT;
    }
}

function modul_ell($fooldal_id,$aldomain) {
//Behívandó modulok ellenõrzése
/////////////////////////////////
// fooldal_modulok tábla:
// id int(3) auto_increment primary
// fooldal_id int(2) [Melyik fõoldalhoz tartozik]
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
        //Itt még ellenõrizni kell, hogy az URL adatai alapján változik-e valami!
        return $modulList;
    }
}

?>
