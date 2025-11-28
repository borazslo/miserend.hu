<?php

namespace Html\Church;

use Illuminate\Database\Capsule\Manager as DB;

class EditSchedule extends \Html\Html {

    public function __construct($path) {
        $this->tid = $path[0];

        $this->church = \Eloquent\Church::find($this->tid)->append(['writeAccess']);;
        if (!$this->church) {
            throw new \Exception('Nincs ilyen templom.');
        }
        
        if (!$this->church->writeAccess) {
            throw new \Exception('Hiányzó jogosultság!');
            return;
        }

        $isForm = \Request::Text('submit');
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

       
        //Észrevétel        
        $this->jelzes = $this->church->remarksStatus;
		
		 		

        //miseaktív
        if ($this->church->miseaktiv == 1)
            $this->active['yes'] = ' checked ';
        else
            $this->active['no'] = ' checked ';



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

    }

    

}
