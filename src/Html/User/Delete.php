<?php

namespace App\Html\User;

class Delete extends \App\Html\Html {

    public function __construct() {

        $this->title = 'Felhasználó törlése';
        $this->template = 'layout.twig';
        $this->input['uid'] = \App\Request::IntegerRequired('uid');

        $this->user2delete = new \App\User($this->input['uid']);
        if ($this->user2delete->uid == 0) {
            addMessage("Nincs ilyen felhasználó!", danger);
            return;
        }

        $this->input['confirmation'] = \App\Request::SimpleText('confirmation');
        if (!$this->input['confirmation']) {
            $this->askConfirmation();
            return;
        } else {
            $this->delete();
        }
    }

    function delete() {
        global $user;
        if (!$user->checkRole('user')) {
            throw new \Exception("Hiányzó jogosultság miatt nem lehetséges a törlése!");
        }
        $this->user2delete->delete();
        header("Location: /user/catalogue");
    }

    function askConfirmation() {
        $kiir = "\n<span class=kiscim>Biztosan törölni akarod a következő felhasználót?</span>";
        $kiir.="\n<br><br><span class=alap>" . $this->user2delete->username . " (" . $this->user2delete->nev . ")</span>";
        $kiir.="<br><br><a href='/user/" . $this->user2delete->uid . "/delete?confirmation=confirmed' class=link>Igen</a> - <a href='/user/" . $this->user2delete->uid . "/edit' class=link>NEM</a>";

        $this->content = $kiir;
    }

}
