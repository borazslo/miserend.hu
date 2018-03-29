<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class DioceseCatalogue extends Html {

    public function __construct($path) {
        global $user;

        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod megnézni a templomok listáját.');
        }

        $this->title = "Templomok listája egyházmegyénként";
        $ehm = !empty($_REQUEST['ehm']) ? $_REQUEST['ehm'] : 'false';
        
        $ehmsDB = DB::table('egyhazmegye')->where('ok','i')->orderBy('sorrend')->get();
        $this->ehms = array();        
        foreach($ehmsDB as $tmp) {
            $this->ehms[$tmp->id] = $tmp;
        }
               
        if (is_numeric($ehm) AND $ehm > 0) {
            $this->ehms[$ehm]->selected = "selected";
                                                          
            $this->title = "Templomok listája: ".$this->ehms[$ehm]->nev. " egyházmegye";
            
            $espkersDB = DB::table('espereskerulet')->where('ehm',$ehm)->orderBy('nev')->get();
            
            $this->espkers = array();
            foreach($espkersDB as $espker) {
                $this->espkers[$espker->id] = $espker->nev;
            }            
          
            $this->churchesGroupByEspker = \Eloquent\Church::where('ok','i')
                    ->where('egyhazmegye',$ehm)
                    ->orderBy('varos')->orderBy('nev')
                    ->get()->groupBy('espereskerulet');
            }
            
            return;
            $query = "select templomok.id,templomok.nev,templomok.varos,espereskerulet.nev from espereskerulet, templomok where espereskerulet.id=templomok.espereskerulet and templomok.egyhazmegye=$ehm order by templomok.espereskerulet, templomok.varos";
            if (!$lekerdez = mysql_query($query))
                echo "<br>HIBA!<br>$query<br>" . mysql_error();
            $a = 0;
            $excel = '';
            while (list($tid, $tnev, $varos, $espker) = mysql_fetch_row($lekerdez)) {
                $a++;
                if (!isset($espkerell) OR $espker != $espkerell) {
                    $txt.= "<br><h3>$espker espereskerület</h3>";
                    $espkerell = $espker;
                }
                $txt.= "$a. [$tid] $tnev ($varos)<br>";
                $excel.="\n$tid;$tnev;$varos;$espker";
            }
            $txt.="<br><br><span class=alap>Az alábbi szöveget kimásolva excelbe importálható.<br>Excelben: Adatok / Szövegből oszlopok -> táblázattá alakítható</span><br><textarea class=urlap cols=60 rows=20>$excel</textarea>";
        }


}
