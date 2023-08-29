<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Service_hours extends Api {

    public function run() {
        parent::run();

        $this->return = [];

        $churches = \Eloquent\Church::limit(10000)->get();
        foreach($churches as $church ) {
            $serviceHours = new \ServiceHours();
            $serviceHours->loadMasses($church->id,['skipvalidation']);
            
            $this->return[] = [
                'church_id' => $church->id,
                'service_hours' => $serviceHours->string
            ];            
        }
        
    }

}