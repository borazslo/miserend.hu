<?php

include("load.php");

//TODO: ez itt nem túl barátságok dolog
if (isset($_GET['q'])) {
    include $_GET['q'] . ".php";
} else {


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
    $query = "select fajlnev as fajl,zart,jogkod from modulok where id='" . $m_id . "' and ok='i' AND fajlnev != '' LIMIT 1;";
    $lekerdez = mysql_query($query);
    $module = mysql_fetch_assoc($lekerdez);
    if ($module == array() OR ! is_file("moduls/" . $module['fajl'] . ".php"))
        $hiba = 'A választott modul behívása sikertelen.';
    if ($module['zart'] == 1 and ! $user->loggedin)
        $hiba = 'A hozzáféréshez be kell jelentkezni.';
// Az admin_miserend kivétel, mert ahhoz megfelelő normálék is hozzáférhetnek!
    if (!$user->checkRole($module['jogkod'] AND $m_id != 27))
        $hiba = 'A hozzáféréshez további jogosultságokra volna szükség.';

    if (!$hiba) {
        if (!$m_op)
            $m_op = $_REQUEST['m_op'];
        if (empty($m_op))
            $m_op = 'index';

        if (!include_once("moduls/" . $module['fajl'] . ".php"))
            $hiba = 'HIBA! A szükséges fájl nem hívható be! (' . $module['fajl'] . ')';
    }
    if ($hiba) {
        echo $hiba;
        exit;
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

//TODO: ezmiez
$emaillink_lablec = "<A HREF=\"javascript:linkTo_UnCryptMailto('ocknvq%3CkphqBokugtgpf0jw');\" class=emllink>info<img src=img/kukaclent.gif align=absmiddle border=0>miserend.hu</a>";


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
$vars['campaign'] = ""; //updatesCampaign();


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
