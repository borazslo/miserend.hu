<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Services\ExternalApi;

// https://operations.osmfoundation.org/policies/nominatim/

class NominatimApi extends ExternalApi
{
    public $name = 'nominatim';
    public $apiUrl = 'https://nominatim.openstreetmap.org/';

    public function OSM2GeoJson($osmtype, $osmid)
    {
        $this->cache = '2 weeks';  // Nem számolunnk azzal, hogy a boundary-k sűrűn változnának.
        $this->query = 'details.php?addressdetails=1&hierarchy=0&group_hierarchy=1';
        $this->query .= '&osmtype='.$osmtype.'&osmid='.$osmid;
        $this->query .= '&polygon_geojson=1&format=json';

        if ($this->runQuery()) {
            return $this->jsonData->geometry;
        } else {
            return false;
        }
    }

    public function buildQuery(): void
    {
        $this->rawQuery = $this->query;
    }
}
