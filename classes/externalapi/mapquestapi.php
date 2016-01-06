<?php

namespace ExternalApi;

# https://developer.mapquest.com/

class MapquestApi extends \ExternalApi\ExternalApi {

    public $name = 'mapquest';
    public $apiUrl = "http://open.mapquestapi.com/directions/v2/";

    function distance($pointFrom, $pointTo) {

        global $config;

        if (!$config['mapquest']['appkey'] or $config['mapquest']['appkey'] == '***') {
            return false;
        }

        $this->query = "route?from=" . implode(',', $pointFrom) . "&to=" . implode(',', $pointTo) . "";
        $this->query .= "&outFormat=json&unit=k&routeType=shortest&narrativeType=none";
        $this->query .= "&doReverseGeocode=false";
        $this->runQuery();

        $mapquest = $this->jsonData;
        if (isset($mapquest->route->routeError->errorCode)) {
            if ($mapquest->info->statuscode == 602)
                return -1;
            elseif ($mapquest->route->routeError->errorCode > 0)
                return -2;
        }
        $d = $mapquest->route->distance * 1000;
        return $d;
    }

    function buildQuery() {
        global $config;
        $this->rawQuery = $this->query;
        $this->rawQuery .= "&key=" . $config['mapquest']['appkey'];
    }

}
