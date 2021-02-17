<?php

namespace Html\Email;

class RemarkFeedback extends Email {

    public function __construct($path) {
        $this->rid = \Request::Integer('rid');
        if(!$this->rid) {
            $this->rid = $path[0];
            if (!is_numeric($this->rid)) {
                throw new \Exception("Helytelen észrevétel azonosító.");
            }
        }
        $this->remark = \Eloquent\Remark::find($this->rid);
        parent::__construct($path);        
    }
    
    public function preparePage($path) {
        $this->setTitle("Észrevételre reagálás");
      
        $this->church = $this->remark->church;
        $this->mail->to = $this->remark->email;

        if (isset($path[1])) {
            $type = $path[1];
        } else {
            $type = \Request::Text('type');
        }

        if ($type) {
            global $user;
            $this->mail->render('remarkfeedback_' . $type, (array) $this );
        } else {
            $this->mail->type = "remarkfeedback_custom";
            $this->mail->body = "\n\n\n\n<strong>Üdvözlettel:</strong>\n";
            
            $this->mail->body .= $this->user->nev.", önkéntes";          
        }
        
    }

    public function send() {
        parent::send();
        $this->remark->appendComment("email küldve: " . $this->mail->type);
        $this->remark->save();
    }
    
    function checkPermission() {
        /* Csak templomgazda küldhet ki emailt */
        global $user;
        $this->user = $user;
        if (!$user->checkRole('miserend') and ! ($user->username == $this->remark->church->letrehozta ) and ! $user->checkRole('ehm:' . $this->remark->church->egyhazmegye)) {
            addMessage("Hiányzó jogosultság. Elnézést.", "danger");
            return false;
        }
        
        return true;
        
    }

}
