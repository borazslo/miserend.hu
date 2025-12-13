<?php

namespace Html;


class Cron extends Html {

    public $template = 'layout_empty.twig';

    public function __construct($path = false) {
        set_time_limit('300');
        ini_set('memory_limit', '512M');

        if($jobId = \Request::Integer('cron_id')) {
            $nextjob = \Eloquent\Cron::find($jobId);
			if (!$nextjob) return;
        }
		
		else {
            $jobs = \Eloquent\Cron::nextJobs()->get();
			
			if(count($jobs) < 1) 
				return;
			
			foreach($jobs as $job) {
				if( 
					( $job->from == '' AND $job->until == '' ) or
					( strtotime($job->from) < time() AND time() < strtotime($job->until) )
				) {
						// Itt az idő vagy nem kell idő
						$nextjob = $job; 
						break;
				} 				
			}			
		}
		if(!$nextjob) return;
		
		$job = $nextjob;
        $job->attempts++;
        $job->save();

        try {
            $this->runJob($job);
        } catch (\Exception $exception) {
            $this->error = true;
            echo "<strong>" . $job->class . "->" . $job->function . "() futtatása sikertelen.</strong>\n";
            $this->printExceptionVerbose($exception);
        }

        if (!isset($this->error)) {
            $job->success();
        }
    }

    function runJob($job) {
        $className = $job->class;
        $functionName = $job->function;
        if (class_exists($className)) {
            $object = new $className();
        } else {
            throw new \Exception("Class '$className' does not exists.");
        }
        if (method_exists($object, $functionName)) {
            $object->$functionName();
        } else {
            throw new \Exception("Function " . $className . "->" . $functionName . "() does not exists.");
        }
    }
	    

}
