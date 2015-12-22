<?php

include("load.php");

allowOldUrls();

$action = \Request::Text('q');
switch ($action) {
    case 'remarks':
        $html = new \Html\Remark();
        break;

    case 'help':
        $html = new \Html\Help();
        break;

    case 'staticpage':
        $html = new \Html\StaticPage();
        break;

    case 'user/maintainedchurches':
        $html = new \Html\User\MaintainedChurches();
        break;

    case 'user/list':
        $html = new \Html\User\Catalogue();
        break;

    case 'home':
        $html = new \Html\Home();
        break;

    case 'api':
        $html = new \Html\Api();
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

if ($html) {
    $html->render();
    echo $html->html;
}

//OLD STUFF
if (!isset($_REQUEST['q'])) {
    $m_id = $_REQUEST['m_id'];
    $m_op = $_REQUEST['m_op'];

    switch ($m_id) {
        case 27:
            if (!$user->loggedin) {
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

    if (isset($tartalom)) {
        if (is_array($tartalom)) {
            $vars = array_merge($vars, $tartalom);
        } else {
            $vars['content'] = $tartalom;
        }
    }
    $vars['user'] = $user;
    $template = $vars['template'] . ".twig";
    print $twig->render($template, $vars);
}
?>
