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
        
        global $_tidsToWorkWith;
        $this->tids = $_tidsToWorkWith;
    }

}
