<?php

namespace Html\Calendar;

use Api\Church;
use Illuminate\Database\Capsule\Manager as DB;


class Migrate extends \Html\Html {

    public $template = "layout_empty.twig";

    public $napok = [ 1 => "MO", 2 => "TU", 3 => "WE", 4 => "TH", 5 => "FR", 6 => "SA", 7 => "SU"];
    public $specialDays = [
                '01-01', '01-06', '03-25', '05-01', '06-29', '07-26', '08-05', '08-15', '08-15 -8', '08-20', '08-20 -8', '08-29', '10-20', '10-23', '11-01', '11-02', '12-31'
            ];

    public function __construct($path) {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $time = microtime(true);

        $masseswitherror = [];
        $churcheswitherror = [];
        $periodswitherror = [];

        try {
            // find templomok that have exactly one distinct misek.idoszamitas
            $templomok = DB::table('templomok as t')
            ->join('misek as m', 'm.tid', '=', 't.id')
            ->select('t.*')
            ->where('ok','i')->where('miseaktiv','1')
            ->where('updated_at', '>=', '2020-01-01 00:00:00');

            if(isset($_GET['limit'])) {
                $templomok = $templomok->limit($_GET['limit']);
            }
            
            $templomok = $templomok->groupBy('t.id');
            
            if(isset($_GET['tid'])) {
                $templomok = $templomok->where('t.id',$_GET['tid']);
            } 

            $templomok = $templomok->get();

            echo count($templomok)." templomot találtunk.<br/>\n";
            $countperiods = 0;
            $countmasses = 0;
            $savednasses = 0;
            $countmisekwitherror = 0;
            //printr($templomok);

            $languages = unserialize(LANGUAGES);
            $languages['hu'] = $languages['h'];
            $languages = is_array($languages) ? array_keys($languages) : [];
            
            
            $rows = [];
            foreach ($templomok as $t) {
                
                $templom = \Eloquent\Church::find($t->id);
                if(!$templom) {
                    // throw new \Exception("No church found with id ".$t->id);
                    echo "<br><strong>Error:</strong> No church found with id ".$t->id."<br/>\n";
                    continue;
                }
                // delete existing calendar entries for this church
                DB::table('cal_masses')->where('church_id', $t->id)->delete();

                // Load all rows from `misek` for this church, then group them by `idoszamitas`
                // (collection grouping, done in PHP after fetching).
                $Idoszakok = DB::table('misek')
                    ->where('tid', $t->id)
                    ->where('torles', '0000-00-00 00:00:00')
                    ->orderBy('idoszamitas')
                    ->get()
                    ->groupBy('idoszamitas');
                $countperiods += count($Idoszakok);
                foreach ($Idoszakok as $idoszaknev => $misek) {
                    $countmasses += count($misek);
                    try {               
                        
                        $period = $this->findPeriod($misek[0]);
                        if(!$period) {
                            throw new \Exception("No period found for mise idoszamitas ".$idoszaknev);
                        }
                        
                        $misek = $this->concatDays($misek);

                        $misek = $this->normalizeMiseLanguage($misek, $templom);
                        $misek = $this->normalizeMiseTitleAndRite($misek, $templom);
                        $misek = $this->normalizeMiseAttributes($misek, $templom);

                        foreach ($misek as $mise) {
                                
                            
                        
                                $title = "Szentmise";

                                if($mise->liturgy == "ige") {
                                    $title = "Igeliturgia";
                                    $rite = "ROMAN_CATHOLIC";

                                } else if ( $mise->liturgy == 'rom' ) {
                                    $title = "Szentmise";
                                    $rite = "ROMAN_CATHOLIC";
                                } else if ( $mise->liturgy == 'regi' ) {
                                    $title = "Régi rítusú szentmise";
                                    $rite = "TRADITIONAL";
                                } else if ($mise->liturgy == 'gor' ) {
                                    $title = "Szent Liturgia";
                                    $rite = "GREEK_CATHOLIC";
                                } else if ( $mise->liturgy == 'utr' or $mise->liturgy == 'vecs' or $mise->liturgy == 'szent' ) { 
                                    if($mise->liturgy == 'utr') {
                                        $title = "Utrenye";
                                    } else if ($mise->liturgy == 'szent' ) {
                                        $title = "Szentségimádás";
                                    } else {
                                        $title = "Vecsernye";
                                    }
                                    $rite = "GREEK_CATHOLIC";
                                } else {
                                    //echo "Unknown liturgy type: '".$mise->liturgy."' (templom id: ".$t->id.", mise idoszamitas: ".$mise->idoszamitas.")<br/>\n";   
                                    $mise->error = "Unknown liturgy type: '".$mise->liturgy."'";
                                    throw new \Exception($mise->error,45);
                                }   

                                if($mise->nyelv && in_array($mise->nyelv, $languages)) {
                                    if($mise->nyelv == 'h')  $mise->nyelv = 'hu';                                    
                                } else if ($mise->nyelv == '' ) {
                                    $mise->nyelv = 'hu';
                                } else {                                                                        
                                    $mise->error = "Unknown language code: '".$mise->nyelv."'";
                                    $mise->nyelv = 'hu';
                                    throw new \Exception($mise->error,35);
                                }

                                // Nem teljesen világos, hogy miért kell itt még ellenőrizni mert a normalizeMiseAttributes függvényben is megvan ez az ellenőrzés
                                if(!isset($mise->types)) {
                                    $mise->types = [];
                                }

                                $calmass = \Eloquent\CalMass::create([
                                    'church_id' => $t->id,
                                    'period_id' => $period->id,
                                    'title' => $title,
                                    'types' => $mise->types,
                                    'rite' => $rite,
                                    'start_date' => $period->start_date."T".$mise->ido,
                                    'rrule' => [
                                        'freq' => 'weekly',
                                        'until' => $period->end_date."T23:59:59",
                                        "dtstart" => $period->start_date."T".$mise->ido,
                                    ],
                                    'lang' => $mise->nyelv,
                                    'comment' => $mise->megjegyzes
                                ]);
                                
                                $rrule = &$calmass->rrule;

                                // Ha meg van adva, hogy milyen napon van, akkor beletesszük
                                $byweekday = array_map(function($day) {
                                        if($day == 0) return false;
                                        return $this->napok[$day];
                                    }, $mise->nap);                                
                                $byweekday = array_values(array_filter($byweekday, function($v) {
                                    return $v !== false;
                                }));
                                if($byweekday != [])                                                                   
                                    $rrule['byweekday'] = $byweekday;
                                

                                // Páros héten, páratlan héten, a hónap valahanyadik hetében, stb...
                                if($mise->nap2 and $mise->nap2 != "") {
                                    if(in_array($mise->nap2, [1,2,3,4,5,-1])) {
                                        $rrule['freq'] = "monthly";
                                        $rrule['bysetpos'] = (int)$mise->nap2;    
                                    }
                                    else if (in_array($mise->nap2, ['ps','pt'])) {
                                        $rrule['freq'] = "weekly";
                                        if($mise->nap2 == 'ps') {
                                            // páros
                                            $rrule['byweekno'] = [2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52];
                                        } else {
                                            // páratlan
                                            $rrule['byweekno'] = [1,3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33,35,37,39,41,43,45,47,49,51];
                                        }
                                    }
                                    else {
                                        echo "Unknown nap2 value: ".$mise->nap2." (templom id: ".$t->id.", mise idoszamitas: ".$mise->idoszamitas.")<br/>\n";   
                                        throw new \Exception("Unknown nap2 value: ".$mise->nap2);
                                    }
                                }

                                // Egyetlen napos periódusok esetén külön szabályok
                                if($mise->tol == $mise->ig) {
                                    
                                    // Karácsony napjaira külön szabály
                                    if($period->name == 'Karácsony') {
                                        {
                                            $rrule['freq'] = 'monthly';
                                            if( in_array($mise->tol, ['12-24','12-24 -8', '12.24 -8','12-25 -1', 'december 24. -8','December 24 -8'])) {
                                                $mise->tol = '12-24';
                                                $rrule['bymonthday'] = [24];
                                            } else if(  in_array($mise->tol,['12-25','12-25 -8','12-24 +1', '12.15','December 25. -8'])) {
                                                $mise->tol = '12-25';
                                                $rrule['bymonthday'] = [25];
                                            } else if( in_array($mise->tol,['12-26','12-25 +1','12.26']) ) {
                                                $rrule['bymonthday'] = [26];
                                            } else {
                                                echo "Invalid date for Karácsony period: ".$mise->tol." (templom id: ".$t->id.", mise idoszamitas: ".$mise->idoszamitas.")<br/>\n";
                                                throw new \Exception("Invalid date for Karácsony period: ".$mise->tol);
                                            }
                                        }
                                    }
                                    else if($period->name == 'Nagyszombat' or $period->name == 'Húsvéti vigília') {
                                        $rrule['freq'] = 'weekly';
                                        $rrule['byweekday'] = ['SA'];
                                    } else if ($period->name == 'Nagycsütörtök' or $period->name = 'Nagcsütörtök') {
                                        $rrule['freq'] = 'weekly';
                                        $rrule['byweekday'] = ['TH'];
                                    } else if ($period->name == 'Nagypéntek') {
                                        $rrule['freq'] = 'weekly';
                                        $rrule['byweekday'] = ['FR'];
                                    } else if ($period->name == 'Húsvétvasárnap' or $period->name == 'Húsvét') {
                                        $rrule['freq'] = 'weekly';
                                        $rrule['byweekday'] = ['SU'];
                                    }
                                    // Dátumos nagy ünnepeink 
                                    else if (in_array($mise->tol, $this->specialDays) ) {
                                        $rrule['freq'] = 'yearly';
                                        $rrule['bymonthday'] = [ (int)substr($mise->tol, 3,2) ];
                                        $rrule['bymonth'] = [ (int)substr($mise->tol, 0,2) ];
                                    }

                                }
                                $calmass->rrule = $rrule;
                                
                                $calmass->save();
                                $savednasses++;
                                                                
                        }
                    } catch (\Exception $e) {   
                        $churcheswitherror[$t->id] = $t;
                        
                        
                        $code = $e->getCode();                                                                            

                        if ( $code > 0  ) {
                            // egy misére vonatkozó hiba
                            $countmisekwitherror++;
                            if(!isset($mise->error)) $mise->error = $e->getMessage();
                            $masseswitherror[$mise->id] = $mise;   
                            //echo  $code." -- Error processing mise id=".$mise->id.", templom id = ".$mise->tid.": ".$e->getMessage()."<br/>\n"; 
                        
                        }  else {                       
                            // periódusokra vonatkozó hiba 
                            $countmisekwitherror += count($misek);
                        
                            $key = $misek[0]->tol."-".$misek[0]->ig;
                            if(!isset($periodswitherror[$key])) {
                                $periodswitherror[$key] = $misek[0];
                                $periodswitherror[$key]->count = 0;
                                $periodswitherror[$key]->countall = 0;
                            } 
                            $periodswitherror[$key]->count++;       
                            $periodswitherror[$key]->countall += count($misek);
                        }
                        
                        /*
                        echo "Error processing templom <i>".$t->nev.", ".$t->varos." (".$t->id.")</i>";
                        if(isset($misek[0])) 
                            echo ": <b>".$misek[0]->idoszamitas."</b> ['".$misek[0]->tol."', '".$misek[0]->ig."'] ";
                        echo " - ".$e->getMessage()."<br/>\n";
                        */        
                    }
                
                } 
            
                // Egymást kizáró periódusok rendbetétele. Egyszerüsítve.
                $rows = DB::table('cal_masses')
                    ->select('cal_masses.church_id', 'cal_periods.name', 'cal_periods.id', 'cal_periods.weight')
                    ->join('cal_periods', 'cal_periods.id', '=', 'cal_masses.period_id')
                    ->where('cal_masses.church_id', $t->id)
                    ->groupBy('cal_masses.period_id')
                    ->get();

                foreach ($rows as $i => $row) {
                    $experiod = [];
                    $currWeight = isset($row->weight) ? (int)$row->weight : 0;
                    foreach ($rows as $other) {
                        if ($other->id === $row->id) continue;
                        $otherWeight = isset($other->weight) ? (int)$other->weight : 0;
                        if ($otherWeight > $currWeight) {
                            $experiod[] = $other->id;
                        }
                    }
                    $rows[$i]->experiod = empty($experiod) ? false : $experiod;
                }

                // Map period_id => experiod from the grouped rows
                $periodEx = [];
                foreach ($rows as $row) {
                    $periodEx[(int)$row->id] = $row->experiod === false ? false : (array)$row->experiod;
                }

                // Fetch all cal_masses for this church and update each row's experiod column
                $masses = DB::table('cal_masses')->where('church_id', $t->id)->get();
                foreach ($masses as $m) {
                    $ex = isset($periodEx[(int)$m->period_id]) ? $periodEx[(int)$m->period_id] : false;
                    $updateValue = $ex === false ? null : json_encode($ex);
                    DB::table('cal_masses')->where('id', $m->id)->update(['experiod' => $updateValue]);
                }
                
            }


            /// Munka vége. Írjuk ki az eredményeket.
            echo $countperiods." időszakot dolgoztam fel.<br/>\n";
            echo $countmasses." miséd dolgoztam fel.<br/>\n";
            echo $savednasses." misét mentettem.<br/>\n";
            echo $countmisekwitherror." misével gyűlt meg a bajom.<br/>\n";
            echo count($periodswitherror)." időszakkal gyűlt meg a bajom.<br/>\n";
            echo count($churcheswitherror)." templommal gyűlt meg a bajom.<br/>\n";
            echo "Futási idő: ".round(microtime(true) - $time, 2)." másodperc.<br/>\n";
            

            // Sorbarendezem az eredményeket
            if (!empty($periodswitherror) && is_array($periodswitherror)) {
                uasort($periodswitherror, function($a, $b) {
                    $ac = isset($a->count) ? (int)$a->count : 0;
                    $bc = isset($b->count) ? (int)$b->count : 0;
                    if ($ac !== $bc) {
                        // primary: count, descending
                        return ($ac > $bc) ? -1 : 1;
                    }

                    // secondary: updated_at (use church.updated_at if tid present), more recent first
                    $a_up = '';
                    $b_up = '';
                    if (isset($a->tid)) {
                        $churchA = \Eloquent\Church::find($a->tid);
                        $a_up = $churchA ? $churchA->updated_at : '';
                    }
                    if (isset($b->tid)) {
                        $churchB = \Eloquent\Church::find($b->tid);
                        $b_up = $churchB ? $churchB->updated_at : '';
                    }

                    $ta = $a_up ? strtotime($a_up) : 0;
                    $tb = $b_up ? strtotime($b_up) : 0;
                    if ($ta === $tb) return 0;
                    return ($ta > $tb) ? -1 : 1; // descending (newer first)
                });
            }
            
            echo "<h2>Periods with errors</h2>\n";
            if (empty($periodswitherror)) {
                echo "<p>No periods with errors.</p>\n";
            } else {
                $n = 0;
                echo "<table border=\"1\" cellpadding=\"4\" cellspacing=\"0\">\n";
                echo "<thead>
                    <tr>
                        <th>#</th>
                        <th>idoszamitas</th>
                        <th>[tol, ig]</th>
                        <th>templom</th>
                        <th>count</th>
                        <th>countall</th>
                        <th>updated_at</th>
                        <th></th>
                    </tr></thead>\n";
                echo "<tbody>\n";
                foreach ($periodswitherror as $key => $m) {
                    $idoszamitas = isset($m->idoszamitas) ? htmlspecialchars($m->idoszamitas) : '';
                    $tol = isset($m->tol) ? htmlspecialchars($m->tol) : '';
                    $ig = isset($m->ig) ? htmlspecialchars($m->ig) : '';
                    $count = isset($m->count) ? (int)$m->count : 0;
                    $countall = isset($m->countall) ? (int)$m->countall : 0;
                    

                    $church = null;
                    if (isset($m->tid)) {
                        $church = \Eloquent\Church::find($m->tid);
                    }
                    $nev = $church ? htmlspecialchars($church->nev) : '';
                    $varos = $church ? htmlspecialchars($church->varos) : '';
                    $tid = isset($m->tid) ? (int)$m->tid : '';
                    $updated_at = $church ? htmlspecialchars($church->updated_at) : '';

                    echo "<tr>";
                    echo "<td>".(++$n)."</td>";
                    echo "<td>{$idoszamitas}</td>";
                    echo "<td>['" . $tol . "', '" . $ig . "']</td>";
                    echo "<td>{$nev}, {$varos} ({$tid}) </td>";                    
                    echo "<td>{$count}</td>";
                    echo "<td>{$countall}</td>";
                    echo "<td>{$updated_at}</td>";
                    echo "<td>";
                    echo "<a href=\"/templom/{$tid}/editschedule\">/editschedule</a> ";
                    echo "<a href=\"/calendar/migrate/?tid={$tid}\">/migrate/...</a>";
                    echo "</td>";
                    echo "</tr>\n";
                }
                echo "</tbody>\n</table>\n";
            }

            // masses with errors
            echo "<h2>Masses with errors</h2>\n";
            if (empty($masseswitherror)) {
                echo "<p>No masses with errors.</p>\n";
            } else {
                echo "<table border=\"1\" cellpadding=\"4\" cellspacing=\"0\">\n";
                echo "<thead>
                    <tr>
                        <th>#</th>
                        <th>templom</th>
                        <th>idoszamitas</th>
                        <th>[tol, ig]</th>
                        <th>nap</th>
                        <th>nap2</th>                    
                        <th>nyelv</th>
                        <th>liturgy</th>
                        <th>milyen</th>
                        <th>megjegyzes</th>
                        <th>updated_at</th>
                        <th>error</th>
                        <th></th>
                    </tr></thead>\n";
                echo "<tbody>\n";
                $n = 0;
                foreach ($masseswitherror as $mise) {
                    $n++;
                    echo "<tr>";
                    echo "<td>".$n."</td>";
                    echo "<td>";
                    $church = \Eloquent\Church::find($mise->tid);
                    if ($church) {
                        $nev = htmlspecialchars($church->nev);
                        $varos = htmlspecialchars($church->varos);
                        $tid = (int)$church->id;
                        echo "{$nev}, {$varos} ({$tid}) ";
                    }
                    echo "</td>";
                    echo "<td>".$mise->idoszamitas."</td>";
                    echo "<td>".$mise->tol." - ".$mise->ig."</td>";
                    echo "<td>".json_encode($mise->nap)."</td>";
                    echo "<td>".$mise->nap2."</td>";
                    echo "<td>".$mise->nyelv."</td>";
                    echo "<td>".$mise->liturgy." ".json_encode($mise->liturgies)."</td>";
                    echo "<td>".$mise->milyen."</td>";
                    echo "<td>".$mise->megjegyzes."</td>";
                    echo "<td>".$church->updated_at."</td>";
                    echo "<td>".( isset($mise->error) ? $mise->error : "" )."</td>";
                    echo "<td>";
                    echo "<a href=\"/templom/{$tid}/editschedule\">/editschedule</a> ";
                    echo "<a href=\"/calendar/migrate/?tid={$tid}\">/migrate/...</a>";
                    echo "</td>";

                    echo "</tr>";
                }
            }

            


        } catch (\Exception $e) {
            $this->rows = ['error' => $e->getMessage()];
            printr($e->getMessage());
        }


        //$this->content = json_encode($_REQUEST);



    }

    public function findPeriod($mise) {
            //printr($mise);


        /* Milyen időszakok léteznek:
        SELECT 
            m.*, 
            x.db AS darabszam
        FROM misek m
        JOIN (
            SELECT 
                idoszamitas, 
                MIN(id) AS sample_id,
                COUNT(*) AS db
            FROM misek
            GROUP BY idoszamitas
        ) x ON m.id = x.sample_id
        WHERE torles = '0000-00-00 00:00:00'  
        ORDER BY `darabszam` DESC
        */

        /* Egy adott időszak előfordulása és változatai 
        SELECT 
                m.*,                -- mintasor
                x.db AS darabszam   -- ennyi ilyen (tol,ig) pár van
            FROM misek m
            JOIN (
                SELECT 
                    tol,
                    ig,
                    MIN(id) AS sample_id,
                    COUNT(*) AS db
                FROM misek
                WHERE idoszamitas = 'advent'                
                    AND torles = '0000-00-00 00:00:00'
                    AND tol <> ig 
                GROUP BY tol, ig
            ) x ON m.id = x.sample_id
            ORDER BY x.tol, x.ig;
        */
//printr($mise);
        
        // Karácsony
        if( in_array( [$mise->tol, $mise->ig], [
            [ '12-24','12-26'], ['12-24','12-25'], ['12-24 -8', '12-24 -8'], ['12-25','12-26'],['12-25','12-25'],['12-25 -8', '12-25 -8'],['12-25 -1', '12-25 -1']	,['december 24. -8', 'december 24. -8']	,
            ['12-24', '12-24'],['12-25', '12-25'],['12-26', '12-26'],	['12-24 +1', '12-24 +1'],['12-25 +1', '12-25 +1'],['12.15', '12.15'],['12.26', '12.26'],['12.24 -8', '12.24 -8'],
            ['December 24 -8', 'December 24 -8'],['December 25. -8', 'December 25. -8']
        ] ) )  {
            $periodName = 'Karácsony';
        }
        // Szent három nap
        elseif (
            in_array($mise->idoszamitas, ['Húsvét','Húsvétvasárnap','Nagyszombat','Húsvéti vigília','Nagypéntek', 'Nagycsütörtök'])
        ) {
            $periodName = 'Szent három nap';

        }
        // Egy-egy nagy ünnep az egész évbenbe rakjuk bele
        else if ( in_array( $mise->tol, $this->specialDays ) and $mise->tol == $mise->ig ) {
            $periodName = 'Egész évben';
        }

        // Egész évben
        else if( in_array( [$mise->tol, $mise->ig], [
            ['január 1.', 'december 31.'],['0101', '1231'],['December 25.', 'Advent I. vasárnapja'],['01-03', 'Advent I. vasárnapja -1'],['08-24', '07-04'],['December 25.', 'Advent I. vasárnapja'],['01.01', '12.31'],[ '12-25','Advent I. vasárnapja -1'],	['1', '12-31'],	['01-01', '11-30'],	['Advent I. vasárnapja', 'Krisztus Király vasárnapja'],
            ['08-28', '07-23'],['01-01', 'Advent I. vasárnapja -1'],[ "01-01", "12-31"],['12-24', 'Advent I. vasárnapja'],['12-25', '11-30'],['01-01 +1', '12-31 -1'],['01-01 +1', '12-31'],	['július 2. vasárnapja', '05-31'],
            ['01.01', '12.31'],[ "12-25", "Advent I. vasárnapja"],['06-01', '04-30'],['Húsvéthétfő +1', '11.06.'],	['08-31', '06-30'],	['12-27', 'Advent I. vasárnapja'],
            ['Advent I. vasárnapja', '12-24 -1'] , ['Húsvétvasárnap', 'Krisztus Király vasárnapja'],	['12-26 +1', 'Advent I. vasárnapja -1'],	['12-28', '11-30'],	['12-24', '11-30']
        ] ) )  {
            $periodName = 'Egész évben';
        }
        // Ősszel
        else if ( in_array( [$mise->tol, $mise->ig], [
                ['08-28', '10-31'],['szeptember 2. vasárnapja +1', '10-31'],['09-01', '11-30 -1'],['09-01', '10-31'],['09-01', 'Advent I. vasárnapja'],['11-01', 'Advent I. vasárnapja -1'],
                ['első tanítási nap', 'Advent I. vasárnapja'],['10-26', 'Advent I. vasárnapja -1'],['első tanítási nap', 'Őszi óraátállítás -1'],['szeptember utolsó vasárnapja +1', 'Advent I. vasárnapja'],['09-01', 'október utolsó vasárnapja -1'],['10-01', 'Advent I. vasárnapja -1'],['09-01', '12-31'],	['szeptember utolsó vasárnapja', 'Advent I. vasárnapja -1'],['első tanítási nap', '09-30'],
                ['09-01 +1', 'Advent I. vasárnapja -1'],['09-01', '11-08'],['09-01', '09-28'],['08-24', 'szeptember utolsó vasárnapja -1'],['10-01', 'Őszi óraátállítás -1'],['szeptember 1. vasárnapja', 'Advent I. vasárnapja -1'],['09-01', 'Advent I. vasárnapja -1'],	['10-26', 'Advent I. vasárnapja'],['10-01', 'Advent I. vasárnapja'],
                ['09-03', '10-22'],['augusztus utolsó vasárnapja +1', 'október utolsó vasárnapja']	,['09-01', '10-14'],['10-31', 'Advent I. vasárnapja'],['első tanítási nap', 'Advent I. vasárnapja -1'],['09-01', 'Őszi óraátállítás -1'],	['10-02', 'Advent I. vasárnapja'],	['08-24', 'Őszi óraátállítás']
                 
        ] ) ) { 
                $periodName = 'Ősz';
        }
        
        // Tavasszal
        else if ( in_array( [$mise->tol, $mise->ig], [
                ['05-01', '06-30'],['Tavaszi óraátállítás', '06-30'],['Tavaszi óraátállítás', 'utolsó tanítási nap'],
                ['03-01', 'Húsvétvasárnap'],['Húsvétvasárnap', '06-30'],['04-01', 'utolsó tanítási nap'],['Tavaszi óraátállítás', '06-05'],['04-25', '07-15'],['Húsvétvasárnap', '07-04'],['01-01', '04-30'],['03-30', 'utolsó tanítási nap'],['03-01', '05-31'],['05-01', '06-19'],	['Tavaszi óraátállítás', '07-04'],
                ['Tavaszi óraátállítás', '04-30 -1'],['03-01', '03-19'],['május első vasárnapja', 'június utolsó vasárnapja -1'],['05-01', '06-31 -1'],['03-01', '04-30'],['03-16', '05-30'], 	['04-25', '05-31'], ['05-01', 'utolsó tanítási nap'],['04-01', '05-21']
        ] ) ) { 
            $periodName = 'Tavasz';
        }
        
        // Télen 
        else if ( in_array( [$mise->tol, $mise->ig], [
                ['10.01', '04.30'],['10-31', '02-28'],['09-01', 'Hamvazószerda -1'],['12-24', 'utolsó tanítási nap'],['10.01', '03.30'],['október utolsó vasárnapja', '03-25'],	['10-01', '05-01 -1'],	['12-25', 'Tavaszi óraátállítás -1'],['Őszi óraátállítás', '02-28'],	['szeptember 1. vasárnapja', '03-28'],
                ['Őszi óraátállítás', '03-25'],['12-01', '02-28'],	["12-25 +1", "03-24"],['12-25 +1', '04-30'],['09-04', '02-25'],	['10-01', '02-28'],['09-01', '07-31'],	['10-01', '03-28'],	['09-01', '03-24'],	['10.05', 'Tavaszi óraátállítás'],
                ['10-23', '02-28'],['Virágvasárnap', 'Advent I. vasárnapja -1'],['10-31', '03-31'],['10-26', '03-28'], ['10-30', '03-25'],['10-01', '03-30'],['11-01', 'Advent I. vasárnapja'],	['09-30', '04-07'],['09-01', '05-31 -1'],['09-03', '06-30'],	['október utolsó vasárnapja', 'március utolsó szombatja'],
                ['Advent I. vasárnapja', 'Virágvasárnap -1'],['10-15', '04-14'],['10-30', '03-26'],	['10-28', '03-24'],	['10-01', '05-31'],	['10-04', '04-30'],['10-01', '03-25'],['október második vasárnapja +1', '05-31'],	['12-25', '03-25'],	['11-01', '05-31 -1'],['10.01.', '03.31.'],
                ['10-05', 'Tavaszi óraátállítás -1'],['10-27', '05-31'],['11-01', '05-31'],['11-01','03-15'],['10-01', '03-31'],['10-31', '03-27'],	['11-01', 'Húsvétvasárnap -1'],['Őszi óraátállítás', '02-27'],	['09-03', '06-30'],['12-25', 'utolsó tanítási nap -1'],	['10-05', '06-14'],
                ['10-02', '03-29'],['10-26', '03-29'],['10-29', '03-24'],['10-01', '03-29'],	['09-29', '04-24'],	['11-01', '03-25'],	['10-30', '04-30'],	['szeptember 1', '03.31'],['10-03', '03-16'],
                ['09-30 +1', 'Tavaszi óraátállítás'],['10-01', '03.31'],['október első vasárnapja', 'Tavaszi óraátállítás'],['11-01', '03-01 -1'],['10-27', '03-30'],['10-01', '04-30'],	['12-25', '03-29'],['09-01', 'Húsvétvasárnap'],['10-27', '03-29'],['Advent I. vasárnapja +1', 'Húsvétvasárnap']	,	['10-15', 'Tavaszi óraátállítás'],
                ['10-01', '03-01 -1'],['10-26', 'Tavaszi óraátállítás -1'],['szeptember utolsó vasárnapja', '03-31'],['11-01', '03-29'],['09-01', '03-31'],['09-01', '03-31'],['09-02', '04-30'],['Őszi óraátállítás', 'Tavaszi óraátállítás'],['11-01', '04-30'],['Őszi óraátállítás', 'Advent I. vasárnapja -1'],['11-01', '03-30'],['10-31', '03-26'],['10-01', '03-23'],	['szeptember utolsó vasárnapja', 'május első vasárnapja'],
                ['09-27', '03-30 -1'],['10-29', '03-26'],['szeptember utolsó vasárnapja', 'március utolsó vasárnapja'],['10-15', '02-28'],['10-02', '04-30'],['09-01', '04-05'],['12-25', '03-31 -1'],['09-10', '03-30'],['Őszi óraátállítás', 'Tavaszi óraátállítás -1'],	['09-30', '04-23'], 	['10-01', 'Húsvétvasárnap'],['10-02', '03-24'], 	['09-01', '12-31 -1'], 		['10-01', 'március utolsó vasárnapja'],	['augusztus utolsó vasárnapja', 'Úrnapja'],
                ['Őszi óraátállítás +1', '03-24'],['09-13', 'Húsvétvasárnap -1'],['szeptember 1. vasárnapja', '03-28'],['09-01', '05-31'],    	['11-01', '03-31']  , ['11-01', '02-28'], ['12-25', '06-30'], ['12-25', '04-23'],	['10-15', '03-14'],	['09-02', '05-31'],['05-01', '09-31'],                	['10-01', 'március utolsó vasárnapja -1'], ['11-01', 'Húsvétvasárnap'], ['09-02', '06-06'],	['09-30', '04-24'], ['12-25', '03-31'], 	['szeptember utolsó vasárnapja +1', 'Húsvétvasárnap -1'],	['11-01', '01-31'], 	['10.01', '03.31'], ['10.01', '03.31 -1'], 	['10.01', '04.30 -1'],['12-25', 'Húsvéthétfő']
            ] ) ) { 
            $periodName = 'Tél';
        }        
        // Nyáron
        else if ( in_array( [$mise->tol, $mise->ig], [
                ['Húsvétvasárnap +1', '09-30'],['Tavaszi óraátállítás', '11-04'],['05-01', '10-01'],['04-15', '08-31'],['04-05', '08-31'],['04-25', '09-28'],['03-29', 'szeptember 1. vasárnapja -1'],['03-27', '10-29'],['03-25', '10-27'],	['03-30', '09-30'],['Húsvétvasárnap', 'október utolsó szombatja'],['03-15', '10-14'],	['03-25', '10-01'],['03-17', '10-02'],['06-15', '10-04'],
                ['Tavaszi óraátállítás', '09-26 -1'],['03-30', '08-31 -1'],['Húsvéthétfő', 'szeptember utolsó vasárnapja'],['04-01', '08-31'],['05-01', '11-30'],['03-30', '10-01 -1'],['05-01', '09-01'],['03-30', '10-25'],['03-25', '10-28'],['Tavaszi óraátállítás', '09-30'],	['05-29', '09-03'],['04-01', '10-01 -1'],['04.01', '09.30'],['03-01', '05-21 -1'],
                ['03-01', '11-30'],['Húsvétvasárnap', '09-12'],['03-29', 'október első vasárnapja -1'],['05-01', '09-01 -1'],['04-01', '09-09 -1'],['06-01', '09-31'],['04-01', '10-31'],['03-31', '09-30'],['március utolsó vasárnapja', '09-30'],['06.01 +1', 'október második vasárnapja -1'],	['Húsvéthétfő +1', 'szeptember utolsó vasárnapja -1'],
                ['03-01', '10-14'],['03-30', 'szeptember 2. vasárnapja'],['Tavaszi óraátállítás', 'Őszi óraátállítás'],['04-24', '09-29'],	['05-01', '10-03'],['04-01', '10-30'],['03-31', '10-31'],['03-27', '10-30'],	['06-01', '10-31'],	['Tavaszi óraátállítás', '10.04'],
                ['Tavaszi óraátállítás', 'szeptember utolsó vasárnapja'],['Tavaszi óraátállítás', 'Őszi óraátállítás -1'],['05-01', '10-29'],	['Húsvéthétfő', '08-30'],['Tavaszi óraátállítás', '10-15'],['03-24', '09-30'],['04-01', '09-31'],	['05-01', '08-31 -1'],	['05-13', '10-11'],
                ['03-31', '10-26'],['03-28', '10-30'],	['05-01', '09-30'],['Húsvétvasárnap', '09-30'],['04-25', '09-29'],['05-01', 'szeptember 1. vasárnapja'],	['03-25', 'szeptember utolsó vasárnapja'],	['március utolsó vasárnapja', 'szeptember utolsó vasárnapja'],
                ['03-25', 'Őszi óraátállítás'],['Tavaszi óraátállítás', '11-04'],['03-26', '10-29'],['05-01', '10-31'],['03-01', '09-30'],	['Húsvétvasárnap', '10-31'],['06-16', '10-20'],['05.01', '09.30 -1'],	['07-01', '09-02'],['03-01', 'október utolsó szombatja'],['május első vasárnapja', 'szeptember utolsó vasárnapja']	,
                ['03-31 +1', '10-01 -1'],['06-01', '10-26'],['03-29', '10-25'],['04-01', '09-30'],['Húsvétvasárnap +1', '10-31'],	['03-30', '10-01'], ['03-25', '08-31'],	['április 1', 'augusztus 31 -1'],	['Húsvéthétfő', '09-30'],	['Tavaszi óraátállítás +1', 'Őszi óraátállítás -1'],
                 ['04.01', '09.30 -1'], ['03-26', '09-30'],['04-01', '09-30 -1'], ['04-24', '10-01'], 	['03-30', '10-26'],	['04-08', '09-29'],['március utolsó vasárnapja', 'október utolsó szombatja'],['Húsvétvasárnap', '11-01']
         
        ] ) ) { 
            $periodName = 'Nyár';
        }

        // Tanítási időben
        else if ( in_array( [$mise->tol, $mise->ig], [
                ['szeptember 2. vasárnapja +1', 'június 2. vasárnapja -1'],['09-02', '06-02'],['12-26', 'utolsó tanítási nap'],	['09-01', '06-29'],	['08-31 +1', '04-27'],	['09-08', '06-01'],
                ['09-01', '03-30'],['10-01', '0331'],['09-01', '06-31'],['első tanítási nap', 'utolsó tanítási nap'],['első tanítási nap', 'utolsó tanítási nap -1'],['09-08', '06-23 -1'],
                ['szeptember 1. vasárnapja', 'május első szombatja -1'],['09-09', '05-15'],['08-24', '06-22'],['09-01', '06-15'],['09-01', '04-30'],	['szeptember 2. vasárnapja', 'utolsó tanítási nap'],
                ['szeptember 2. vasárnapja', 'május első vasárnapja'],['08-31', '06-01'],['09-01', '06-30'],['09-01', '06-14'],	['09-01', 'utolsó tanítási nap'],['szeptember 1. vasárnapja', 'június első vasárnapja'],
                ['szeptember 1. vasárnapja', 'Úrnapja -1'],['08-29', '06-04'],['09-30 +1', '05-01'],	['09-01', '06-01 -1'],
                ['szeptember 1. vasárnapja', 'Június 3. vasárnapja'],	['10-02', '06-30'], 	['09-01', 'Virágvasárnap -1'], 	['szeptember 1. vasárnapja +1', '04-30']
        ] ) ) { 
            $periodName = 'Tanítási idő';
        }
        // Nyári szünetben
        else if ( in_array( [$mise->tol, $mise->ig], [
                	['05-01', '09-08'],['május első vasárnapja', 'augusztus utolsó vasárnapja'],['Május 3. vasárnapja', 'szeptember 2. vasárnapja -1'],['07-16', '08-31'],['06-23', '08-23'],['06-11', '08-31'],['06-03', '09-01'],['07.01', '09.01.'],['utolsó tanítási nap +1', '08-31'],['06-02', '08-30'],['június első vasárnapja', 'augusztus utolsó vasárnapja -1'],['utolsó tanítási nap +1', 'szeptember 1. vasárnapja -1'],['06-15 +1', '09-01 -1'],['06-30', '08-31'],['07-01', '08-31'],	['06-16', '08-31'],['06-15', '08-31'],['utolsó tanítási nap +1', '0831'],	['07-01', '09-30'], 	['június 2. vasárnapja', 'szeptember 2. vasárnapja'],	['utolsó tanítási nap', 'első tanítási nap -1'],	['06-30', '08-30'],	['06-23', '09-07'],
                ['03-31', '08-31'],['Pünkösdvasárnap', '09-02'],['06-06', '08-31'],['06-01 +1', '09-30'],['06-01', '08-30'],['06-01', '08-31'],['06-01', '09-30'],['06-01', '09-01'],['utolsó tanítási nap', 'szeptember 2. vasárnapja'],	['07-01', '10-01'],	['05-01', '08-31'],	['07-01', '08-30'],	['06-29 +1', '09-01 -1'],['04-28', '08-31'],['06-15 +1', '08-31'],	['Nagycsütörtök', '08-31'],
               	['05-16', '09-08'],['június utolsó vasárnapja', '08-28'],['június 2. vasárnapja', '08-31'],['június első vasárnapja', 'szeptember 1. vasárnapja'],['utolsó tanítási nap', '06-30'],['utolsó tanítási nap +1','első tanítási nap -1'],['05-21', '08-31'],['07-05', '08-23'],['Pünkösdvasárnap', '09-30'],['Június 3. vasárnapja', 'szeptember 1. vasárnapja'],	['06-15', '09-15'],['06-29', '08-31'],	['06-02', '09-07'],
                ['07-18', '08-27'],['06-19 +1', '08-28 -1'],['Júius 3. vasárnapja', 'szeptember 1. vasárnapja'], ['07-24', '08-27'], ['utolsó tanítási nap', 'első tanítási nap'], 	['Húsvétvasárnap', 'szeptember utolsó vasárnapja'], ['június első vasárnapja +1', 'szeptember 1. vasárnapja -1'],['06-27', '09-06'],	['Virágvasárnap', '08-31']	
        ] ) ) { 
            $periodName = 'Nyári szünet';
        }

        // Advent
        else if ( in_array( [$mise->tol, $mise->ig], [
                ['Advent I. vasárnapja', '12-23'],['Advent I. vasárnapja', 'December 25. -1'],['Advent I. vasárnapja', '12-24'],['Advent I. vasárnapja', '12-23 -1'],['Advent I. vasárnapja +1', '12-25 -1'],	['Advent I. vasárnapja +1', '12-24 -1'],['Advent I. vasárnapja', '12-26'],
                ['Advent I. vasárnapja', 'December 25. -1'],['Advent I. vasárnapja', '12-23'],['Advent I. vasárnapja', '12-25 -1'],	['Advent I. vasárnapja +1', '12-23'],['Advent I. vasárnapja', '12-20 -1'],	['12-01', '12-23'],['Advent I. vasárnapja +1', '2025-12-23'],
                ['12-01', '12-25 -1'] ,['Advent I. vasárnapja', '12-25']       , ['Advent I. vasárnapja', '12-19'], ['12-01', '12-24 -1'],['Advent I. vasárnapja +1', '12-24'],['Advent I. vasárnapja', '01-02']
        ] ) ) {         
            $periodName =  'Advent';            
        } 

        // Nagyböjt
        else if ( in_array( [$mise->tol, $mise->ig], [
                ['Hamvazószerda', 'Nagycsütörtök'],['Hamvazószerda', 'Nagycsütörtök -1'],
                ['Hamvazószerda', 'Húsvétvasárnap']
                
        ] ) ) { 
            $periodName = 'Nagyböjt';                                    
        }
        // Húsvét
        else if ( in_array( [$mise->tol, $mise->ig], [
                ['Nagykedd', 'Húsvéthétfő'],['Nagycsütörtök', 'Húsvétvasárnap'],['Nagycsütörtök', 'Nagycsütörtök'],['Nagycsütörtök', 'Húsvéthétfő']  
                
        ] ) ) { 
            $periodName = 'Szent három nap';                                    
        }


        //
        // Lássuk hónapokra bontva
        //
        //Januárban
        else if ( in_array( [$mise->tol, $mise->ig], [['01-01', '01-31']] ) ) { 
            $periodName = 'Január';                                    
        }
        // Februárban
        else if ( in_array( [$mise->tol, $mise->ig], [['02-01', '02-28'], ['02-01', '02-29']] ) ) { 
            $periodName = 'Február';                                    
        }
        // Márciusban
        else if ( in_array( [$mise->tol, $mise->ig], [['03-01', '03-31']] ) ) { 
            $periodName = 'Március';                                    
        }
        //Áprilisban
        else if ( in_array( [$mise->tol, $mise->ig], [['04-01', '04-30']] ) ) { 
            $periodName = 'Április';                                    
        }
        // Májusban
        else if ( in_array( [$mise->tol, $mise->ig], [['05-01', '05-31']] ) ) { 
            $periodName = 'Május';                                    
        }
        // Júniusban
        else if ( in_array( [$mise->tol, $mise->ig], [['06-01', '06-30']] ) ) { 
            $periodName = 'Június';                                    
        }
        // Júliusban
        else if ( in_array( [$mise->tol, $mise->ig], [['07-01', '07-31']] ) ) { 
            $periodName = 'Július';                                    
        }
        // Augusztusban
        else if ( in_array( [$mise->tol, $mise->ig], [['08-01', '08-31']] ) ) { 
            $periodName = 'Augusztus';                                    
        }
        // Szeptemberben
        else if ( in_array( [$mise->tol, $mise->ig], [['09-01', '09-30'],['09-01', '09-31']] ) ) { 
            $periodName = 'Szeptember';                                    
        }
        // Októberben
        else if ( in_array( [$mise->tol, $mise->ig], [['10-01', '10-31']] ) ) { 
            $periodName = 'Október';            
        }
        // Novemberben
        else if ( in_array( [$mise->tol, $mise->ig], [['11-01', '11-30']] ) ) { 
            $periodName = 'November';                                    
        }
        // Decemberben
        else if ( in_array( [$mise->tol, $mise->ig], [['12-01', '12-31']] ) ) { 
            $periodName = 'December';                                    
        }
       
        else {
            throw new \Exception("Cannot map mise idoszamitas with tol=".$mise->tol." and ig=".$mise->ig);
        }

        $period = \Eloquent\CalPeriod::where('name', $periodName )->first();
        if (!$period) {
            throw new \Exception("CalPeriod '".$periodName."' not found");   
        }
        

        $year = date('Y');
        $period->start_date = $this->findPeriodStart($period, $year);
        if($period->start_date > date('Y-m-d')) {
            $year - 1;
            $period->start_date = $this->findPeriodStart($period, $year);
        }
        $period->end_date = $this->findPeriodEnd($period, $year);
        if($period->end_date < $period->start_date) {
            $period->end_date = $this->findPeriodEnd($period, $year + 1);
        }
        return $period;
        
        return false;

    }

    public function findPeriodStart ($period, $year) {
        if($period->start_month_day) {
            $start_parts = explode('-', $period->start_month_day);
            
            return sprintf("%04d-%02d-%02d", $year, $start_parts[0], $start_parts[1]);
        }
        
        if($period->start_period_id) {
            $start_period = \Eloquent\CalPeriod::find($period->start_period_id);
            if(!$start_period) {
                throw new \Exception("Start period with id ".$period->start_period_id." not found");
            }
            return $this->findPeriodStart($start_period, $year);
        }

        $year_period = \Eloquent\CalPeriodYear::where('period_id', $period->id)->where('start_year', $year)->first();
        if(!$year_period) {
            throw new \Exception("No year period found for period id ".$period->id." and year ".$year);           
        }
        return $year_period->start_date;

        throw new \Exception("Cannot find start date for period ".$period->id);
        return false;
    }

    public function findPeriodEnd ($period, $year) {
        if($period->multi_day == 0) {
            return $this->findPeriodStart($period, $year);
        }

        if($period->end_month_day) {
            $end_parts = explode('-', $period->end_month_day);
            return sprintf("%04d-%02d-%02d", $year, $end_parts[0], $end_parts[1]);
        }

        if($period->end_period_id) {
            $end_period = \Eloquent\CalPeriod::find($period->end_period_id);
            if(!$end_period) {
                throw new \Exception("End period with id ".$period->end_period_id." not found");
            }
            return $this->findPeriodEnd($end_period, $year);
        }


        throw new \Exception("Cannot find end date for period ".$period->id);
        return false;
    }
    
    // Ha két mise között csak a hét napja a külünbség, akkor szépen összevonjuk
    public function concatDays($misek) {
        
        $return = [];
        $fieldToConcat = ['tid', 'ido', 'nap2', 'tol', 'ig', 'nyelv', 'milyen', 'megjegyzes','torles'];
        
        
        foreach($misek as $mise) {            
            $parts = [];
            foreach ($fieldToConcat as $field) {
                $parts[] = isset($mise->{$field}) ? (string)$mise->{$field} : '';
            }
            $key = implode('_', $parts);
            
            if(!isset($return[$key])) {
                $return[$key] = $mise;
                $return[$key]->nap = [ $mise->nap ];
            } else {
                $return[$key]->nap[] = $mise->nap;
            }                        
        }

        return $return;
        
    }



    public function normalizeMiseAttributes($misek, $templom) {
        $periods = [0,1,2,3,4,5,-1,"ps","pt"];
        $attributes = ['csal','d','ifi','g','cs'];
        
        $attributeMapping = [
            'csal' => 'FAMILY',
            'd'    => 'STUDENT',
            'ifi'  => 'UNIVERSITY_YOUTH',
            'g'    => 'GUITAR',
            'o'   => 'ORGAN',
            'cs'   => 'SILENT'
        ];

        foreach($misek as &$mise) {            
            $mise->types = ['ma'];
            $types = explode(',', strtolower((string)$mise->milyen));
            $mise->attributes = [];            
            foreach($types as $type) {
                $type = trim($type);                                
                $pattern = '/^(' . implode('|', $attributes) . ')(' . implode('|', $periods) . '|)$/i';                
                if (preg_match($pattern, $type, $m)) {                                        
                    $mise->attributes[] = [
                        strtolower($m[1]),
                        isset($m[2]) ? $m[2] : null
                    ];                    
                }
            } 

            foreach($mise->attributes as &$attr) {
                if($attr[1] == "" or $mise->nap2 == $attr[1]) {
                    if(isset($attributeMapping[$attr[0]])) {                        
                        $mise->types[] = $attributeMapping[$attr[0]];
                    } else {
                        throw new \Exception("Unknown attribute code '".$attr[0]."' in mise id=".$mise->id. "(templom id=".$templom->id.")", 30);
                    }
                } else {
                    throw new \Exception("Mismatched attribute period in mise id=".$mise->id. "(templom id=".$templom->id.")", 31);                        
                }
                
            }
         
            return $misek;
        }
    }

    public function normalizeMiseTitleAndRite($misek, $templom) {
        $periods = [0,1,2,3,4,5,-1,"ps","pt"];
        //$liturgies = ['csal','d','ifi','g','cs','gor','rom','regi','vecs','utr','szent'];
        $liturgies = ['gor','rom','regi','vecs','utr','szent'];
        
        foreach($misek as &$mise) {

            $types = explode(',', strtolower((string)$mise->milyen));
            $mise->liturgies = [];            
            foreach($types as $type) {
                $type = trim($type);                                
                $pattern = '/^(' . implode('|', $liturgies) . ')(' . implode('|', $periods) . '|)$/i';                
                if (preg_match($pattern, $type, $m)) {                                        
                    $mise->liturgies[] = [
                        strtolower($m[1]),
                        isset($m[2]) ? $m[2] : null
                    ];                    
                }
            }
            // Ha nincs liturgia megadva, akkor alapértelmezett rítus szerinti szentmise
            if(count($mise->liturgies) == 0 ) {
                if(in_array($templom->egyhazmegye, [17,18,34])) {
                    // Görög katolikus egyházmegyékben görög katolikus rítus
                    $mise->liturgy = 'gor';
                } else {
                    // Minden más esetben római katolikus rítus
                    $mise->liturgy = 'rom';
                }
            }
            // Ha csak egy liturgia van megadva, akkor azt állítjuk be
            else if(count($mise->liturgies) == 1 && ( 
                        $mise->liturgies[0][1] === null or 
                        $mise->liturgies[0][1] == '' or 
                        $mise->liturgies[0][1]  == $mise->nap2 ) ) {
                $mise->liturgy = $mise->liturgies[0][0];
            } // Ha valami bonyolultabb van, akkor egyelőre elvérzünk
            else {                
                $mise->liturgy = implode(',', array_map(function($x){
                    return (string)$x[0] . (isset($x[1]) && $x[1] !== null && $x[1] !== '' ? (string)$x[1] : '');
                }, $mise->liturgies));
                $mise->error = "Complex liturgy found '".$mise->milyen;
                throw new \Exception($mise->error, 25);
                
            }
        }
        
        return $misek;


    }

    // A miséknél a nyelv mező rendezése és optimalizálása
    public function normalizeMiseLanguage($misek, $templom) {
        $languages = unserialize(LANGUAGES);
        $languages['hu'] = $languages['h'];
        $languages = is_array($languages) ? array_keys($languages) : [];
            
        foreach( $misek as &$mise) {
            if(!$mise->nyelv) continue;

            // távolítsa el a végén álló vesszőket és követő szóközöket
            $mise->nyelv = preg_replace('/,+\s*$/', '', (string)$mise->nyelv);

            if($mise->nyelv == 'h2,h4') {
                $mise->nap2 = 'ps';
                $mise->nyelv = 'hu';
            }

            $val = strtolower(trim((string)$mise->nyelv));
            
            if (preg_match('/\b^('.join('|',$languages).')(([1-5]|-1|ps|pt))$\b/', $val, $m)) {
                                              
                // Tulajdonképpen felesleges volt a ciklus, mert a misének is van ciklusa
                if($mise->nap2 == $m[2]) {
                    $mise->nyelv = $m[1];
                    continue;
                } 

                if($mise->nap2 == 0 OR $mise->nap2 == '') {
                    
                    // TODO FIXME -- EZ MIII?
                    $nyelv1 = $m[1];
                    if($m[1] == 'hu' or $m[1] == 'h')  {                        
                        //echo "Hungarian language found in mise id=".$mise->id.", templom_id = ".$mise->tid." updated_at:".$templom->updated_at."<br/>\n";
                        //throw new \Exception("Hungarian language found", 26);

                        $nyelv2 = $this->languageCodeFromOrszagId($templom->orszag);
                        if($nyelv2 == $nyelv1) {
                            $mise->nap2 = $m[2];
                        }
                    }
                    else $nyelv2 = 'hu';

                    if (in_array($m[2], ['ps', 'pt'])) {
                        // current mise gets the matched nap2 and language
                        $mise->nap2 = $m[2];
                        $mise->nyelv = $nyelv1;

                        // create a copy with the opposite nap2 and same language
                        $opposite = ($m[2] === 'ps') ? 'pt' : 'ps';
                        $copy = clone $mise;
                        $copy->nap2 = $opposite;
                        $copy->nyelv = $nyelv2;

                        // append the copy to the collection with a unique key to avoid collisions
                        $misek['dup_' . uniqid()] = $copy;
                    
                    } else if (in_array($m[2], ['1','2','3','4','5'])) {
                        // current mise gets the matched nap2 and language
                        $mise->nap2 = $m[2];
                        $mise->nyelv = $nyelv1;

                        // create copies for other nap2 values
                        for ($day = 1; $day <= 5; $day++) {
                            if ($day == (int)$m[2]) continue; // skip the current nap2

                            $copy = clone $mise;
                            $copy->nap2 = (string)$day;
                            $copy->nyelv = $nyelv2;

                            // append the copy to the collection with a unique key to avoid collisions
                            $misek['dup_' . uniqid()] = $copy;
                        }
                    
                    } else if ($m[2] == '-1') {
                        // current mise gets the matched nap2 and language
                        $mise->nap2 = $m[2];
                        $mise->nyelv = $nyelv1;

                        // create copies for other nap2 values including 'ps' and 'pt'
                        for ($day = 1; $day <= 4; $day++) {
                            $copy = clone $mise;
                            $copy->nap2 = (string)$day;
                            $copy->nyelv = $nyelv2;

                            // append the copy to the collection with a unique key to avoid collisions
                            $misek['dup_' . uniqid()] = $copy;
                        }
                        
                     
                    }


                    
                    continue;
                }

            } else if (preg_match('/^(?:(de|h|hu|sk)[1-5](?:\s*,\s*(de|h|hu|sk)[1-5]){3,4})$/i', $val, $m) AND ( $mise->nap2 == 0 OR $mise->nap2 == '' ) ) {
                $weeks = explode(',',$m[0]);
                
                $first = true;
                foreach ($weeks as $w) {
                    $w = trim($w);
                    if (preg_match('/^(de|h|hu|sk)([1-5])$/i', $w, $m2)) {
                        
                        if ($first) {
                            $mise->nyelv = $m2[1];
                            $mise->nap2 = $m2[2];
                            $first = false;
                        } else {
                            $copy = clone $mise;
                            $copy->nyelv = $m2[1];
                            $copy->nap2 = $m2[2];
                            $misek['dup_' . uniqid()] = $copy;
                        }
                    }
                }        

            }

            // Ha minden hétre más nyelv van megadva
            $pattern = '/^(?:(?:'.join('|',$languages).'|h)[1-5])(?:,(?:'.join('|',$languages).')[1-5]){4}$/';
            if( preg_match($pattern, $val) ) {
                $pairs = explode(',', $val);
                $first = true;
                foreach ($pairs as $p) {
                    $p = trim($p);
                    if (!preg_match('/^([a-z]{1,2})([1-5])$/i', $p, $m2)) {
                        continue;
                    }
                    $lang = strtolower($m2[1]);
                    $day = (string)$m2[2];

                    if ($first) {
                        $mise->nyelv = $lang;
                        $mise->nap2 = $day;
                        $first = false;
                    } else {
                        $copy = clone $mise;
                        $copy->nyelv = $lang;
                        $copy->nap2 = $day;
                        $misek['dup_' . uniqid()] = $copy;
                    }
                }
            }





        }


        return $misek;
    }

    public function languageCodeFromOrszagId($id) {
        $map = [
            1  => 'de', // Ausztria
            2  => 'nl', // Belgium
            3  => 'bg', // Bulgária
            4  => 'el', // Ciprus
            5  => 'cs', // Cseh Köztársaság
            6  => 'da', // Dánia
            7  => 'et', // Észtország
            8  => 'fi', // Finnország
            9  => 'fr', // Franciaország
            10 => 'de', // Németország
            11 => 'el', // Görögország
            12 => 'hu', // Magyarország
            13 => 'it', // Olaszország
            14 => 'en', // Írország
            15 => 'is', // Izland
            16 => 'lv', // Lettország
            17 => 'de', // Liechtenstein
            18 => 'de', // Luxemburg
            19 => 'lt', // Litvánia
            20 => 'mt', // Málta
            21 => 'nl', // Hollandia
            22 => 'no', // Norvégia
            23 => 'pl', // Lengyelország
            24 => 'pt', // Portugália
            25 => 'ro', // Románia
        ];

        return isset($map[(int)$id]) ? $map[(int)$id] : null;
    }

    
}