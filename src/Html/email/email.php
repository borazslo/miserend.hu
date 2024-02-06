<?php

namespace App\Html\Email;

class Email extends \App\Html\Html {

    public function __construct($path) {
        if(!$this->checkPermission()) {
            throw new \Exception("Nincs jogod levélküldéshez.");
        }

        $this->mail = new \App\Model\Email();
        if (isset($_REQUEST['send'])) {
            $this->send();
            $this->template = 'layout_simpliest.twig';
            $this->body = "Köszönjük, elküldtük.";
            unset($this->mail);
        } else {            
            $this->preparePage($path);
        }
    }

    public function send() {
        $this->mail->to = \App\Request::TextRequired('email');
        $this->mail->subject = \App\Request::TextRequired('subject');
        $this->mail->body = \App\Request::TextRequired('text');
        $this->mail->type = \App\Request::Simpletext('type');
        if (!$this->mail->send()) {
            addMessage('Nem sikerült elküldeni az emailt. Bocsánat.', 'danger');
        }
    }

    public function preparePage($path) {
        $id = \App\Request::Integer('id');
        
        if($id) {
            $this->mail = \App\Model\Email::find($id);
        }      
    }

    function checkPermission() {
        global $user;
        $this->user = $user;
        if (!$this->user->checkRole('"any"')) {
            return false;   
        }
        return true;
    }
    
}
