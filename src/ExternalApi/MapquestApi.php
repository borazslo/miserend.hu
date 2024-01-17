<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\ExternalApi;

// https://developer.mapquest.com/

class MapquestApi extends ExternalApi
{
    public $name = 'mapquest';
    public $apiUrl = 'http://open.mapquestapi.com/directions/v2/';

    public function distance($pointFrom, $pointTo)
    {
        global $config;

        if (!$config['mapquest']['appkey'] || '***' == $config['mapquest']['appkey']) {
            throw new \Exception('Missing mapquest appkey.');
        }

        $this->query = 'route?from='.implode(',', $pointFrom).'&to='.implode(',', $pointTo).'';
        $this->query .= '&outFormat=json&unit=k&routeType=shortest&narrativeType=none';
        $this->query .= '&doReverseGeocode=false';

        try {
            $this->runQuery();
        } catch (\Exception $e) {
            // Általában akkor kerül elő ez, ha a mapquestApin elfogyott a havi lekérdezés adagunk
            // echo $this->responseCode // 403 = forbidden
            // throw new \Exception($this->rawData);
            return -2; // ??
        }

        $mapquest = $this->jsonData;
        if (isset($mapquest->route->routeError->errorCode)) {
            if (602 == $mapquest->info->statuscode) {
                return -1;
            } elseif ($mapquest->route->routeError->errorCode > 0) {
                return -2;
            }
        }
        $d = $mapquest->route->distance * 1000;

        return $d;
    }

    public function buildQuery(): void
    {
        global $config;
        $this->rawQuery = $this->query;
        $this->rawQuery .= '&key='.$config['mapquest']['appkey'];
    }
}
