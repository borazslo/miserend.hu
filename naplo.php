<?php

$kidob = "Érvénytelenség. Kérjük, zárjon be.<Script languate=javascript> close(); </script>";

include("load.php");

function teendok($tid) {
    $_REQUEST['op'] = 'list';
    $_REQUEST['tid'] = $tid;
    $html = new \Html\Remark();
    $html->render();
    echo $html->html;
    exit;
}

function email() {
    global $twig, $user;

    $textvars = array();
    if (!is_numeric($_REQUEST['rid']))
        die('Tök helytelen azonosító.');
    $remark = $vars['remark'] = new Remark($_REQUEST['rid']);
    $vars['church'] = $textvars['church'] = getChurch($remark->tid);
    $vars['type'] = $_REQUEST['type'];
    $textvars['remark'] = $remark;
    $textvars['user'] = $user;


    switch ($_REQUEST['type']) {

        case 'koszonet':
            $vars['text'] = $twig->render('email_feedback_koszonet.twig', $textvars);
            break;

        case 'plebaniara':
            $vars['text'] = $twig->render('email_feedback_plebaniara.twig', $textvars);
            break;

        case 'android':
            $vars['text'] = $twig->render('email_feedback_android.twig', $textvars);
            break;

        default:
            $vars['text'] = '';
            break;
    }

    $vars['content'] = $content;

    echo $twig->render('naplo_email.twig', $vars);

    echo $header . $content . $footer;
}

function sendemail() {
    if (isset($_REQUEST['clear'])) {
        $remark = new Remark($_REQUEST['rid']);
        teendok($remark->tid);
    }

    $mail = new Mail();
    $mail->to = $_REQUEST['email'];
    $mail->content = nl2br($_REQUEST['text']);
    $mail->type = "eszrevetel_" . $_REQUEST['type'];
    if (!isset($_REQUEST['subject']) OR $_REQUEST['subject'] == '')
        $_REQUEST['subject'] = "Miserend";
    $mail->subject = $_REQUEST['subject'];

    if (!$mail->send())
        addMessage('Nem sikerült elküldeni az emailt. Bocsánat.', 'danger');

    $remark = new Remark($_REQUEST['rid']);
    $remark->addComment("email küldve: " . $mail->type);

    teendok($remark->tid);
}

switch ($_REQUEST['op']) {

    case 'sendemail':
        sendemail();
        break;

    case 'email':
        email();
        break;

    default:
        teendok($_REQUEST['id']);
        break;
}
?>
