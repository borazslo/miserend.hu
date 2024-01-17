<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

use App\ExternalApi\NominatimApi;

class BoundaryGeoJson extends Ajax
{
    public function __construct()
    {
        if (!isset($_REQUEST['osm'])) {
            return;
        }

        header('Content-Type: application/json');
        echo '[';
        $osmData = explode(';', $_REQUEST['osm']);
        foreach ($osmData as $key => $osmDataItem) {
            preg_match('/(node|way|relation|N|W|R):([0-9]{1,8})$/i', $osmDataItem, $osm);
            if ('relation' == $osm[1]) {
                $osm[1] = 'R';
            } elseif ('way' == $osm[1]) {
                $osm[1] = 'W';
            } elseif ('node' == $osm[1]) {
                $osm[1] = 'N';
            }

            $nominatim = new NominatimApi();
            $geoJson = $nominatim->OSM2GeoJson($osm[1], $osm[2]);

            print_r(json_encode($geoJson));

            if ($key + 1 < \count($osmData)) {
                echo ',';
            }
        }
        echo ']';
    }
}
