<?php

include_once 'config.inc';
dbconnect();

//migrate('misek','misek2');

print_r(getMisek(1));
/*

$stmt = $db->prepare("SELECT * FROM misek2 WHERE animal_id = :animal_id AND animal_name = :animal_name");
$stmt->bindParam(':animal_id', $animal_id, PDO::PARAM_INT);
$stmt->bindParam(':animal_name', $animal_name, PDO::PARAM_STR, 5);
$stmt->execute();
print_R($stmt->fetchAll());
*/

function getMisek($templomid = false,$datum = false) {
    global $db;
    if($templomid) $where = ' templomid = '.$templomid.' AND ';
    if(!$datum) {
        $query = "SELECT * FROM idoszakok LEFT JOIN misek2 ON misek2.idoszakid = idoszakok.id WHERE ".$where." misek2.nap <> ''";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $misek = array();
        foreach($results as $result) {
            $misek[$result['idoszakid']][] = $result;
        }        
        return $misek;        
    } else {
        $query = "SELECT * FROM idoszakok LEFT JOIN misek2 ON misek2.idoszakid = idoszakok.id WHERE ".$where." misek2.nap <> '' GROUP BY idoszakid";
        echo $query;
       $stmt = $db->prepare($query);
       $stmt->execute();
       $idoszakok = $stmt->fetchAll();
       $idk = array();
       foreach($idoszakok as $idoszak) {
              $in = false;
              $tolakt = getHatarEvben($idoszak['tol'],$idoszak['tolt'],date('Y',strtotime($datum)));
              $tolelozo = getHatarEvben($idoszak['tol'],$idoszak['tolt'],date('Y',strtotime($datum." -1 year")));
              if($tolakt <= $datum ) {
                if($datum <= getHatarHatartol($idoszak['ig'],$idoszak['igt'],$tolakt)) {
                    $in = true;
                }              
              } elseif ($tolelozo <= $datum) {
                if($datum <= getHatarHatartol($idoszak['ig'],$idoszak['igt'],$tolelozo)) {
                    $in = true;
                }
              }
              if($in) $idk[] = ' idoszakid = '.$idoszak['idoszakid'];
       }
       $query = "SELECT * FROM idoszakok 
            LEFT JOIN misek2 ON misek2.idoszakid = idoszakok.id 
            WHERE ".$where." 
                (".implode(' OR ',$idk).") 
                AND misek2.nap <> ''
                AND nap = ".date('N',strtotime($datum));
       $stmt = $db->prepare($query);
       $stmt->execute();
       $misek = $stmt->fetchAll();
       return $misek;       
    }
}


function getHatarEvben($nev, $t, $ev) {
    global $db;
    if(preg_match('/[0-9]{1,2}-[0-9]{1,2}/i',$nev)) {
        $return = $ev."-".$nev;
    } else {
        $stmt = $db->prepare("SELECT datum FROM unnepek WHERE nev = '".$nev."' AND ev = '".$ev."' LIMIT 1");
        $stmt->execute();
        $hatar = $stmt->fetch();
        if(!isset($hatar['datum'])) die('Hiányzó ünnepi adat! :( '.$nev."|".$t."|".$ev);
        $return = $hatar['datum'];
    }
    if($t > 0) $t = '+1 day';
    elseif($t == 0) $t = '';
    elseif($t < 0) $t = '-1 day';
    $return = date('Y-m-d',strtotime($return." ".$t));    
    return $return;
}

function getHatarHatartol($nev, $t, $hatar) {
    $ezevben = getHatarEvben($nev,$t,date('Y',strtotime($hatar)));
    if( $ezevben > $hatar) return $ezevben;
    else return getHatarEvben($nev,$t,date('Y',strtotime($hatar." +1 year")));
    return;
}

function migrate($old,$updated) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM ".$old." WHERE torles = '0000-00-00 00:00:00' ORDER BY templom LIMIT 200000");
    $stmt->execute();
    $regimisek = $stmt->fetchAll();
    
    $idonevek = array('ny'=>'nyári','t'=>'téli');
    $idoszakok = array();
    foreach($regimisek as $regimise) {
        $tmp = array();
        $tmp['templomid'] = $regimise['templom'];
        $tmp['nev'] = $idonevek[$regimise['idoszamitas']];
        if($regimise['idoszamitas'] == 't') {
            $tmp['tol'] = date('m-d',strtotime($regimise['datumig']." +1 day"));
            $tmp['ig'] = date('m-d',strtotime($regimise['datumtol']." -1 day"));
        } else {
            $tmp['tol'] = date('m-d',strtotime($regimise['datumtol']));
            $tmp['ig'] = date('m-d',strtotime($regimise['datumig']));
        }
        if(!isset($idoszakok[$regimise['templom']][$regimise['idoszamitas']])) $idoszakok[$regimise['templom']][$regimise['idoszamitas']]  = $tmp;
        $idoszakok[$regimise['templom']][$regimise['idoszamitas']]['misek'][] = $regimise;
        
    }
    foreach($idoszakok as $idoszaktny) {
        foreach($idoszaktny as $idoszak) {
            $query = "INSERT INTO idoszakok (templomid,nev,tol,ig) VALUES (".$idoszak['templomid'].",'".$idoszak['nev']."','".$idoszak['tol']."','".$idoszak['ig']."');";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $idoszakId = $db->lastInsertId() ;
            if($idoszakId == 0) {
                $query = "SELECT id FROM idoszakok WHERE templomid = ".$idoszak['templomid']." AND nev = '".$idoszak['nev']."' AND tol = '".$idoszak['tol']."' AND ig = '".$idoszak['ig']."' LIMIT 1;";
                $stmt = $db->prepare($query);                
                $stmt->execute();
                $result = $stmt->fetch();
                $idoszakId = $result['id'];
            }
            foreach($idoszak['misek'] as $mise) {
                $query = "INSERT INTO ".$updated." (templom,nap,ido,idoszakid,nyelv,milyen,megjegyzes,modositotta,moddatum,torles,torolte) VALUES
                    ('".$mise['templom']."','".$mise['nap']."','".$mise['ido']."',".$idoszakId.",'".$mise['nyelv']."','".$mise['milyen']."','".$mise['megjegyzes']."','".$mise['modositotta']."','".$mise['moddatum']."','".$mise['torles']."','".$mise['torolte']."');";
                $stmt = $db->prepare($query);                
                $stmt->execute();
            }
        }
    }

    
    exit;

}

?>