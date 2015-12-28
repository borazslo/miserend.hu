<?php

namespace Html;

class Cron extends Html {
    
    public $template = 'layout_empty.twig';

    public function __construct($path) {
        set_time_limit('300');
        ini_set('memory_limit', '512M');

        if ($path[0]) {
            switch ($path[0]) {

                case 'hourly':
                    clearoutTokens();
                    clearoutMessages();
                    updateOverpass(50);
                    updateDistances();
                    break;

                case 'daily':
                    #clearoutVolunteers();
                    deleteOverpass();

                    $overpass = new \OverpassApi();
                    $overpass->updateUrlMiserend();

                    for ($v = 1; $v < 5; $v++) {
                        $file = 'fajlok/sqlite/miserend_v' . $v . '.sqlite3';
                        generateSqlite($v, $file);
                        //upload2ftp('*url*','*user*','*password*','web/'.$file,$file);
                    }
                    break;

                case 'weekly':
                    #assignUpdates();
                    updateImageSizes();
                    generateMassTmp();

                    updateCleanMassLanguages();
                    updateGorogkatolizalas();
                    updateDeleteZeroMass();
                    updateComments2Attributes();
                    //not so fast!
                    updateAttributesOptimalization();

                    break;
            }
        }
    }

}
