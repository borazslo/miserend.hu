<?php

namespace Eloquent;

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
            ['\Eloquent\Cron', 'initialize', '1 week'],
            ['\Message', 'clean', '1 hour'],
            ['\Html\Cron', 'oldWeekly', '1 week'],
            ['\Api\Sqlite', 'cron', '1 day'],
            ['\ExternalApi\OverpassApi', 'updateUrlMiserend', '1 day'],
            ['\ExternalApi\OverpassApi', 'clearOldCache', '1 day'],
            ['\OSM', 'checkBoundaries', '5 min'],
            ['\OSM', 'checkUrlMiserend', '1 day'],            
            ['\KeywordShortcut', 'updateAll', '1 day'],
            ['\Distance', 'updateSome', '15 min'],
            ['\Token', 'cleanOut', '2 hours'],
            ['\Photos', 'cron', '1 week']
        ];
        foreach ($jobsToSave as $jobToSave) {
            $job = \Eloquent\Cron::firstOrCreate(['class' => $jobToSave[0], 'function' => $jobToSave[1]]);
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
