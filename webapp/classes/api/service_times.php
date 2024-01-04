<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Service_times extends Api {

    public function run() {
        parent::run();

        $this->return = [];

        $churches = \Eloquent\Church::limit(10000)->get();
        foreach($churches as $church ) {
            $serviceTimes = new \ServiceTimes();
            $serviceTimes->loadMasses($church->id,['skipvalidation']);
            
            $this->return[] = [
                'church_id' => $church->id,
                'service_hours' => $serviceTimes->string
            ];            
        }
        
    }

}