<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Remark extends Html {

    public $template;

    public function __construct() {

        $action = \Request::SimpletextRequired('op');
        switch ($action) {
            case 'list':
                $this->tid = \Request::IntegerRequired('tid');
                $church = new \Church($this->tid);
                $this->listOfChurch($church);
                break;

            case 'addform':
                $this->tid = \Request::IntegerRequired('tid');
                $church = new \Church($this->tid);
                $this->newForm($church);
                break;

            case 'add':
                $this->tid = \Request::IntegerRequired('tid');
                $church = new \Church($this->tid);
                $this->add($church);
                break;

            case 'modify':
                $this->template = 'remark_list.twig';
                $rid = \Request::IntegerRequired('rid');
                $remark = new \Remark($rid);
                $state = \Request::Simpletext('state');
                $remark->changeState($state);

                $comment = \Request::Text('adminmegj');
                if ($comment != '') {
                    $remark->addComment($comment);
                }
                $this->tid = $remark->tid;

                $church = new \Church($this->tid);
                $this->listOfChurch($church);
                break;

            case 'add':
                break;
        }
    }

    function listOfChurch($church) {
        $this->setTitle('Észrevételek');
        $this->pageDescription = "Javítások/észrevételek kezelése";

        $church->getRemarks();
        $this->church = $church;
        $this->remarks = $church->remarks;

        $this->template = 'remark_list.twig';

        global $user;
        if (!$user->checkRole('miserend') and ! ($user->username == $templom->letrehozta ) and ! $user->checkRole('ehm:' . $templom['egyhazmegye'])) {
            addMessage("Hiányzó jogosultság. Elnézést.", "danger");
            return;
        }
    }

    function newForm($church) {
        $this->setTitle("Észrevétel beküldése");
        $this->church = $church;

        $this->pageDescription = "Javítások, változások bejelentése a templom adataival, miserenddel, kapcsolódó információkkal (szentségimádás, rózsafűzér, hittan, stb.) kapcsolatban.";
        $this->disclaimer = 'Figyelem! Nem állunk közvetlen kapcsolatban a plébániákkal ezért plébániai ügyekben (pl. keresztelési okiratok, stb.) sajnos nem tudunk segíteni.';

        $this->template = 'remark_form.twig';
    }

    function add($church) {
        $this->setTitle("Észrevétel beküldése");
        $this->church = $church;
        $remark = new \Remark();

        $remark->tid = $church->id;

        $remark->text = \Request::TextRequired('leiras');
        $remark->name = \Request::TextRequired('nev');
        $remark->email = \Request::TextRequired('email');

        if ($remark->email == '')
            unset($remark->email);
        if (!$remark->save())
            addMessage("Nem sikerült elmenteni az észrevételt. Sajánljuk.", "danger");
        if (!$remark->emails())
            addMessage("Nem sikerült elküldeni az értesítő emaileket.", "warning");
        $content = "<h2>Köszönjük!</h2><strong>A megjegyzést elmentettük és igyekszünk mihamarabb feldolgozni!</strong></br></br>" . $remark->PreparedText4Email . "<br/><input type='button' value='Ablak bezárása' onclick='self.close()'>";
        global $config;
        if ($config['debug'] < 1)
            $content .= "<script language=Javascript>setTimeout(function(){self.close();},3000);</script>";

        $this->content = $content;

        $this->pageDescription = "Javítások, változások bejelentése a templom adataival, miserenddel, kapcsolódó információkkal (szentségimádás, rózsafűzér, hittan, stb.) kapcsolatban.";

        $this->template = 'remark.twig';
    }

}
