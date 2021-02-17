<?php

namespace Html\Ajax;

class SwitchReliable extends Ajax {

    public function __construct() {
        $rid = \Request::IntegerRequired('rid');
        $reliable = \Request::InArrayRequired('reliable', array('i', 'n', '?', 'e'));
        
        $remark = \Eloquent\Remark::find($rid);

        global $user;
        if (!$user->checkRole('miserend') and ! ($user->username == $remark->church->letrehozta ) and ! $user->checkRole('ehm:' . $remark->church->egyhazmegye)) {
            throw new Exception("Hiányzó jogosultság.");            
        }
        $remark->megbizhato = $reliable;
        $remark->save();
        
        header("Content-Type: text/plain"); 
        echo 'ok';
    }

}
