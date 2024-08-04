<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Apitest extends Html {

    public function __construct() {
        parent::__construct();
        $this->setTitle('API tesztelés');

        global $user;
        if (!$user->isadmin) {
            addMessage("Hozzáférés megtagadva!", "danger");
            $this->redirect('/');
        }

        
   
      
        
    }
    
   
   
}
