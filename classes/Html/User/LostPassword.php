<?php

namespace Html\User;

class LostPassword extends \Html\Html {

    public function __construct() {
        $this->input['lostPassword'] = \Request::simpleText("lostPassword");
        if ($this->input['lostPassword'] == 'sendMeMyPassword') {
            $this->input['username'] = \Request::TextRequired('username');
            $this->input['email'] = \Request::TextRequired('email');

            if ($this->recoverUser()) {
                $this->newpassword = $this->recoveredUser->generatePassword();
                $this->recoveredUser->newPassword($this->newpassword);
                $this->sendNewPasswordMail();
            }
        }
    }

    function recoverUser() {
        $userByNev = new \User($this->input['username']);
        $userByMail = new \User($this->input['email']);
        if ($userByMail->uid != $userByNev->uid) {
            addMessage('A megadott adatok alapján nem találtunk felhasználót.', 'danger');
            return false;
        }
        $this->recoveredUser = $userByNev;
        return true;
    }

    function sendNewPasswordMail() {
        $email = new \Mail();
        $email->subject = "Jelszó emlékeztető - Virtuális Plébánia Portál";

        $email->content = "Kedves " . $this->recoveredUser->username . "!<br/><br/>";
        $email->content.="\n\nKérésedre küldjük a bejelentkezéshez szükséges újjelszót:";
        $email->content.="\n" . $this->newpassword . "<br/><br>";
        $email->content.="Kérjük mihamarabb változtasd meg a jelszót.<br/><br/>";
        $email->content.="\n\nVPP \nhttp://www.plebania.net";

        $email->to = $this->recoveredUser->email;
        $email->send();

        addMessage("Az új jelszót elküldtük a regisztrált emailcímre. Kérjük lépjen be, és mihamarabb módosítsa.", 'success');
    }

}
