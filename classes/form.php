<?php

class Form {

    public function selectReligiousAdministration($selected = false) {
        $return = $this->religiousAdministrationSelection($selected);
        $this->form['dioceses'] = $return['dioceses'];
        $this->form['deaneries'] = $return['deaneries'];
    }

    static function religiousAdministrationSelection($selected = false) {
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
        $selectDiocese = array(
            'type' => 'select',
            'name' => 'church[egyhazmegye]',
            'id' => 'selectEgyhazmegye',
            'options' => $options,
            'selected' => $selected['diocese']
        );

        foreach ($dioceses as $selectibleDiocese) {
            $options = [0 => 'Válassz/Nem tudom'];
            $deaneries = \Illuminate\Database\Capsule\Manager::table('espereskerulet')
                            ->select('id', 'nev')
                            ->where('ehm', $selectibleDiocese->id)
                            ->orderBy('nev')->get();
            foreach ($deaneries as $selectibleDeanery) {
                $options[$selectibleDeanery->id] = $selectibleDeanery->nev . " espereskerület";
            }
            $selectDeanery[$selectibleDiocese->id] = array(
                'type' => 'select',
                'name' => 'church[espereskerulet]',
                'id' => 'selectEspereskeruletDiocese' . $selectibleDiocese->id,
                'class' => 'selectEspereskeruletDiocese',
                'options' => $options,
                'selected' => $selected['deanery']
            );
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
