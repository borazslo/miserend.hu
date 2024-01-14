<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html;

use Illuminate\Database\Capsule\Manager as DB;

class DioceseCatalogue extends Html
{
    public function __construct($path)
    {
        global $user;

        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod megnézni a templomok listáját.');
        }

        $this->title = 'Templomok listája egyházmegyénként';
        $ehm = !empty($_REQUEST['ehm']) ? $_REQUEST['ehm'] : 'false';

        $ehmsDB = DB::table('egyhazmegye')->where('ok', 'i')->orderBy('sorrend')->get();
        $this->ehms = $options = [];
        foreach ($ehmsDB as $tmp) {
            $this->ehms[$tmp->id] = $tmp;
            $options[$tmp->id] = $tmp->nev;
        }

        $this->form['diocese'] = [
            'type' => 'select',
            'name' => 'ehm',
            'options' => $options,
        ];

        if (is_numeric($ehm) && $ehm > 0) {
            $this->ehms[$ehm]->selected = 'selected';
            $this->form['diocese']['selected'] = $ehm;

            $this->title = 'Templomok listája: '.$this->ehms[$ehm]->nev.' egyházmegye';

            $espkersDB = DB::table('espereskerulet')->where('ehm', $ehm)->orderBy('nev')->get();

            $this->espkers = [];
            foreach ($espkersDB as $espker) {
                $this->espkers[$espker->id] = $espker->nev;
            }

            $this->churchesGroupByEspker = \App\Model\Church::where('ok', 'i')
                    ->where('egyhazmegye', $ehm)
                    ->orderBy('varos')->orderBy('nev')
                    ->get()->groupBy('espereskerulet');
        }

        return;
    }
}
