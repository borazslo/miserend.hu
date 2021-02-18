<?php

namespace Html\Church;

class ChangeHolders extends \Html\Html {
    
    public function __construct($path) {
        $where = [];
        $data = [];
        
        if (isset($path[0])) {
            $where['church_id'] = $path[0];
        } else {
            $where['church_id'] = \Request::IntegerRequired('tid');
        }
        
        $where['user_id'] = \Request::IntegerRequired('uid');
        
        $data['status'] = \Request::InArrayRequired('access', ['allowed','denied','revoked']);                
        $description = \Request::Text('description');
        if($description != '') {
            $data['description'] = $description;
        }
       
        
        global $user;
        if(!$user->checkRole('miserend')) {
            throw new \Exception('Hiányzó jogosultság');
        } 
        
        \Eloquent\ChurchHolder::updateOrCreate($where,$data);
        
        addMessage('A változtatást sikeresen elmentettük.', 'info');
        
        return $this->redirect('/templom/'.$where['church_id'].'/edit');
        
    }
            
}

