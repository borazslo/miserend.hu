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

        global $user;
        $this->user = $user;
        if ($type) {            
            $this->mail->render('remarkfeedback_' . $type, (array) $this );
        } else {
            $this->mail->render('remarkfeedback' . $type, (array) $this );            
        }
        
    }

    public function send() {
        parent::send();
        
        $this->remark->appendComment("email küldve: " . $this->mail->type." (".$this->mail->id.")");
        $this->remark->save();
    }
    
    function checkPermission() {
        /* Csak templomgazda küldhet ki emailt */
        if (!$this->remark->church->writeAccess) {
            addMessage("Hiányzó jogosultság. Elnézést.", "danger");
            return false;
        }
        
        return true;
        
    }

}
