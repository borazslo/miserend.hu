<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

use App\Exception;
use Illuminate\Database\Capsule\Manager as DB;

class OSM
{
    /*
    * Az OSM-el rendelkező templomoknál letöltjük, hogy milyen területekhez
    * tartozik.
    */

    public function checkBoundaries($limit = 50)
    {
        $churches = Model\Church::where('ok', 'i')->where('lat', '<>', '')
            ->doesntHave('boundaries')
            ->orderByRaw('RAND()')
            ->take($limit)
            ->get();

        if (\count($churches) < 1) {
            $results = DB::table('templomok')
                ->join('lookup_boundary_church', 'templomok.id', '=', 'lookup_boundary_church.church_id')
                ->select('lookup_boundary_church.*')
                ->orderBy('lookup_boundary_church.updated_at', 'ASC')
                ->groupBy('church_id')
                ->limit($limit)
                ->get();
            $churches = [];
            foreach ($results as $result) {
                $churches[] = Model\Church::find($result->church_id);
            }
        }

        foreach ($churches as $church) {
            $church->MdownloadOSMBoundaries();
            $church->MmigrateBoundaries();
        }
    }

    /*
     * Az OSM-ből az url:miserend -es cuccok lekérése és a templomok azok
     * alapján való mentése.
     */

    public function checkUrlMiserend()
    {
        $overpass = new Services\ExternalApi\OverpassApi();
        $overpass->downloadUrlMiserend();

        /*
         * Ezzel nem élünk, de az adatbázisban jól mutathat az adatbázisban
         * az osm és osmtags táblák, a lookup nélkül.
         */
        $overpass->saveElement();

        if (!$overpass->jsonData->elements) {
            throw new Exception('Missing Json Elements from OverpassApi Query');
        }
        $c = 0;
        foreach ($overpass->jsonData->elements as $element) {
            ++$c;
            if ($c > 10000) {
                exit;
            }
            preg_match('/miserend\.hu\/\?{0,1}templom(\/|=)([0-9]{1,5})/i', $element->tags->{'url:miserend'}, $match);
            if (!isset($match[2])) {
                /*
                 * TODO: Van url:miserend, de az értéke vacak.
                 */
                // printr($element);
            } else {
                $church = Model\Church::find($match[2]);
                if ($church) {
                    $this->saveOSM2Church($church, $element);
                }
            }
        }
    }

    public function saveOSM2Church($church, $element)
    {
        if (isset($element->center->lat)) {
            $element->lat = $element->center->lat;
        }
        if (isset($element->center->lon)) {
            $element->lon = $element->center->lon;
        }

        $changed = false;

        /* TODO: Megnézhetnénk, hogy lat, lon, name (name:hu) egyezik-e. *
        $keys = [['lon','lon'],['lat','lat'],['type','osmtype'],['id','osmid']];
        foreach ($keys as $key) {
            if( $element->{$key[0]} == $church->{$key[1]} ) echo "ok ";
            echo $element->{$key[0]}." vs ".$church->{$key[1]}."<br/>";
        }
        */

        if ($element->id != $church->osmid || $element->type != $church->osmtype) {
            $church->osmtype = $element->type;
            $church->osmid = $element->id;
            $changed = true;
        }

        /* TODO: biztosan fejetlenül átmentjük a koordinátákat? */
        if ($element->lat != $church->lat || $element->lon != $church->lon) {
            $church->lon = $element->lon;
            $church->lat = $element->lat;
            $changed = true;
        }
        $changed ? $church->save() : false;
    }
}
