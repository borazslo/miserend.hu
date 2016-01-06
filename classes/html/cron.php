<?php

namespace Html;

class Cron extends Html {

    public $template = 'layout_empty.twig';

    public function __construct($path = false) {
        set_time_limit('300');
        ini_set('memory_limit', '512M');

        $job = \Eloquent\Cron::nextJobs()->first();

        if (!$job) {
            echo "Nincs futtatandó feladat.";
            return;
        }

        $job->attempts++;
        $job->save();
        try {
            echo "<strong>" . $job->class . "->" . $job->function . "() futtatása ....</strong>\n";
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

    function oldHourly() {
        clearoutTokens();
        clearoutMessages();
    }

    function oldWeekly() {
        #clearoutVolunteers();
        #assignUpdates();

        updateImageSizes();
        generateMassTmp();

        updateCleanMassLanguages();
        updateGorogkatolizalas();
        updateDeleteZeroMass();
        updateComments2Attributes();
        //not so fast!
        updateAttributesOptimalization();
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
