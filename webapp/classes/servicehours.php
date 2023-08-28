<?php

use Illuminate\Database\Capsule\Manager as DB;

/**
 * A miserendek és az OSM/open_hours közötti kapcsolatot biztosítja
 */
class ServiceHours {


    function loadMasses(int $tid) {
        $string = "";

        // Load all Times of Church
        $results = DB::table('misek')
			->where('torles','0000-00-00 00:00:00')
			->where('tid',$tid)
            ->orderBy('weight','DESC')
            ->orderBy('idoszamitas')
			->orderBy('nap')
			->orderBy('ido')
			->get();

        global $milyen;
        $days = $_days = array('Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su');
        $months = ["Jan","Feb","Mar","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];


        $period = ""; $nap = "";
        $schedule = [];
        foreach($results as $row) {
            foreach(['idoszamitas','weight','tol','ig','tmp_datumtol','tmp_relation','tmp_datumig'] as $field)
                $schedule[$row->idoszamitas][$field] = $row->$field;

            $day = [];
            foreach(['ido','nyelv','milyen','megjegyzes','nap2'] as $field) { 
                if($row->$field != '' ) $day[$field] =   $row->$field;            
            }

            $d = $days[$row->nap > 0 ? $row->nap - 1 : 6];
            if($row->tol == $row->ig) $d = "Mo-Su";
            if(isset($row->nap2)) {
                if($row->nap2 == 'pt') $d = 'week 1-53/2 '.$d;
                elseif($row->nap2 == 'ps') $d = 'week 2-53/2 '.$d;
                elseif( $row->nap2 != 0 ) $d .= '['.$row->nap2.']'; 
            }
            $schedule[$row->idoszamitas]['days'][$d][] = $day;
        }
        
        //Optimze days
        foreach($schedule as &$periods) {
            $hashs = [];
            foreach($periods['days'] as $day => $times ) {
                    $hashs[md5(json_encode($times))][] = $day;                    
            }
            foreach($hashs as $days) {
                if(count($days) > 1 OR 4==4) {
                    $saveDay = $periods['days'][$days[0]];
                    $dayPattern = ''; $lastDayKey = false;
                    foreach($days as $k => $day) {
                        $DayKey = array_search($day, $_days);                        
                        
                        if($dayPattern == '' ) $dayPattern = $day;
                        elseif ($lastDayKey < $DayKey - 1)  {
                            $dayPattern .= ','.$day;
                        } else {                             
                            $dayPattern .= '-'.$day;
                        }
                        $lastDayKey = $DayKey;

                        
                        unset($periods['days'][$day]);
                    }
                                        
                    $dayPattern = preg_replace('/^('.implode('|',$_days).')(-('.implode('|',$_days).')){1,4}-('.implode('|',$_days).')(,|$)/',"$1-$4$5",$dayPattern);
                
                    $periods['days'][$dayPattern] = $saveDay;
                }
            }
            

        }
       //printr($schedule);

        // Generate OSM string
        global $milyen;
        $string = '';
        foreach($schedule as $key => $periodss) {            
            if(count($schedule) == 1 AND $periodss['tmp_datumtol'] == '01-01' AND $periodss['tmp_datumig'] == '12-31') { }
            else if($periodss['tmp_datumtol'] == $periodss['tmp_datumig'])
                $string .= date("M d",strtotime("2023-".$periodss['tmp_datumtol'])).": ";
            else
                $string .= date("M d",strtotime("2023-".$periodss['tmp_datumtol']))."-".date("M d",strtotime("2023-".$periodss['tmp_datumig'])).": ";
            
            foreach($periodss['days'] as $key => $times) {
                // if(count($periodss['days']) == 1 AND $key == 'Mo-Su') { } else  // Ez logikus lenne, de a feldolgozó nem szereti
                $string .= $key." ";
                foreach($times as $time) {
                    $string .= date('H:i',strtotime($time['ido']));

                    //add comment(s)
                    /**/ 
                    if(isset($time['milyen']) OR isset($time['megjegyzes']) ) {
                        $string .= " \"";
                        if(isset($time['milyen'])) {
                            if(isset($milyen[$time['milyen']])) $string .= $milyen[$time['milyen']]['name'];
                            else $string .= $time['milyen'];
                        }
                        if(isset($time['milyen']) AND isset($time['megjegyzes']) ) $string .= ", ";
                        if(isset($time['megjegyzes'])) $string .= $time['megjegyzes'];
                        $string .= "\"";
                    }
                    /**/
                    $string .= ',';
                }
                $string = preg_replace('/(\,(| ))$/','',$string);
                $string .= ", ";
            }
            $string = preg_replace('/(\,(| ))$/','',$string);
            $string .= "; ";            
        }
        $string = preg_replace('/(\;(| ))$/','',$string);

        $this->validate($string);
        
        $this->string = $string;
            
    }

    function validate(string $string) {

        $exp = urlencode(str_replace("\n","",$string));

        $openingh = new \externalapi\OpeninghApi();
        $openingh->validate($string);
        $this->linkForDetails = $openingh->linkForDetails;
    
    }

   

}
