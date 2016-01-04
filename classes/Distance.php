<?php

use Illuminate\Database\Capsule\Manager as DB;

class Distance {

    public function __construct() {
        
    }

    public function updateSome() {
        $this->update(false, 50);
    }

    function update($church_id = false, $limit = false) {
        $counter = 0;
        if (!is_numeric($limit) or $limit == false) {
            $limit = 120;
        }

        $query = \Eloquent\Church::has('osms')->take($limit)->orderBy('moddatum', 'desc');
        if ($church_id) {
            if (is_array($church_id)) {
                $query = $query->whereIn('id', $church_id);
            } else {
                $query = $query->where('id', $church_id);
            }
        }

        $churches = $query->get();
        if (!$churches) {
            throw new \Exception('There are no churches to measure the distance from/to');
        }

        foreach ($churches as $churchFrom) {
            set_time_limit('600');

            $point = ['lon' => $churchFrom->osm->lon, 'lat' => $churchFrom->osm->lat];
            $maxDistance = 10000; //meter
            $bbox = $this->getBBox($point, $maxDistance);

            $query = \Eloquent\Church::inBBox($bbox)->where('id', '!=', $churchFrom->id);
            $churchesInBBox = $query->get();

            foreach ($churchesInBBox as $churchTo) {
                $processingDistance = \Eloquent\Distance::findOrNew(['church_from' => $churchFrom->id, 'church_to' => $churchTo->id])->first();
                if ($churchFrom->update_at > $processingDistance->update_at
                        OR $churchTo->update_at > $processingDistance->update_at) {

                    $pointFrom = ['lat' => $churchFrom->osm->lat, 'lon' => $churchFrom->osm->lon];
                    $pointTo = ['lat' => $churchTo->osm->lat, 'lon' => $churchTo->osm->lon];
                    $rawDistance = $this->getRawDistance($pointFrom, $pointTo);

                    if ($rawDistance < $maxDistance AND $rawDistance > 0) {
                        $mapquest = new \ExternalApi\MapquestApi();
                        $mapquestDistance = $mapquest->distance($pointFrom, $pointTo);

                        if ($mapquestDistance == -2) {
                            return;
                        } elseif ($mapquestDistance > 0) {
                            $processingDistance->distance = $mapquestDistance;
                        }
                    } else {
                        $processingDistance->distance = $rawDistance;
                    }
                    $processingDistance->save();
                    $counter++;
                    if ($counter >= $limit) {
                        return true;
                    }
                }
            }
        }
    }

    function getRawDistance($pointFrom, $pointTo) {
        $this->validatePoint($pointFrom);
        $this->validatePoint($pointTo);

        $lat1 = $pointFrom['lat'] * M_PI / 180;
        $lat2 = $pointTo['lat'] * M_PI / 180;
        $long1 = $pointFrom['lon'] * M_PI / 180;
        $long2 = $pointTo['lon'] * M_PI / 180;

        if ($lat1 == $lat2 AND $long1 == $long2)
            return 0;

        $R = 6371; // km
        $d = $R * acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($long2 - $long1)) * 1000;
        return $d;
    }

    function getBBox($point, $distanceInM) {
        $this->validatePoint($point);

        $distanceInKm = $distanceInM / 1000;
        // earth's radius in km = ~6371
        $radius = 6371;

        // latitude boundaries
        $bbox['latMax'] = $point['lat'] + rad2deg($distanceInKm / $radius);
        $bbox['latMin'] = $point['lat'] - rad2deg($distanceInKm / $radius);

        // longitude boundaries (longitude gets smaller when latitude increases)
        $bbox['lonMax'] = $point['lon'] + rad2deg($distanceInKm / $radius / cos(deg2rad($point['lat'])));
        $bbox['lonMin'] = $point['lon'] - rad2deg($distanceInKm / $radius / cos(deg2rad($point['lat'])));

        return $bbox;
    }

    function validatePoint($point) {
        if (!$this->isPoint($point)) {
            throw new \Exception('$point has wrong format: ' . print_r($point, 1));
        } else {
            return true;
        }
    }

    function isPoint($point) {
        if (!isset($point['lat']) or ! isset($point['lon']))
            return false;
        if ($point['lat'] == '' or $point['lon'] == '')
            return false;
        if (!is_numeric($point['lat']) or ! is_numeric($point['lon']))
            return false;
        if ($point['lat'] < -90 or $point['lat'] > 90)
            return false;
        if ($point['lon'] < -180 or $point['lon'] > 180)
            return false;

        return true;
    }

}
