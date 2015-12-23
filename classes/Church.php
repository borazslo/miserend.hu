<?php

use Illuminate\Database\Capsule\Manager as DB;

class Church {

    public $id;

    public function __construct($tid) {
        $this->id = $tid;

        $result = DB::table('templomok')
                ->join('terkep_geocode as geo', 'geo.tid', '=', 'templomok.id', 'left')
                ->join('varosok', 'varosok.nev', '=', 'templomok.varos', 'left')
                ->select('templomok.*', 'geo.lat', 'geo.lng', 'geo.checked', 'geo.address2', 'varosok.irsz')
                ->where('templomok.id', "=", $this->id)
                ->limit(1)
                ->get();
        if (!count($result)) {
            throw new Exception("There is no church with tid = '$this->id'.");
        }

        if ($result[0]->letrehozta != '') {
            $this->responsible = array($result[0]->letrehozta);
        } else {
            $this->responsible = array();
        }

        $acceptedColumns = array('nev', 'ismertnev',
            'leiras', 'megjegyzes', 'adminmegj',
            'miseaktiv', 'misemegj', 'bucsu',
            'frissites', 'eszrevetel',
            'kontakt', 'kontaktmail', 'letrehozta', 'megbizhato', 'regdatum', 'modositotta', 'moddatum', 'ok');
        foreach ($acceptedColumns as $column) {
            $this->$column = $result[0]->$column;
        }
        $this->getFullName();

        global $user;
        if (!$this->checkPrivilegeRead($user)) {
            throw new Exception("Read access denied to church tid = '$tid'");
        }

        $this->getOSM();
        $this->getLocation();
        $this->getReligious_administration();

        $this->kepek = getImages($this->id);
    }

    function getOSM() {
        try {
            $this->osm = new OSM();
            $this->osm->getByChurchId($this->id);
        } catch (Exception $e) {
            //OSM data is not obligatory
            //echo $e->getMessage();
        }
    }

    function getReligious_administration() {
        $this->religious_administration = new stdClass();
        $this->religious_administration->diocese = new Diocese();
        $this->religious_administration->diocese->getByChurchId($this->id);
        $this->religious_administration->deaconry = new Deaconry();
        $this->religious_administration->deaconry->getByChurchId($this->id);
        $this->getParish();
    }

    function getParish() {
        if (!isset($this->religious_administration)) {
            $this->religious_administration = new stdClass();
        }
        $parish = new Parish();
        $parish->getByChurchId($this->id);
        $this->religious_administration->parish = $parish;
    }

    function getLocation() {
        $this->location = new Location();
        $this->location->getByChurchId($this->id);
    }

    function getNeighbourChurches() {
        $neighbours = DB::select("SELECT d.distance tavolsag,t.nev,t.ismertnev,t.varos,t.id tid FROM distance as d
            LEFT JOIN templomok as t ON (tid1 <> :tid1 AND tid1 = id ) OR (tid2 <> :tid2 AND tid2 = id )
            WHERE ( tid1 = :tid3 OR tid2 = :tid4 ) AND distance <= 10000 
            AND t.id IS NOT NULL 
            ORDER BY distance ", ['tid1' => $this->id, 'tid2' => $this->id, 'tid3' => $this->id, 'tid4' => $this->id]);
        $this->neigbourChurches = (array) $neighbours;

        if (!isset($this->neigbourChurches)) {
            $neighbours = DB::select("SELECT d.distance tavolsag,t.nev,t.ismertnev,t.varos,t.id tid FROM distance as d
                LEFT JOIN templomok as t ON (tid1 <> :tid1 AND tid1 = id ) OR (tid2 <> :tid2 AND tid2 = id )
                WHERE ( tid1 = :tid3 OR tid2 = :tid4 )
                ORDER BY distance 
                LIMIT 1", ['tid1' => $this->id, 'tid2' => $this->id, 'tid3' => $this->id, 'tid4' => $this->id]);
            $this->neigbourChurches = (array) $neighbours;
        }
    }

    function checkPrivilegeRead($user) {
        if ($this->ok == 'i')
            return true;
        if ($this->letrehozta == $user->username)
            return true;
        if ($user->checkRole('miserend'))
            return true;
        return false;
    }

    function allowOldStructure() {
        foreach ($this->location as $k => $v) {
            $this->$k = $v;
        }
        if (!$this->neighbourChurches)
            $this->getNeighbourChurches();
        foreach ($this->neigbourChurches as $neighbour) {
            $this->szomszedok[] = (array) $neighbour;
        }
        $this->plebania = $this->religious_administration->parish->description;
        $this->pleb_url = $this->religious_administration->parish->url;
        $this->pleb_eml = $this->religious_administration->parish->email;
        $this->egyhazmegye = $this->religious_administration->diocese->name;
        $this->espereskerulet = $this->religious_administration->deaconry->shortname;
    }

    function getFullName() {
        $this->fullName = $this->nev;
        if (!empty($this->ismertnev)) {
            $this->fullName .= '(' . $this->ismertnev . ')';
        } else {
            $this->fullName .= '(' . $this->varos . ')';
        }
    }

    function getRemarks() {
        $results = DB::table('eszrevetelek')
                ->select('id')
                ->where('hol_id', "=", $this->id)
                ->orderBy('datum', 'desc')
                ->get();

        $this->remarks = array();
        foreach ($results as $result) {
            $result = (array) $result;
            $tmp = new \Remark($result['id']);
            if (isset($tmp) AND $tmp->id > 0)
                $this->remarks[$result['id']] = $tmp;
        }
    }

    function delete() {
        $tid = $this->id;
        $query = "delete from templomok where id='$tid'";
        mysql_query($query);

        //Miséket is töröljük
        $query = "delete from misek where templom='$tid'";
        mysql_query($query);

        //És kiszedi a törölt szomszédosokat!!!
        $query = "select id, szomszedos1, szomszedos2 from templomok where szomszedos1 like '%-$tid-%' or szomszedos2 like '%-$tid-%'";
        $lekerdez = mysql_query($query);
        while (list($szid, $sz1, $sz2) = mysql_fetch_row($lekerdez)) {
            if (strstr($sz1, $tid)) {
                //Ha a másik templomnál szerepel a mi templomunk
                //akkor töröljük onnan is a hozzárendelést!
                $sz1 = str_replace('--', '!', $sz1);
                $sz1 = str_replace('-', '', $sz1);
                $sz1T = explode('!', $sz1);
                foreach ($sz1T as $ertek) {
                    if ($ertek != $tid) {
                        $ujsz1T[] = $ertek;
                    }
                }
                if (is_array($ujsz1T))
                    $ujsz1 = '-' . implode('--', $ujsz1T) . '-';
                else
                    $ujsz1 = '';
                mysql_query("update templomok set szomszedos1='$ujsz1' where id='$szid'");
            }
            if (strstr($sz2, $tid)) {
                //Ha a másik templomnál szerepel a mi templomunk
                //akkor töröljük onnan is a hozzárendelést!
                $sz2 = str_replace('--', '!', $sz2);
                $sz2 = str_replace('-', '', $sz2);
                $sz2T = explode('!', $sz2);
                foreach ($sz2T as $ertek) {
                    if ($ertek != $tid) {
                        $ujsz2T[] = $ertek;
                    }
                }
                if (is_array($ujsz2T))
                    $ujsz2 = '-' . implode('--', $ujsz2T) . '-';
                else
                    $ujsz2 = '';
                mysql_query("update templomok set szomszedos2='$ujsz2' where id='$szid'");
            }
        }

        //Fájlokat és képeket is törölni kell!
        //Könyvtár tartalmát beolvassa
        $konyvtar = "fajlok/templomok/$tid";
        if (is_dir($konyvtar)) {
            $handle = opendir($konyvtar);
            while ($file = readdir($handle)) {
                if ($file != '.' and $file != '..') {
                    @unlink("$konyvtar/$file");
                }
            }
            closedir($handle);
        }

        //Könyvtár tartalmát beolvassa
        $konyvtar = "kepek/templomok/$tid";
        if (is_dir($konyvtar)) {
            $handle = opendir($konyvtar);
            while ($file = readdir($handle)) {
                if ($file != '.' and $file != '..' and $file != 'fokep' and $file != 'kicsi') {
                    unlink("$konyvtar/$file");
                }
            }
            closedir($handle);
        }
        $konyvtar = "kepek/templomok/$tid/kicsi";
        if (is_dir($konyvtar)) {
            $handle = opendir($konyvtar);
            while ($file = readdir($handle)) {
                if ($file != '.' and $file != '..') {
                    unlink("$konyvtar/$file");
                }
            }
            closedir($handle);
        }
    }

}
