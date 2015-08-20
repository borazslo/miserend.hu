<?php
include("load.php");

//TODO: ez itt nem túl barátságok dolog
if(isset($_GET['q'])) { include $_GET['q'].".php"; exit;}


//Beállított modulok
if($_REQUEST['templom']>0 AND ( !isset($_REQUEST['m_id']) OR !isset($_REQUEST['m_op'])))  {
	$m_id = 26;
	$m_op = 'view';
	$_REQUEST['tid'] = $_REQUEST['templom'];
} elseif(isset($_REQUEST['m_id']) AND is_numeric($_REQUEST['m_id'])) {
	$m_id=$_REQUEST['m_id'];
} else {
	$m_id=26;
}
           

//Modul beöltése
$lekerdez=mysql_query("select fajlnev as fajl,zart,jogkod from modulok where id='".$m_id."' and ok='i' AND fajlnev != '' LIMIT 1;");
$module=mysql_fetch_assoc($lekerdez);
if($module == array() OR !is_file("moduls/".$module['fajl'].".php") ) 
    $hiba='A választott modul behívása sikertelen.';
if($module['zart'] == 1 and !$user->loggedin)
    $hiba='A hozzáféréshez be kell jelentkezni.';
if(!$user->checkRole($module['jogkod']) )
    $hiba='A hozzáféréshez további jogosultságokra volna szükség.';   

if(!$hiba) {
    if(!$m_op) $m_op=$_REQUEST['m_op'];
    if(empty($m_op)) $m_op='index';
		
    //TODO: a templates2 teljes készítése után törölhető, addig az admin oldalak legyenek alap design
    if(preg_match('/\/admin_/i', $module['fajl'])) {
        $loader = new Twig_Loader_Filesystem('templates');
        $twig = new Twig_Environment($loader); // cache?        
    }

    if(!include_once("moduls/".$module['fajl'].".php"))
        $hiba ='HIBA! A szükséges fájl nem hívható be! ('.$module['fajl'].')';
}

//TODO: ez mi ez?
if($user->checkRole('"any"')) { $vars['chat'] = chat_vars(); }	


if($hiba) {
    //TODO: Kedvesebb hibaüzenetet adjon már, légyszi.
    echo $hiba;
} else
    print design($vars);


?>
