<?php

namespace Html\Church;

class EditSchedule extends \Html\Html {

    public function __construct($path) {
        global$user;

        $this->tid = $path[0];

        $jog = false;
        if ($user->checkRole('miserend')) {
            $jog = true;
        }

        if ($jog == false AND isset($_REQUEST['tid']) AND is_numeric($_REQUEST['tid']) AND in_array($m_op, array('addtemplom', 'addmise', 'addingtemplom', 'addingmise'))) {
            $church = getChurch($_REQUEST['tid']);
            if ($user->checkRole('miserend') OR in_array($user->login, $church['responsible']) OR in_array($user->login, $church['diocese']['responsible']))
                $jog = true;
            else
                $jog = false;
        } elseif (( $m_op == 'index' OR $m_op == 'modtemplom' )AND ( $user->checkRole('miserend') OR count($user->responsible['diocese']) > 0 )) {
            $jog = true;
        }

        if ($jog == true) {
            $isForm = \Request::Text('submit');
            if ($isForm) {
                $this->modify();
            }
            $this->preparePage();
        } else {
            $this->title = 'Hiányzó jogosultság!';
            addMessage('Hiányzó jogosultság!', 'danger');
        }
    }

    function modify() {
        global $user;

        $most = date('Y-m-d H:i:s');

        foreach ($_REQUEST as $k => $i)
            $_REQUEST[$k] = sanitize($i);
        if (!is_numeric($_REQUEST['tid']))
            die('tid csak numeric');

        //DELETE
        if (isset($_REQUEST['delete']['period'])) {
            foreach ($_REQUEST['delete']['period'] as $period) {
                $query = "UPDATE misek SET torles = '" . $most . "', torolte = '" . $user->login . "' WHERE tid = " . $_REQUEST['tid'] . " AND idoszamitas = '" . $period . "' ;";
                mysql_query($query);
            }
        }
        if (isset($_REQUEST['delete']['particular'])) {
            foreach ($_REQUEST['delete']['particular'] as $particular) {
                $query = "UPDATE misek SET torles = '" . $most . "', torolte = '" . $user->login . "' WHERE tid = " . $_REQUEST['tid'] . " AND idoszamitas = '" . $particular . "' ;";
                mysql_query($query);
            }
        }
        if (isset($_REQUEST['delete']['mass'])) {
            foreach ($_REQUEST['delete']['mass'] as $mid) {
                $query = "UPDATE misek SET torles = '" . $most . "', torolte = '" . $user->login . "' WHERE tid = " . $_REQUEST['tid'] . " AND id = '" . $mid . "' LIMIT 1;";
                mysql_query($query);
            }
        }

        //UPDATE
        if (is_array($_REQUEST['period'])) {
            foreach ($_REQUEST['period'] as $period) {
                foreach ($period as $key => $mass) {
                    if (is_numeric($key)) {
                        $mass['tid'] = $_REQUEST['tid'];
                        $mass['idoszamitas'] = sanitize($period['name']);
                        $mass['weight'] = $period['weight'];
                        $mass['tol'] = sanitize($period['from']);

                        if ($period['from2'] != 0)
                            $mass['tol'] .= ' ' . $period['from2'];
                        $mass['ig'] = sanitize($period['to']);
                        if ($period['to2'] != 0)
                            $mass['ig'] .= ' ' . $period['to2'];

                        $mass['milyen'] = cleanMassAttr($mass['milyen']);
                        $mass['nyelv'] = cleanMassAttr($mass['nyelv']);


                        if ($mass['id'] != 'new') {
                            $query = "UPDATE misek SET ";
                            $query .= "nap='" . $mass['napid'] . "',ido='" . $mass['ido'] . ":00',nap2='" . $mass['nap2'] . "',idoszamitas='" . $mass['idoszamitas'] . "',weight='" . $mass['weight'] . "',tol='" . $mass['tol'] . "',ig='" . $mass['ig'] . "',nyelv='" . $mass['nyelv'] . "',milyen='" . $mass['milyen'] . "',megjegyzes='" . $mass['megjegyzes'] . "',";
                            $query .= "modositotta='" . $user->login . "',moddatum='" . $most . "'";
                            $query .= " WHERE tid = " . $mass['tid'] . " AND id = " . $mass['id'] . " LIMIT 1";
                        } else {
                            $query = "INSERT INTO misek ";
                            $query .= " (tid,nap,ido,nap2,idoszamitas,weight,tol,ig,nyelv,milyen,megjegyzes,modositotta,moddatum) ";
                            $query .= " VALUES ('" . $mass['tid'] . "','" . $mass['napid'] . "','" . $mass['ido'] . ":00','" . $mass['nap2'] . "','" . $mass['idoszamitas'] . "','" . $mass['weight'] . "','" . $mass['tol'] . "','" . $mass['ig'] . "','" . $mass['nyelv'] . "','" . $mass['milyen'] . "','" . $mass['megjegyzes'] . "',";
                            $query .= "'" . $user->login . "','" . $most . "')";
                        }
                        mysql_query($query);
                    }
                }
            }
        }
        if (is_array($_REQUEST['particular'])) {
            foreach ($_REQUEST['particular'] as $particular) {
                foreach ($particular as $key => $mass) {
                    if (is_numeric($key)) {
                        $mass['tid'] = $_REQUEST['tid'];
                        $mass['idoszamitas'] = sanitize($particular['name']);
                        $mass['weight'] = $particular['weight'];
                        $mass['tol'] = sanitize($particular['from']);
                        if ($particular['from2'] != 0)
                            $mass['tol'] .= ' ' . $particular['from2'];
                        $mass['ig'] = $mass['tol'];
                        $mass['napid'] = 0;

                        if ($mass['id'] != 'new') {
                            $query = "UPDATE misek SET ";
                            $query .= "nap='" . $mass['napid'] . "',ido='" . $mass['ido'] . ":00',nap2='" . $mass['nap2'] . "',idoszamitas='" . $mass['idoszamitas'] . "',weight='" . $mass['weight'] . "',tol='" . $mass['tol'] . "',ig='" . $mass['ig'] . "',nyelv='" . $mass['nyelv'] . "',milyen='" . $mass['milyen'] . "',megjegyzes='" . $mass['megjegyzes'] . "',";
                            $query .= "modositotta='" . $user->login . "',moddatum='" . $most . "'";
                            $query .= " WHERE tid = " . $mass['tid'] . " AND id = " . $mass['id'] . " LIMIT 1";
                        } else {
                            $query = "INSERT INTO misek ";
                            $query .= " (tid,nap,ido,nap2,idoszamitas,weight,tol,ig,nyelv,milyen,megjegyzes,modositotta,moddatum) ";
                            $query .= " VALUES ('" . $mass['tid'] . "','" . $mass['napid'] . "','" . $mass['ido'] . ":00','" . $mass['nap2'] . "','" . $mass['idoszamitas'] . "','" . $mass['weight'] . "','" . $mass['tol'] . "','" . $mass['ig'] . "','" . $mass['nyelv'] . "','" . $mass['milyen'] . "','" . $mass['megjegyzes'] . "',";
                            $query .= "'" . $user->login . "','" . $most . "')";
                        }
                        mysql_query($query);
                    }
                }
            }
        }
        generateMassTmp('tid = ' . $_REQUEST['tid']);


        //LOG
        $ip = $_SERVER['REMOTE_ADDR'];
        $host = gethostbyaddr($ip);
        $tid = $_REQUEST['tid'];
        $ma = date('Y-m-d');
        list($log) = mysql_fetch_row(mysql_query("select log from templomok where id='$tid'"));
        $log.="\nMISE_MOD: " . $user->login . " ($most - [$ip - $host])";
        if ($_REQUEST['update'] == 'i')
            $frissites = ", frissites='$ma'";
        $_REQUEST['misemegj'] = preg_replace('/<br\/>/i', "\n", $_REQUEST['misemegj']);
        $_REQUEST['adminmegj'] = preg_replace('/<br\/>/i', "\n", $_REQUEST['adminmegj']);
        $query = "update templomok set miseaktiv='" . $_REQUEST['miseaktiv'] . "', misemegj='" . $_REQUEST['misemegj'] . "', adminmegj='" . $_REQUEST['adminmegj'] . "', log='$log' $frissites where id='$tid' LIMIT 1";
        mysql_query($query);


        $modosit = $_REQUEST['modosit'];
        if ($modosit == 'i') {
            return;
        } elseif ($modosit == 'm') {
        //redirect to edit church
        //$kod = miserend_addtemplom($tid);
        } elseif ($modosit == 't') {
            header('Location: /templom/' . $tid);
            die();
        } else {
            //redirect to edit church
        }
    }

    function preparePage() {

        //Ezt csak a development szerveren kéne
        $this->update_addmisejs();

        $this->church = new \Church($this->tid);
        $masses = getMasses($this->tid);

        //Észrevétel
        $jelzes = getRemarkMark($this->tid);
        $this->jelzes = $jelzes;

        //miseaktív
        if ($this->church->miseaktiv == 1)
            $this->active['yes'] = ' checked ';
        else
            $this->active['no'] = ' checked ';


        $this->lastperiod = 0;
        if (isset($masses['periods']))
            foreach ($masses['periods'] as $pkey => $period) {
                $this->periods[] = formPeriod($pkey, $period, 'period');
            }
        $this->lastperiod = $pkey;

        $this->lastparticular = 0;
        if (isset($masses['particulars']))
            foreach ($masses['particulars'] as $pkey => $particular) {
                $this->particulars[] = formPeriod($pkey, $particular, 'particular');
            }
        $this->lastparticular = $pkey;

        $this->misemegj = array(
            'type' => 'textbox',
            'name' => "misemegj",
            'value' => $this->church->misemegj,
            'label' => 'Rendszeres rózsafűzér, szentségimádás, hittan, stb.<br/>');
        $this->adminmegj = array(
            'type' => 'textbox',
            'name' => "adminmegj",
            'value' => $this->church->adminmegj,
            'labelback' => ' A templom szerkesztésével kacsolatosan.');

        $this->update = array(
            'type' => 'checkbox',
            'name' => "update",
            'value' => 'i',
            'checked' => true,
            'labelback' => 'Utoljára frissítve: ' . date('Y.m.d.', strtotime($this->church->frissites))
        );

        $this->helptext = '<span class="alap">Figyelem! Ha átfedés van két periódus/időszak vagy különleges miserend között, akkor a listában lejjebb lévő vagyis „nehezebb” periódus vagy különleges miserend jelenik meg a keresőben!</span>';
    }

    function update_addmisejs() {
        $file = 'js/miserend_addmise.js';
        if (is_writable($file)) {
            if (filemtime('load.php') > filemtime($file)) {
                $text = file_get_contents($file);
                $text = preg_replace_callback("/(\/\*([A-Z]{1,15})\*\/)(.*?)(\/\*\/([A-Z]{1,15})\*\/)/i", function ($match) {
                    $attributes = unserialize(constant($match[2]));
                    foreach ($attributes as $abbrev => $attribute) {
                        $tmp[] = $abbrev;
                    }
                    $return = $match[1] . " var " . strtolower($match[2]) . " = ['" . implode("','", $tmp) . "']; " . $match[4];
                    return $return;
                }, $text);
                file_put_contents($file, $text);
            }
        }
    }

}
