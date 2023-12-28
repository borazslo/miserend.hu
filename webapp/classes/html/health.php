<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Health extends Html {

    public function __construct() {
        parent::__construct();
        $this->setTitle('Miserend.hu állapotáról');
		
		$this->cronjobs = \Eloquent\Cron::orderBy('deadline_at','DESC')->get()->toArray();
		
		$this->infos = [
			['server', $_SERVER['SERVER_SOFTWARE']],
			['php verzió', phpversion()],
			['php extensions', implode(', ',get_loaded_extensions())]						
		];
		
		
		return;
			
    }
}