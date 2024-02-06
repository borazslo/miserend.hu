<?php

namespace App\Html\User;

class LostPassword extends \App\Html\Html {

    public function __construct() {
        $this->input['lostPassword'] = \App\Request::simpleText("lostPassword");
        if ($this->input['lostPassword'] == 'sendMeMyPassword') {
            $this->input['data'] = \App\Request::TextRequired('data');

            if ($this->recoverUser()) {
                $this->newpassword = $this->recoveredUser->generatePassword();
                $this->recoveredUser->newPassword($this->newpassword);
                $this->sendNewPasswordMail();
            }
        } else {
            $this->input['data'] = \App\Request::Text('data');
        }
        
    }

    function recoverUser() {
        $userByNevOrEmail = new \App\User($this->input['data']);
        if($userByNevOrEmail->uid > 0) {
            $this->recoveredUser = $userByNevOrEmail;
            return true;
        } else {
            addMessage('A megadott adatok alapján nem találtunk felhasználót.', 'danger');
            return false;
        }                     
    }

    function sendNewPasswordMail() {
        $email = new \App\Model\Email();
        $this->recoveredUser->newpwd = $this->newpassword;
        
        $email->render('user_newpassword', $this->recoveredUser);
        $email->send($this->recoveredUser->email);

        addMessage("Az új jelszót elküldtük a regisztrált emailcímre. Kérjük lépjen be, és mihamarabb módosítsa.", 'success');
    }

}
