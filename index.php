<?php
include("load.php");

if(isset($_GET['q'])) { include $_GET['q'].".php"; exit;}


/////////////////////////////////
//Ellenőrzések, beállítások
/////////////////////////////////


$fooldal_id=10;
$fooldal_cim='Miserend';
$design='miserend';
$aldomain=$urlT[3]; // ures!!
$nyitomodul=26;
if($_REQUEST['admin']!=0) $adminoldal = true; else $adminoldal = false; 

if(!empty($_GET['design'])) $design=$_GET['design']; 

$vars['design_url'] = $design_url = $config['path']['domain'];

//Beállított modulok
if($_REQUEST['templom']>0 AND ( !isset($_REQUEST['m_id']) OR !isset($_REQUEST['m_op'])))  {
	$M_ID=26;
	$M_OP='view';
	$TID=$_REQUEST['templom'];
} elseif(isset($_REQUEST['m_id'])) {
	$M_ID=$_REQUEST['m_id'];
} else {
	$M_ID=26;
}
            
$modul_url='moduls';
$nyitomodul=1;

/////////////////////////////////
//modul kiválasztása
/////////////////////////////////
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

/////////////////////////////////
//belépés ellenőrzése
/////////////////////////////////
    $belepve=$user->loggedin;

    // Ez mi ez??
	if(($m_zart and !$belepve) or !empty($_POST['login'])) {
		$atvett = $user->atvett;
		if($user->atvett=='i') {
			$modul="$modul_url/regisztracio.php";
			$m_id=28;
		}			
	}		
		
	$u_id=$user->uid;
    $u_login=$user->username;
    $u_jogok=$user->jogok;
    
    $belephiba='jaj'; /*$belepesT[4];
	$szavazott=$belepesT[5];
	$u_oldal=$belepesT[6];
	$tiltott_IP=$belepesT[8];
	$belep_tiltott_IP=$belepesT[9];*/
	
	$u_varos=$user->varos;
	$u_becenev=$user->becenev;
	$u_baratok=$user->baratok;
	$u_ismerosok=$user->ismerosok;
		

	$vars['global']['linkveg'] = "";

/////////////////////////////////
//jogosultság ellenőrzése
/////////////////////////////////
    $jogosult=false;
    $mjogell='-'.$m_id.'-';
    if(strstr($u_jogok,$mjogell) or $m_jogok==0) $jogosult=true;
    
    $mehet=false;
    if($m_zart and $belepve and $jogosult) $mehet=true;
    elseif(!$m_zart) $mehet=true;

    if($user->jogok != '') { $vars['chat'] = chat_vars(); }
/////////////////////////////////
//nyelv beállítása
/////////////////////////////////
	nyelvmeghatarozas();

/////////////////////////////////
//tartalmi rész összeállítása
/////////////////////////////////
    $script .= '<link href="css/jquery-ui.icon-font.css" rel="stylesheet" type="text/css" />'."\n";
    $script .= '<script src="/bower_components/jquery/dist/jquery.min.js"></script>'."\n";
    $script .= '<script src="/bower_components/jquery-ui/jquery-ui.js"></script>'."\n";
    $script .= '<script src="/bower_components/jquery-ui/ui/autocomplete.js"></script>'."\n";
    $script .= '<script src="/bower_components/jquery-colorbox/jquery.colorbox.js"></script>';
    $script .= '<script src="/bower_components/jquery-colorbox/i18n/jquery.colorbox-hu.js"></script>';
    $script .= '<script src="jscripts2/als/jquery.als-1.5.min.js"></script>';
    
    $script .= '<link rel="stylesheet" href="templates/colorbox.css" />';
    $script .= '<link rel="stylesheet" href="templates/als.css" />';

    $script .= '<link rel="stylesheet" href="/bower_components/jquery-ui/themes/smoothness/jquery-ui.css">'."\n";

    $script .= '<script src="js/miserend.js"></script>';


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
        if(preg_match('/\/admin_/i', $modul)) {
            $loader = new Twig_Loader_Filesystem('templates');
            $twig = new Twig_Environment($loader); // cache?        
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
