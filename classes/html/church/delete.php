<?php

namespace Html\Church;

class Delete extends \Html\Html {

    public function __construct($path) {
        global $user;
        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod a templomot törölni.');
        }

        $this->input['tid'] = $path[0];

        $this->church2delete = \Eloquent\Church::find($this->input['tid']);
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
        $this->church2delete->remarks()->delete();
        $this->church2delete->delete();
        header("Location: /templom/list");
    }

    function askConfirmation() {
        // church/delete.twig
    }

}
