<?php

namespace App\Html\Church;

class ChangeHolders extends \App\Html\Html {
    
    public function __construct($path) {
        $where = [];
        $data = [];
        
        if (isset($path[0])) {
            $where['church_id'] = $path[0];
        } else {
            $where['church_id'] = \App\Request::IntegerRequired('tid');
        }
        
        $where['user_id'] = \App\Request::Integer('uid');
        $confirmation = \App\Request::Simpletext('confirmation');
        
        if(!$where['user_id']) {
            if($confirmation) {
                // Boldogok vagyunk
                return;
            } else {
                throw new \Exception("Required 'uid' is required.");
            }            
        }
        
        $data['status'] = \App\Request::InArrayRequired('access', ['allowed','denied','revoked','asked']);
        $description = \App\Request::Text('description');
        if($description != '') {
            $data['description'] = $description;
        }
               
        global $user;   
        if ( $user->uid == $where['user_id'] AND $data['status'] == 'asked' )  {
                        
            if($confirmation == 'needed') {
            
                $churchHolder = \App\Model\ChurchHolder::where('user_id',$where['user_id'])->where('church_id',$where['church_id'])->first();
                if(!$churchHolder) {
                    $churchHolder = new \App\Model\ChurchHolder(array_merge($where,$data));
                }
                $this->holder = $churchHolder;
                           
            } else {            
                $churchHolder = \App\Model\ChurchHolder::updateOrCreate($where,$data);
                $churchHolder->sendEmails();
                addMessage('A kérést köszönettel elmentettük.', 'info');
                return $this->redirect('/templom/'.$where['church_id']);
            }
        
        } else if($user->checkRole('miserend')) {
            
           $churchHolder = \App\Model\ChurchHolder::updateOrCreate($where,$data);
           $churchHolder->sendEmails();
           addMessage('A változtatást sikeresen elmentettük.', 'info');
           return $this->redirect('/templom/'.$where['church_id'].'/edit');
            
        } else {
            
                throw new \Exception('Hiányzó jogosultság');
        }
  
    }
            
}

