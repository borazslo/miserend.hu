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
                        ->orderBy('attempts', 'ASC')->orderBy('deadline_at', 'DESC');
    }

    public function initialize() {
        $jobsToSave = [
            ['\Eloquent\Cron', 'initialize', '1 min'],
            ['\Html\Cron', 'oldHourly', '1 hour'],
            ['\Html\Cron', 'oldWeekly', '1 week'],
            ['\Api\Sqlite', 'cron', '1 day'],
            ['\ExternalApi\OverpassApi', 'updateUrlMiserend', '1 day'],
            ['\ExternalApi\OverpassApi', 'clearOldCache', '1 day'],
            ['\KeywordShortcut', 'updateAll', '1 day'],
            ['\Distance', 'updateSome', '15 min']
        ];
        foreach ($jobsToSave as $jobToSave) {
            echo $jobToSave[0];
            $job = \Eloquent\Cron::firstOrCreate(['class'=>$jobToSave[0],'function'=>$jobToSave[1]]);
            $job->frequency = $jobToSave[2];
            $job->save();
        }
    }

}
