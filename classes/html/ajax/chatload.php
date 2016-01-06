<?php

namespace Html\Ajax;

class ChatLoad extends Ajax {

    public function __construct() {
        global $user;
        if (!$user->checkRole("'any'")) {
            $this->content = json_encode(array('result' => 'error', 'text' => 'Hiányzó jogosultság'));
            return;
        }
        $date = date('Y-m-d H:i:s', strtotime($_REQUEST['date']));
        if (!isset($_REQUEST['rev']))
            $comments = chat_getcomments(array('last' => $date));
        else
            $comments = chat_getcomments(array('first' => $date));

        $alert = 0;
        foreach ($comments as $k => $i) {
            global $twig;
            $comments[$k]['html'] = $twig->render('chat/chatComment.twig', array('comment' => $i));
            if ($i['user'] != $user->login) {
                $alert++;
            }
        }

        $this->content = json_encode(array('result' => 'loaded', 'comments' => $comments, 'new' => count($comments), 'alert' => $alert));
    }

}
