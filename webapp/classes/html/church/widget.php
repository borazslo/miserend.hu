<?php

namespace Html\Church;

use Illuminate\Database\Capsule\Manager as DB;

class Widget extends \Html\Html {

    public function __construct($path) {
        parent::__construct();

        // A Path osztály argumentuma itt az URL többi része, tipikusan [0] az id
        $id = null;
        if (is_array($path) && isset($path[0]) && is_numeric($path[0])) {
            $id = (int)$path[0];
        }

        // fallback: kérés paraméterekből is megpróbáljuk
        if (!$id) {
            if (isset($this->input['id']) && is_numeric($this->input['id'])) {
                $id = (int)$this->input['id'];
            } elseif (isset($this->input['church_id']) && is_numeric($this->input['church_id'])) {
                $id = (int)$this->input['church_id'];
            }
        }

        if (!$id) {
            throw new \Exception('Nem található templom azonosító a widget megjelenítéséhez.');
        }

        // A twig sablonnak átadjuk a templom id-t, az Angular kliens ebből, vagy az útvonalból veszi majd
        $this->template = 'church/widget.twig';
        $this->churchId = $id;
    }

}
