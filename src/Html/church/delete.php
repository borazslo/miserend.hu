<?php

namespace App\Html\Church;

class Delete extends \App\Html\Html {

    public function __construct($path) {
        global $user;
        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod a templomot törölni.');
        }

        $this->input['tid'] = $path[0];

        $this->church2delete = \App\Model\Church::find($this->input['tid']);
        if ($this->church2delete->id == 0) {
            addMessage("Nincs ilyen templom!", danger);
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
        
        $comment = \App\Request::Text('comment');
        if($this->church2delete->adminmegj != '')
            $this->church2delete->adminmegj .= "\n";
        $this->church2delete->adminmegj .= "Törölte ".$user->login;
        if($comment)
            $this->church2delete->adminmegj .= ": ".$comment;        
        $this->church2delete->ok = 'n';
        $this->church2delete->log .= "\nDel: " . $user->login . " (" . date('Y-m-d H:i:s') . ")";
        $this->church2delete->save();
                
        $this->church2delete->delete();
        addMessage('A templomot sikeresen töröltük.','info');
        header("Location: /templom/list");
    }

    function askConfirmation() {
        // church/delete.twig
    }

}
