<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Church;

use App\Html\Html;
use App\Model\Church;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Create extends Html
{
    public function create(Request $request): Response
    {
        if (!$this->getSecurity()->isGranted('miserend')) {
            throw new \Exception('Nincs jogosultságod a templomot létrehozni.');
        }

        $church = new Church();
        $church->nev = 'Új misézőhely';
        $church->ok = 'n';
        $church->megbizhato = 'i';
        $church->frissites = date('Y-m-d');
        $church->moddatum = date('Y-m-d');
        $church->egyhazmegye = 1;
        $church->megkozelites = '';
        $church->plebania = '';
        $church->leiras = '';
        $church->megjegyzes = '';
        $church->misemegj = '';
        $church->bucsu = '';
        $church->adminmegj = '';
        $church->log = '';
        $church->save();

        $church->nev = 'Új misézőhely - '.$church->id;
        $church->save();

        $this->redirect('/templom/'.$church->id.'/edit');
    }
}
