<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

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
                    $this->updateKeywordShortcuts();

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

    function updateKeywordShortcuts() {
        $this->updateNameShortcuts();
        $this->updateAdministrativeShortcuts();
        $this->updateReligiousAdministrationShortcuts();

        $this->updateOldStyeleShortcuts();
    }

    function updateReligiousAdministrationShortcuts() {
        $osms = \Eloquent\OSM::whereHasTag('boundary', 'religious_administration')->get();
        foreach ($osms as $osm) {
            $churches = $osm->enclosed()->get();
            foreach ($churches as $church) {
                foreach ($osm->tags as $tag) {
                    if (preg_match('/^(alt_|old_){0,1}name(:|$)/', $tag->name)) {
                        $this->addKeywordShorcut($tag, $church, 'religious_administration');
                    }
                }
            }
        }
    }

    function updateAdministrativeShortcuts() {
        $osms = \Eloquent\OSM::whereHasTag('boundary', 'administrative')->get();
        foreach ($osms as $osm) {
            $churches = $osm->enclosed()->get();
            foreach ($churches as $church) {
                foreach ($osm->tags as $tag) {
                    if (preg_match('/^(alt_|old_){0,1}name(:|$)/', $tag->name)) {
                        $this->addKeywordShorcut($tag, $church, 'administrative');
                    }
                }
            }
        }
    }

    function updateNameShortcuts() {
        $tags = \Eloquent\OSMTag::whereRaw(" `name` REGEXP '^(alt_|old_){0,1}name(:|$)' ")->get();
        foreach ($tags as $tag) {
            $churches = $tag->osm->churches()->get();
            foreach ($churches as $church) {
                $this->addKeywordShorcut($tag, $church, 'name');
            }
        }
    }

    function updateOldStyeleShortcuts() {
        $names = DB::table('templomok')->select('id', 'nev', 'ismertnev')->get();
        foreach ($names as $name) {
            foreach (['nev', 'ismertnev'] as $k => $v) {
                if ($name->$v != '') {
                    $church = \Eloquent\Church::find($name->id);
                    $tag = new \stdClass();
                    $tag->id = (int) ('-' . $church->id . $k);
                    $tag->value = $name->$v;
                    $this->addKeywordShorcut($tag, $church, 'name');
                }
            }
        }
    }

    function addKeywordShorcut($tag, $church, $type) {
        $keywordShortcut = \Eloquent\KeywordShortcut::firstOrNew(['osmtag_id' => $tag->id]);
        $keywordShortcut->church_id = $church->id;
        $keywordShortcut->type = $type;
        $keywordShortcut->value = $tag->value;
        $keywordShortcut->save();
    }

}
