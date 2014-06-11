<?php
session_start();

/////////////////////////////////
// Fő változók:
// $hiba (true,false)
// $hibauzenet (hibaüzenet a látogatónak)
// $hibauzenet_prog (hibaüzenet a programozónak)
// $html_kod (html kód, amit a végén kiírunk)
/////////////////////////////////

/////////////////////////////////
//Alapadatok beállítása (config)
/////////////////////////////////
    $hiba=false;
    $vars = array();

//Adatbázis csatlakozás előkészítése, elindítása
    if(!@include_once('config.inc')) {
        $hiba=true;
        $hibauzenet_prog.='<br>HIBA! A konfigurációs fájl behívásakor!';
	echo 'hiba';
    }
    dbconnect();
        
/////////////////////////////////
//Ellenőrzések, beállítások
/////////////////////////////////
    if (!is_dir('fajlok/igenaptar')) {
        include_once('install.php');
    }

    if(!@include_once('ell.php')) {
        $hiba=true;
        $hibauzenet_prog.='<br>HIBA! Az ellenőrző fájl behívásakor!';
    }

//URL ellenőrzése
    if(!$hiba) {
        $urlT=url_ell();
		
        $fooldal_id=$urlT[0];
        $fooldal_cim=$urlT[1];
        $design=$urlT[2];
        $aldomain=$urlT[3];
        $nyitomodul=$urlT[4];
        $adminoldal=$urlT[5];

		if(!empty($_GET['design'])) $design=$_GET['design'];

        $vars['design_url'] = $design_url = $config['path']['domain'];
        
	}


//Letiltott IP-ről van-e szó
/*
    if(!$hiba) {
        $tiltott_IP_T=ip_ell($fooldal_id);
        //$tiltott_IP és belep_tiltott_IP true vagy false
    }
*/

		//Beállított modulok
		if($_GET['templom']>0) {
			$M_ID=26;
			$M_OP='view';
			$TID=$_GET['templom'];
		}

		//Beállított modulok
		if($_GET['hir']>0) {
			$M_ID=1;
			$M_OP='view';
			$HID=$_GET['hir'];
			if(!empty($_GET['design'])) $M_ID=33;
		}

//Twig        
require_once 'vendor/twig/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader); // cache?        

//MobileDetect
require_once 'vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php';        
$detect = new Mobile_Detect;
//$isMobile = $detect->isMobile();
//$isTablet = $detect->isTablet();
if($detect->isAndroidOS() == 1 AND $_SESSION['isAndroidOS'] != 'shown' OR 1 == 1) {
    echo $twig->render('android_advertisment.html',$vars);
    $_SESSION['isAndroidOS'] = 'shown';
    exit;
}
       
/////////////////////////////////
//designelemek behívása
/////////////////////////////////
    if(!$hiba) {
        if(!include("design.php")) {
            $hiba=true;
            $hibauzenet.='Elnézését kérjük, az oldal nem jeleníthető meg.';
            $hibauzenet_prog.='HIBA! A desing program nem hívható be!<br>'.$design_url.'/design.php';
        }
    }

/////////////////////////////////
//modul kiválasztása
/////////////////////////////////
    if(!$hiba) {
        $m_id=$_POST['m_id'];
        if(empty($m_id)) $m_id=$_GET['m_id'];
        if(empty($m_id) and $nyitomodul>0) {
			$m_id=$nyitomodul;
			//if($lang!='hu' and !empty($lang)) $m_id=17;
		}

		if(!empty($M_ID)) $m_id=$M_ID;

        if(!empty($m_id)) {
            $query="select fajlnev,sablon,zart from modulok where id='$m_id' and ok='i'";
            if(!$lekerdez=mysql_query($query)) {
                $hiba=true;
                $hibauzenet.='A választott funkció behívása sikertelen.';
                $hibauzenet_prog.='HIBA a modul behívásánál:<br>'.mysql_error();
            }
            list($m_fajlnev,$m_oldalsablon,$m_zart)=mysql_fetch_row($lekerdez);
        }
		
		if(!empty($m_fajlnev) and is_file("$modul_url/$m_fajlnev.php")) {
			$modul=$modul_url.'/'.$m_fajlnev.'.php';
		}
		else {
			$modul=$modul_url."/alap$lang.php";
		}
    }

/////////////////////////////////
//belépés ellenőrzése
/////////////////////////////////
    $belepve=false;
	$sid=$_COOKIE['sid'];
	if(!empty($sid)) {
		$vancookie=true;
		if(!empty($_GET['sid'])) $sid=$_GET['sid'];
	}
	else {
		$vancookie=false;
		$sid=$_POST['sid'];
	    if(empty($sid)) $sid=$_GET['sid'];
	}

	$kilep=$_GET['kilep'];
	if(!empty($_POST['login'])) $kilep='';

	include('login.php');
    //Ha kilép
	if($kilep>0) {
		kilepes();
        $belepve=false;
	    $u_id=0;
		$u_login='';
        $u_jogok='';
		$u_oldal='';
		$u_beosztas='';
		setcookie('sid','',time()-3600,'/','miserend.hu');
		setcookie('sid','',time()-3600,'/','hirporta.hu');
		setcookie('sid','',time()-3600,'/','plebania.net');
		setcookie('sid','',time()-3600,'/','taborhely.info');
		setcookie('sid','',time()-3600,'/','emberhalasz.net');
    }
	else {
		//Ha már van sid-je, akkor frissítjük
        if (!empty($sid)) {
			setcookie('sid',$sid,time()+86400,'/','miserend.hu');
			setcookie('sid',$sid,time()+86400,'/','hirporta.hu');
			setcookie('sid',$sid,time()+86400,'/','plebania.net');
			setcookie('sid',$sid,time()+86400,'/','taborhely.info');
			setcookie('sid',$sid,time()+86400,'/','emberhalasz.net');
	        $belepesT=belepell();
			$belepve=$belepesT[0];
		}
        //Ha még nincs, akkor adunk neki
	    else {
		    $belepesT=ujvendeg();
			$sid=$belepesT[10];
			$belepve=$belepesT[0];
			setcookie('sid',$sid,time()+86400,'/','miserend.hu');
			setcookie('sid',$sid,time()+86400,'/','hirporta.hu');
			setcookie('sid',$sid,time()+86400,'/','plebania.net');
			setcookie('sid',$sid,time()+86400,'/','taborhely.info');
			setcookie('sid',$sid,time()+86400,'/','emberhalasz.net');
        }
	    //Ha zárt oldalra akarna belépni és még nincs belépve
		if(($m_zart and !$belepve) or !empty($_POST['login'])) {
			$belepesT=beleptet();
			$belepve=$belepesT[0];
			
			$atvett=$belepesT[20];
			if($atvett=='i') {
				$modul="$modul_url/regisztracio.php";
				$m_id=28;
			}			
		}		
		
		$u_id=$belepesT[1];
        $u_login=$belepesT[2];
        $u_jogok=$belepesT[3];
	    $belephiba=$belepesT[4];
		$szavazott=$belepesT[5];
		$u_oldal=$belepesT[6];
		$u_beosztas=$belepesT[7];
		$tiltott_IP=$belepesT[8];
		$belep_tiltott_IP=$belepesT[9];
		$u_varos=$belepesT[12];
		$u_becenev=$belepesT[13];
		$u_baratok=$belepesT[14];
		$u_ismerosok=$belepesT[15];

		if(strstr($belep_tiltott_IP,$fooldal_id)) {
			$belepve=false;
			$loginhiba='Felhasználó kizárva!';
		}
		if(strstr($tiltott_IP,$fooldal_id)) {
			header("location: http://www.plebania.net");
			exit;
		}

	}

	if($vancookie) {
		//Ha van a cookieban sid, akkor nem kell a linkek végére kitenni
		$linkveg_sid='';
	}
	else {
		$linkveg_sid="&sid=$sid";
	}
	$vars['global']['linkveg'] = $linkveg = $linkveg_sid;

/////////////////////////////////
//jogosultság ellenőrzése
/////////////////////////////////
    $jogosult=false;
    $mjogell='-'.$m_id.'-';
    if(strstr($u_jogok,$mjogell) or $m_jogok==0) $jogosult=true;
    
    $mehet=false;
    if($m_zart and $belepve and $jogosult) $mehet=true;
    elseif(!$m_zart) $mehet=true;

/////////////////////////////////
//nyelv beállítása
/////////////////////////////////
	nyelvmeghatarozas();

/////////////////////////////////
//tartalmi rész összeállítása
/////////////////////////////////
    if(!$hiba and !$tiltott_IP_T[0] and $mehet) {
        $m_op=$_POST['m_op'];
        if(empty($m_op)) $m_op=$_GET['m_op'];
        if(empty($m_op)) $m_op='index';

		if(!empty($M_OP)) $m_op=$M_OP;

		if($atvett=='i') $m_op='atvett';

        //Behívjuk a modulhoz a szótárát is
        $szotarfajl="$modul_url/szotar/$m_id$lang.inc";
        if($m_id>0 and is_file($szotarfajl)) {
            if(!@include_once($szotarfajl)) {
                $hiba=true;
                $hibauzenet_prog.='<br>HIBA a modul nyelvi fájl behívásánál!';
            }
        }

		//Behívjuk a modulhoz az egyéni designfájlt is, ha van
        $designfajl="$design_url/d_$m_id.php";
        if($m_id>0 and is_file($designfajl)) {
            if(!@include_once($designfajl)) {
                $hiba=true;
                $hibauzenet_prog.='<br>HIBA a modul design fájl behívásánál!';
            }
        }
        //Mivel jogosult, behívjuk a modult
        if(!include_once($modul)) {
            $hiba=true;
            $hibauzenet.='';
            $hibauzenet_prog.='HIBA! A választott modul nem hívható be!<br>'.$modul;
        }
    }
    //Nincs hozzá jogosultsága!
    elseif(!$jogosult and $m_zart and $belepve) {
        $tartalom='<big><br><span class=hiba>HIBA a modul megnyitásában!</span></big>';
    }
    //Nincs belépve, kirakjuk az űrlapot
    elseif($m_zart and !$belepve and !$tiltott_IP_T[1]) {
        $tartalom=loginurlap($belephiba);
        $vars['body']['onload'] = 'onload="fokusz();"';
    }
	//Le van tiltva, nem léphet be
    elseif($m_zart and $tiltott_IP_T[1]) {
        $tartalom='<big><br><span class=hiba>Nem léphetsz be!</span></big>';
    }

/////////////////////////////////
//hibaüzenetek kezelése
/////////////////////////////////
    if($hiba) {
        $html_kod=$hibauzenet_prog;
    }
	elseif($tiltott_IP_T[0]) {
        $html_kod='Ki vagy tiltva!';
    }


/////////////////////////////////
//tartalom formázása a sablonba
/////////////////////////////////
    else {
        $html_kod=design($vars);
    }

/////////////////////////////////
//html kód kiküldése a böngészőnek
/////////////////////////////////
    print $html_kod;

?>
