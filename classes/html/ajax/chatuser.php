<?php

namespace Html\Ajax;

class ChatUser extends Ajax {

    public function __construct() {
        global $user;


        if (!$user->checkRole("'any'")) {
            $this->content = json_encode(array('result' => 'error', 'text' => 'Hiányzó jogosultság'));
            return;
        }
        $text = chat_getusers('html');
        $this->content = json_encode(array('result' => 'loaded', 'text' => $text));
    }

}
