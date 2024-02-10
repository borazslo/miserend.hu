<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

use Illuminate\Database\Capsule\Manager as DB;

class KeywordShortcut
{
    public function updateAll()
    {
        return true;
        // TODO:  Maximum execution time of 300 seconds exceeded in [...]/classes/keywordshortcut.php on line 34
        $this->updateNameShortcuts();
        $this->updateAdministrativeShortcuts();
        $this->updateReligiousAdministrationShortcuts();

        $this->updateOldStyeleShortcuts();
    }

    public function updateReligiousAdministrationShortcuts()
    {
        $osms = Model\OSM::whereHasTag('boundary', 'religious_administration')->get();
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

    public function updateAdministrativeShortcuts()
    {
        $osms = Model\OSM::whereHasTag('boundary', 'administrative')->get();
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

    public function updateNameShortcuts()
    {
        $tags = Model\OSMTag::whereRaw(" `name` REGEXP '^(alt_|old_){0,1}name(:|$)' ")->get();
        foreach ($tags as $tag) {
            $churches = $tag->osm->churches()->get();
            foreach ($churches as $church) {
                $this->addKeywordShorcut($tag, $church, 'name');
            }
        }
    }

    public function updateOldStyeleShortcuts()
    {
        $names = DB::table('templomok')->select('id', 'nev', 'ismertnev')->get();
        foreach ($names as $name) {
            foreach (['nev', 'ismertnev'] as $k => $v) {
                if ($name->$v != '') {
                    $church = Model\Church::find($name->id);
                    $tag = new \stdClass();
                    $tag->id = (int) ('-'.$church->id.$k);
                    $tag->value = $name->$v;
                    $this->addKeywordShorcut($tag, $church, 'name');
                }
            }
        }
    }

    public function addKeywordShorcut($tag, $church, $type)
    {
        $keywordShortcut = Model\KeywordShortcut::firstOrNew(['osmtag_id' => $tag->id]);
        $keywordShortcut->church_id = $church->id;
        $keywordShortcut->type = $type;
        $keywordShortcut->value = $tag->value;
        $keywordShortcut->save();
    }
}
