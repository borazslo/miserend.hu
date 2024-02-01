<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

class Form
{
    public function selectReligiousAdministration($selected = false)
    {
        $return = $this->religiousAdministrationSelection($selected);
        $this->form['dioceses'] = $return['dioceses'];
        $this->form['deaneries'] = $return['deaneries'];
    }

    public static function religiousAdministrationSelection($selected = false)
    {
        if (!$selected) {
            $selected = ['diocese' => false, 'deanery' => false];
        }

        $options = [0 => 'Válassz/Nem tudom'];
        $dioceses = \Illuminate\Database\Capsule\Manager::table('egyhazmegye')
            ->select('id', 'nev')
            ->orderBy('sorrend')->get();
        foreach ($dioceses as $selectibleDiocese) {
            $options[$selectibleDiocese->id] = $selectibleDiocese->nev;
        }
        $selectDiocese = [
            'type' => 'select',
            'name' => 'church[egyhazmegye]',
            'id' => 'selectEgyhazmegye',
            'options' => $options,
            'selected' => $selected['diocese'],
        ];

        foreach ($dioceses as $selectibleDiocese) {
            $options = [0 => 'Válassz/Nem tudom'];
            $deaneries = \Illuminate\Database\Capsule\Manager::table('espereskerulet')
                ->select('id', 'nev')
                ->where('ehm', $selectibleDiocese->id)
                ->orderBy('nev')->get();
            foreach ($deaneries as $selectibleDeanery) {
                $options[$selectibleDeanery->id] = $selectibleDeanery->nev.' espereskerület';
            }
            $selectDeanery[$selectibleDiocese->id] = [
                'type' => 'select',
                'name' => 'church[espereskerulet]',
                'id' => 'selectEspereskeruletDiocese'.$selectibleDiocese->id,
                'class' => 'selectEspereskeruletDiocese',
                'options' => $options,
                'selected' => $selected['deanery'],
            ];
            if ($selectibleDiocese->id == $selected['diocese']) {
                $selectDeanery[$selectibleDiocese->id]['style'] = 'display: inline';
            } else {
                $selectDeanery[$selectibleDiocese->id]['style'] = 'display: none';
                $selectDeanery[$selectibleDiocese->id]['disabled'] = 'disabled';
            }
        }

        return ['dioceses' => $selectDiocese, 'deaneries' => $selectDeanery];
    }
}
