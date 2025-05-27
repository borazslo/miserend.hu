<?php

namespace Html\Church;

class Create extends \Html\Html {

    public function __construct($path) {
        global $user;
        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod a templomot létrehozni.');
        }

        $this->title = 'Új misézőhely létrehozása';
        $this->template = 'layout.twig';

        $church = new \Eloquent\Church;
        $church->nev = 'Új misézőhely';
        $church->ok = 'n';
        $church->megbizhato = 'i';
        $church->frissites = date('Y-m-d');
        $church->moddatum = date('Y-m-d');
        $church->egyhazmegye = 1;
        // General error: 1364 Field '...' doesn't have a default value
        $church->megkozelites = '';
        $church->plebania = '';
        $church->leiras = '';
        $church->megjegyzes = '';
        $church->misemegj = '';
        $church->adminmegj = '';
        $church->log = '';


        $church->save();
        $church->nev = "Új misézőhely - ".$church->id;
        $church->save();

        $this->content = "Létrehozás sikeres: <br/><a href='/templom/".$church->id."/edit'>".$church->nev."</a>";

        $this->redirect("/templom/".$church->id."/edit");


    }

}
