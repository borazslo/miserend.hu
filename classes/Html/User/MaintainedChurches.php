<?php

namespace Html\User;

class MaintainedChurches extends \Html\Html {

    public function __construct() {
        $this->setTitle("Módosítható templomok és miserendek");
        $this->title = "Módosítható templomok és miserendek";
        $this->template = "User/MaintainedChurches.twig";

        global $user;
        if (!is_array($user->responsible['church'])) {
            addMessage("Nincs olyan templom, amit módosíthatnál.", 'info');
            return false;
        }
        foreach ($user->responsible['church'] as $tid) {
            try {
                $this->churches[$tid] = new \Church($tid);
                //TODO: objectify
                $this->churches[$tid]->jelzes = getRemarkMark($tid);
            } catch (\Exception $e) {
                addMessage($e->getMessage(), "info");
            }
        }

        $this->columns2 = true;
    }

}
