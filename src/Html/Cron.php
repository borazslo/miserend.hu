<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html;

class Cron extends Html
{
    public $template = 'layout_empty.twig';

    public function __construct($path = false)
    {
        set_time_limit('300');
        ini_set('memory_limit', '512M');

        if ($jobId = \App\Request::Integer('cron_id')) {
            $nextjob = \App\Model\Cron::find($jobId);
            if (!$nextjob) {
                return;
            }
        } else {
            $jobs = \App\Model\Cron::nextJobs()->get();

            if (\count($jobs) < 1) {
                return;
            }

            foreach ($jobs as $job) {
                if (
                    ('' == $job->from && '' == $job->until)
                    || (strtotime($job->from) < time() && time() < strtotime($job->until))
                ) {
                    // Itt az idő vagy nem kell idő
                    $nextjob = $job;
                    break;
                }
            }
        }
        if (!$nextjob) {
            return;
        }

        $job = $nextjob;
        ++$job->attempts;
        $job->save();

        try {
            $this->runJob($job);
        } catch (\Exception $exception) {
            $this->error = true;
            echo '<strong>'.$job->class.'->'.$job->function."() futtatása sikertelen.</strong>\n";
            $this->printExceptionVerbose($exception);
        }

        if (!isset($this->error)) {
            $job->success();
        }
    }

    public function runJob($job)
    {
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
            throw new \Exception('Function '.$className.'->'.$functionName.'() does not exists.');
        }
    }

    /*
     * Hetente lefutó időzített feladatok.
     * NEM ÜZEMEL!
     * Talán törölhető innen minden. Csak innen kezelt itteni függvények ezek
     * De vajon nincs-e szükség olykor ilyen takarítása?
     */
    public function oldWeekly()
    {
        $this->updateCleanMassLanguages();
        $this->updateDeleteZeroMass();
        $this->updateComments2Attributes();
        // not so fast!
        $this->updateAttributesOptimalization();
    }

    public function updateCleanMassLanguages()
    {
        global $config;
        // magyarországi templomok nyelve
        $query = " UPDATE misek LEFT JOIN templomok on misek.tid = templomok.id SET nyelv = NULL  WHERE ( nyelv = 'h0' OR nyelv = 'h') AND templomok.orszag = 12;";
        mysql_query($query);
        if ($config['debug'] > 0) {
            echo 'Magyarországi templomokban alapértelmezetten magyarul misézünk<br/>';
        }
    }

    public function updateAttributesOptimalization()
    {
        global $config;
        // milyen/nyelv optimalizálás (!minden misén átmegy!)
        $c = 0;
        $query = "SELECT * from misek WHERE milyen <> '' OR nyelv <> '' ";
        $result = mysql_query($query);
        while ($row = mysql_fetch_array($result)) {
            $query = "UPDATE misek SET milyen = '".cleanMassAttr($row['milyen'])."', nyelv = '".cleanMassAttr($row['nyelv'])."' WHERE id = ".$row['id'].' LIMIT 1';
            // echo $query."<br/>";
            mysql_query($query);
            ++$c;
        }
        if ($config['debug'] > 0) {
            echo $c.' db milyen/nyelv optimalizálva<br/>';
        }
    }

    public function updateComments2Attributes()
    {
        global $config;
        // megjegyzés -> tulajdonság
        $c = 0;
        $attributes = unserialize(ATTRIBUTES);
        foreach ($attributes as $abbrev => $attribute) {
            $query = "SELECT * from misek WHERE megjegyzes REGEXP '^".$attribute['name']."$' ";
            $result = mysql_query($query);
            while ($row = mysql_fetch_array($result)) {
                if (!preg_match('/(^|,)'.$abbrev.'($|,)/i', $row['milyen'])) {
                    $milyen = $abbrev.','.$row['milyen'];
                } else {
                    $milyen = $abbrev.','.$row['milyen'];
                }
                $query = "UPDATE misek SET megjegyzes = '', milyen = '".$milyen."' WHERE id = ".$row['id'].' LIMIT 1';
                // echo $query."<br/>";
                mysql_query($query);
                ++$c;
            }
        }
        if ($config['debug'] > 0) {
            echo $c.' db megjegyzés tulajdonsággá alakítva<br/>';
        }
    }

    public function updateDeleteZeroMass()
    {
        global $config;

        // Ha csak 00:00:00-k vannak, akkor töröljük azokat is ianktiváljuk a misét
        $query = "SELECT count(misek.id) as misek ,SUM(if(ido = '00:00:00', 1, 0)) AS nullak, tid, misek.id,misek.megjegyzes,templomok.misemegj FROM misek LEFT JOIN templomok ON tid = templomok.id GROUP BY tid;";
        $result = mysql_query($query);
        $c = 0;
        while ($tmp = mysql_fetch_array($result)) {
            if (1 == $tmp['nullak'] && 1 == $tmp['misek']) {
                ++$c;
                if ('' != $tmp['megjegyzes'] && '' == $tmp['misemegj']) {
                    // echo $tmp['tid'].": ".$tmp['megjegyzes']." -::-".$tmp['misemegj']."<br/>";
                    $query = "UPDATE templomok SET misemegj = '".$tmp['megjegyzes']."' WHERE id = ".$tmp['tid'].' LIMIT 1';
                    // echo $query."<br/>";
                    mysql_query($query);
                }
                $query = 'UPDATE templomok SET  miseaktiv = 0 WHERE id = '.$tmp['tid'].' LIMIT 1';
                // echo $query."<br/>";
                mysql_query($query);

                $query = 'DELETE FROM misek WHERE id = '.$tmp['id'].' LIMIT 1;';
                if ($config['debug'] > 1) {
                    echo $query.'<br/>';
                }
                mysql_query($query);
            }
        }
        if ($config['debug'] > 0) {
            echo $c.' db csak nullák eltávolítva<br/>';
        }
    }
}
