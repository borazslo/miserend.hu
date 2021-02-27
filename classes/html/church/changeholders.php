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
        
        $where['user_id'] = \Request::Integer('uid');
        $confirmation = \Request::Simpletext('confirmation');
        
        if(!$where['user_id']) {
            if($confirmation) {
                // Boldogok vagyunk
                return;
            } else {
                throw new \Exception("Required 'uid' is required.");
            }            
        }
        
        $data['status'] = \Request::InArrayRequired('access', ['allowed','denied','revoked','asked']);                
        $description = \Request::Text('description');
        if($description != '') {
            $data['description'] = $description;
        }
               
        global $user;   
        if ( $user->uid == $where['user_id'] AND $data['status'] == 'asked' )  {
                        
            if($confirmation == 'needed') {
            
                $churchHolder = \Eloquent\ChurchHolder::where('user_id',$where['user_id'])->where('church_id',$where['church_id'])->first();
                if(!$churchHolder) {
                    $churchHolder = new \Eloquent\ChurchHolder(array_merge($where,$data));
                }
                $this->holder = $churchHolder;
                           
            } else {            
                $churchHolder = \Eloquent\ChurchHolder::updateOrCreate($where,$data);
                $churchHolder->sendEmails();
                addMessage('A kérést köszönettel elmentettük.', 'info');
                return $this->redirect('/templom/'.$where['church_id']);
            }
        
        } else if($user->checkRole('miserend')) {
            
           $churchHolder = \Eloquent\ChurchHolder::updateOrCreate($where,$data);
           $churchHolder->sendEmails();
           addMessage('A változtatást sikeresen elmentettük.', 'info');
           return $this->redirect('/templom/'.$where['church_id'].'/edit');
            
        } else {
            
                throw new \Exception('Hiányzó jogosultság');
        }
  
    }
            
}

