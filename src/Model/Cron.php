<?php

namespace App\Model;

class Cron extends \Illuminate\Database\Eloquent\Model {

    protected $fillable = array('class', 'function');

    public function success() {
        $this->attempts = 0;
        $this->lastsuccess_at = date('Y-m-d H:i:s');
        $this->renewDeadline();
        $this->save();
    }

    public function renewDeadline() {
        $this->deadline_at = date('Y-m-d H:i:s', strtotime('now +' . $this->frequency));
    }

    public function scopeNextJobs($query) {
        return $query->where('deadline_at', '<', date('Y-m-d H:i:s'))
                        ->where(function($query) {
                            $query->where('attempts', '<', 10)
                            ->orWhere('updated_at', '<', date('Y-m-d H:i:s', strtotime('-12 hour')));
                        })
                        ->orderBy('attempts', 'ASC')->orderBy('deadline_at', 'ASC');
    }

    public function initialize() {
        $jobsToSave = [
            [\App\Model\Cron::class, 'initialize', '1 week'],
           ['\App\Message', 'clean', '1 hour'],
            ['\App\api\Sqlite', 'cron', '1 day'],
            ['\App\externalapi\OverpassApi', 'updateUrlMiserend', '1 day'],
            ['\App\externalapi\OverpassApi', 'clearOldCache', '1 day'],
            ['\App\OSM', 'checkBoundaries', '5 min'],
            ['\App\OSM', 'checkUrlMiserend', '1 day'],
            ['\App\KeywordShortcut', 'updateAll', '1 day'],
            ['\App\Distance', 'updateSome', '15 min'],
            ['\App\Token', 'cleanOut', '2 hours'],
            ['\App\Photos', 'cron', '1 week'],
            ['\App\Crons','gorogkatolizalas','1 week'],
			['\App\Crons','generateMassTolIgTmp','1 week']
			
        ];
        foreach ($jobsToSave as $jobToSave) {
            $job = self::firstOrCreate(['class' => $jobToSave[0], 'function' => $jobToSave[1]]);
            $job->frequency = $jobToSave[2];
            $job->save();
        }
    }
    
    function run() {
        $className = $this->class;
        $functionName = $this->function;
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
        $this->success();
    }

}
