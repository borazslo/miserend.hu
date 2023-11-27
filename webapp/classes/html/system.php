<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class System extends Html {

    public function __construct() {
        parent::__construct();
        $this->setTitle('System');
		        
        global $user;
        
        if (!$user->isadmin) {
            addMessage("Hozzáférés megtagadva!", "danger");
            $this->redirect('/');
        }
    
		
		global $config;
		$this->content = "<h3>`$ config`</h3><pre>".print_r($config,1)."</pre>";
        		
		$this->template = 'layout.twig';
		
		
		
    }
}