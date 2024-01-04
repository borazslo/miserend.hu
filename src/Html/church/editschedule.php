<?php

namespace App\Html\Church;

use Illuminate\Database\Capsule\Manager as DB;

class EditSchedule extends \App\Html\Html {

    public function __construct($path) {
        $this->tid = $path[0];

        $this->church = \App\Model\Church::find($this->tid)->append(['writeAccess']);;
        if (!$this->church) {
            throw new \Exception('Nincs ilyen templom.');
        }
        
        if (!$this->church->writeAccess) {
            throw new \Exception('Hiányzó jogosultság!');
            return;
        }

        $isForm = \App\Request::Text('submit');
        if ($isForm) {
            $this->modify();
        }
        $this->preparePage();
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
                DB::table('misek')
                        ->where('tid',$_REQUEST['tid'])
                        ->where('idoszamitas',$period)
                        ->update([
                            'torles' => $most,
                            'torolte' => $user->login
                        ]);                
            }
        }
        if (isset($_REQUEST['delete']['particular'])) {
            foreach ($_REQUEST['delete']['particular'] as $particular) {
                DB::table('misek')
                        ->where('tid',$_REQUEST['tid'])
                        ->where('idoszamitas',$particular)
                        ->update([
                            'torles' => $most,
                            'torolte' => $user->login
                        ]);                
            }
        }
        if (isset($_REQUEST['delete']['mass'])) {
            foreach ($_REQUEST['delete']['mass'] as $mid) {
                DB::table('misek')
                        ->where('tid',$_REQUEST['tid'])
                        ->where('id',$mid)
                        ->update([
                            'torles' => $most,
                            'torolte' => $user->login
                        ]);                
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


                        $data = [
                            'nap' => $mass['napid'],
                            'ido' => $mass['ido'] . ":00",
                            'nap2' => $mass['nap2'],
                            'idoszamitas' => $mass['idoszamitas'],
                            'weight' =>  $mass['weight'],
                            'tol' => $mass['tol'],
                            'ig' => $mass['ig'],
                            'nyelv' => $mass['nyelv'],
                            'milyen' => $mass['milyen'],
                            'megjegyzes' => $mass['megjegyzes'],                                                           
                        ];
                               
                        if ($mass['id'] != 'new') {
                            DB::table('misek')
                                    ->where('tid',$mass['tid'])
                                    ->where('id',$mass['id'])
                                    ->update($data);
                        } else {
                            $data['modositotta'] = $user->login; 
                            $data['moddatum'] = $most;
                            $data['tid'] = $mass['tid'];
                                                                                    
                            DB::table('misek')                                    
                                    ->insert($data);                                                        
                        }
                    }
                }
            }
        }
        if (isset($_REQUEST['particular']) AND is_array($_REQUEST['particular'])) {
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

                      $data = [
                            'nap' => $mass['napid'],
                            'ido' => $mass['ido'] . ":00",
                            'nap2' => isset($mass['nap2']) ? $mass['nap2'] : false,
                            'idoszamitas' => $mass['idoszamitas'],
                            'weight' =>  $mass['weight'],
                            'tol' => $mass['tol'],
                            'ig' => $mass['ig'],
                            'nyelv' => $mass['nyelv'],
                            'milyen' => $mass['milyen'],
                            'megjegyzes' => $mass['megjegyzes'],                                                           
                        ];
                               
                        if ($mass['id'] != 'new') {
                            DB::table('misek')
                                    ->where('tid',$mass['tid'])
                                    ->where('id',$mass['id'])
                                    ->update($data);
                        } else {
                            $data['modositotta'] = $user->login; 
                            $data['moddatum'] = $most;
                            $data['tid'] = $mass['tid'];
                                                                                    
                            DB::table('misek')                                    
                                    ->insert($data);                                                        
                        }
                    }
                }
            }
        }
       \App\Crons::generateMassTolIgTmp('tid = ' . $_REQUEST['tid']);

        $this->church->log .= "\nMISE_MOD: " . $user->login . " (" . date('Y-m-d H:i:s') . " - [" . $_SERVER['REMOTE_ADDR'] . " - " . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "])";
        if ($_REQUEST['update'] == 'i')
            $this->church->frissites = date('Y-m-d');
        $this->church->misemegj = preg_replace('/<br\/>/i', "\n", $_REQUEST['misemegj']);
        $this->church->adminmegj = preg_replace('/<br\/>/i', "\n", $_REQUEST['adminmegj']);
        $this->church->miseaktiv = $_REQUEST['miseaktiv'];        
        
        /* Valamiért a writeAcess nem az igazi és mivel nincs a tálában ezért kiakadt...*/
        $model = $this->church;
        foreach ($model->getAttributes() as $key => $value) {
        if(!in_array($key, array_keys($model->getOriginal())))
            unset($model->$key);
        }
        $model->save();
        

        $modosit = $_REQUEST['modosit'];
        if ($modosit == 'i') {
            return;
        } elseif ($modosit == 'm') {
            $this->redirect('/templom/' . $this->tid . "/edit");
        } elseif ($modosit == 't') {
            $this->redirect('/templom/' . $this->tid);
        } else {
            $this->redirect('/templom/catalogue');
        }
    }

    function preparePage() {

        //Ezt csak a development szerveren kéne
        $this->update_addmisejs();

        $masses = getMasses($this->tid);

        //Észrevétel        
        $this->jelzes = $this->church->remarksStatus;
		
		 		

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
        if (!isset($pkey))
            $pkey = 0;
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
			'class' => 'tinymce',
            'value' => $this->church->misemegj,
            'label' => 'Rendszeres rózsafűzér, szentségimádás, hittan, stb.<br/>');
        $this->adminmegj = array(
            'type' => 'textbox',
            'name' => "adminmegj",
			'class' => 'tinymce',
            'value' => $this->church->adminmegj,
            'labelback' => ' A templom szerkesztésével kacsolatosan.');

        $this->update = array(
            'type' => 'checkbox',
            'name' => "update",
            'value' => 'i',
            'checked' => true,
            'labelback' => 'Frissítsük a dátumot! (Utoljára frissítve: ' . date('Y.m.d.', strtotime($this->church->frissites)).')'
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
