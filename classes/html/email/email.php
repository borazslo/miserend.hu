<?php

namespace Html\Email;

class Email extends \Html\Html {

    public function __construct($path) {
        global $user;
        $this->user = $user;
        if (!$this->user->checkRole('"any"')) {
            throw new \Excpetion("Nincs jogod levélküldéshez.");
        }

        $this->mail = new \Mail();
        if (isset($_REQUEST['send'])) {
            $this->send();
            $this->template = 'layout_simpliest.twig';
            $this->content = "Köszönjük, elküldtük.";
            unset($this->mail);
        } else {
            $this->preparePage($path);
        }
    }

    public function send() {
        $this->mail->to = \Request::TextRequired('email');
        $this->mail->subject = \Request::TextRequired('subject');
        $this->mail->content = \Request::TextRequired('text');
        $this->mail->type = \Request::Simpletext('type');
        if (!$this->mail->send()) {
            addMessage('Nem sikerült elküldeni az emailt. Bocsánat.', 'danger');
        }
    }

    public function preparePage($path) {
        
    }

}
