<?php

namespace Html\Church;

class Delete extends \Html\Html {

    public function __construct($path) {
        global $user;
        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod a templomot törölni.');
        }

        $this->title = 'Templom és miserendje törlése';
        $this->template = 'layout.twig';
        $this->input['tid'] = $path[0];
        
        $this->church2delete = new \Church($this->input['tid']);
        if ($this->church2delete->id == 0) {
            addMessage("Nincs ilyen templom!", danger);
            return;
        }

        $this->input['confirmation'] = \Request::SimpleText('confirmation');
        if (!$this->input['confirmation']) {
            $this->askConfirmation();
            return;
        } else {
            $this->delete();
        }
    }

    function delete() {
        $this->church2delete->delete();
        header("Location: /templom/list");
    }

    function askConfirmation() {
        global $linkveg, $m_id, $user;

        $tid = $this->input['tid'];

        $kiir.="\n<span class=kiscim>Biztosan törölni akarod a következő templomot?<br><font color=red>FIGYELEM! A kapcsolódó misék és képek is törlődnek!</font></span>";

        $query = "select nev from templomok where id='$tid'";
        list($cim) = mysql_fetch_row(mysql_query($query));
        if (!empty($cim)) {
            $kiir.="\n<br><br><span class=alap><b><i>$cim</i></b></span>";

            $kiir.="<br><br><a href='/templom/$tid/delete?confirmation=confirmed' class=link>Igen</a> - <a href='/templom/$tid/edit' class=link>NEM</a>";
        } else {
            $kiir.="<br><br><span class=hiba>HIBA! Ilyen templom nincs!</span>";
        }

        $this->content = $kiir;

    }

}
