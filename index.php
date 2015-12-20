<?php
//For compatibility
if(isset($_REQUEST['m_id']) AND $_REQUEST['m_id'] == 17) {
    $_REQUEST['q'] = "static";
    $_REQUEST['name'] = $mapping[$_GET['fm']];
} else if(isset($_REQUEST['m_id']) AND $_REQUEST['m_id'] == 29) {
    $_REQUEST['q'] = 'user/maintainedchurches';    
} else if(isset($_REQUEST['m_id']) AND $_REQUEST['m_id'] == 26) {
    if($_REQUEST['m_op'] == 'keres') {
         if (isset($_REQUEST['misekereses'])) {
             $_REQUEST['q'] = 'searchresultsmasses';
         }            
        else {
            $_REQUEST['q'] = 'searchresultschurches';
        }
    } elseif($_REQUEST['m_op'] == 'view') {
        $_REQUEST['q'] = 'church';
    } else {
        $_REQUEST['q'] = 'home';
    }
 } else if(isset($_REQUEST['templom']))  {        
     $_REQUEST['q'] = 'church';
     $_REQUEST['tid'] = $_REQUEST['templom'];
} else if(!isset($_REQUEST['m_id']) AND !isset($_REQUEST['q']) AND !isset($_REQUEST['templom'])) {
    $_REQUEST['q'] = 'home';
}    

include("load.php");

//TODO: ez itt nem túl barátságok dolog
$action = \Request::Text('q');
switch ($action) {        
    case 'remarks':
        $html = new \Html\Remark();        
        break;

    case 'help':
        $html = new \Html\Help();        
        break;
 
    case 'static':
        $html = new \Html\StaticPage();        
        break;
    
    case 'user/maintainedchurches':
        $html = new \Html\User\MaintainedChurches();
    
    case 'home':
        $html = new \Html\Home();        
        break;

    case 'church':
        $html = new \Html\Church();        
        break;
    
    case 'searchresultschurches':
        $html = new \Html\SearchResultsChurches();
        break;

    case 'searchresultsmasses':
        $html = new \Html\SearchResultsmasses();
        break;
    
    default:
        @include $action . ".php";
        break;
}    

if  ($html) {
    $html->messages = getMessages();
    $html->render();    
    echo $html->html;
    exit;
}

if(!isset($_GET['q']))  {


//Beállított modulok
    if ($_REQUEST['templom'] > 0 AND ( !isset($_REQUEST['m_id']) OR ! isset($_REQUEST['m_op']))) {
        $m_id = 26;
        $m_op = 'view';
        $_REQUEST['tid'] = $_REQUEST['templom'];
    } elseif (isset($_REQUEST['m_id']) AND is_numeric($_REQUEST['m_id'])) {
        $m_id = $_REQUEST['m_id'];
    } else {
        $m_id = 26;
    }


    /*
      Module, azaz fő anyag betöltése
     */    
    if (!$m_op)
        $m_op = $_REQUEST['m_op'];
    if (empty($m_op))
        $m_op = 'index';

    switch ($m_id) {      
        case 27:
            if (! $user->loggedin) {
                throw new Exception('A hozzáféréshez be kell jelentkezni.');
            }
            include_once("moduls/admin_miserend.php");
            break;
        case 28:
            include_once("moduls/regisztracio.php");

            break;        
        default:
            break;
    }
    
}

if (isset($tartalom)) {
    if (is_array($tartalom)) {
        $vars = array_merge($vars, $tartalom);
    } else {
        $vars['content'] = $tartalom;
    }
}


//TODO: ezminekez
if (!isset($vars['pageTitle']))
    $vars['pageTitle'] = 'VPP - miserend';
if (isset($titlekieg))
    $vars['pagetitle'] = preg_replace("/^( - )/i", "", $titlekieg) . " | " . $vars['pagetitle'];


$adminmenuitems = array();
//Admin menü összeállítása   
if ($user->checkRole("'any'")) {
    $adminmenuitems = array(
        array(
            'title' => 'Miserend', 'url' => '?m_id=27', 'permission' => 'miserend', 'mid' => 27,
            'items' => array(
                array('title' => 'új templom', 'url' => '?m_id=27&m_op=addtemplom', 'permission' => ''),
                array('title' => 'módosítás', 'url' => '?m_id=27&m_op=modtemplom', 'permission' => ''),
                array('title' => 'egyházmegyei lista', 'url' => '?m_id=27&m_op=ehmlista', 'permission' => 'miserend'),
                array('title' => 'kifejezések és dátumok', 'url' => '?m_id=27&m_op=events', 'permission' => 'miserend'),
            )
        ),
        array(
            'title' => 'Igenaptár', 'url' => '?m_id=31', 'permission' => 'igenaptar', 'mid' => 31,
            'items' => array(
                array('title' => 'naptár beállítása', 'url' => '?m_id=31&m_op=naptar', 'permission' => 'igenaptar'),
                array('title' => 'gondolatok', 'url' => '?m_id=31&m_op=gondolatok', 'permission' => 'igenaptar'),
                array('title' => 'szentek', 'url' => '?m_id=31&m_op=szentek', 'permission' => 'igenaptar'),
            )
        ),
        array(
            'title' => 'Felhasználók', 'url' => '?m_id=21', 'permission' => 'user', 'mid' => 21,
            'items' => array(
                array('title' => 'új felhasználó', 'url' => '?m_id=28&m_op=edit', 'permission' => 'user'),
                array('title' => 'módosítás', 'url' => '?m_id=28&m_op=list', 'permission' => 'user'),
            )
        ),
    );

    $adminmenuitems = clearMenu($adminmenuitems);
}


//Egyházmegyei szerkesztők menüje
if (count($user->responsible['diocese']) > 0 AND ! $user->checkRole('miserend')) {
    $diocesemenuitems = array(
        array(
            'title' => 'Templomok', 'url' => '?m_id=27', 'mid' => 27,
            'items' => array(
                array('title' => 'módosítás', 'url' => '?m_id=27&m_op=modtemplom', 'permission' => ''),
            )
        ),
    );
    $adminmenuitems = array_merge($diocesemenuitems, $adminmenuitems);
}
$vars['adminmenu'] = $adminmenuitems;

//Campaing betöltése
$vars['campaign'] = updatesCampaign();


//Saját templomok blokkjához
if ($user->loggedin AND ! $user->checkRole('miserend'))
    $vars['mychurches'] = feltoltes_block();
// Chat betöltése, ha lehet
if ($user->checkRole('"any"'))
    $vars['chat'] = chat_load();


// Felhasználük 
$vars['user'] = $user;
if ($user->checkRole('miserend'))
    $vars['user']->isadmin = true;

// Üzenetek betöltése    
$vars['messages'] = getMessages();


//Template fájl megtalálása
if (!isset($vars['template'])) {
    $template = "layout.twig";
} elseif (preg_match('/\.([a-zA-Z]{1,4})$/i', $vars['template'])) {
    $template = $vars['template'];
} else
    $template = $vars['template'] . ".twig";

//És a világ kiírása
print $twig->render($template, $vars);
?>
