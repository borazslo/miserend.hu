<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

class Distance
{
    public function __construct()
    {
    }

    public function updateSome()
    {
        $this->update(false, 50);
    }

    public function update($church_id = false, $limit = false)
    {
        $counter = 0;
        if (!is_numeric($limit) || false == $limit) {
            $limit = 120;
        }

        $query = Model\Church::has('osms')->take($limit)->orderBy('updated_at', 'desc');
        if ($church_id) {
            if (\is_array($church_id)) {
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
            $this->MupdateChurch($churchFrom);
        }
    }

    public function MupdateChurch($churchFrom, $maxDistance = 5000)
    { // maxDistance in meter
        set_time_limit('600');
        $counter = 0;
        if ('' == $churchFrom->location->lat || '' == $churchFrom->location->lon) {
            return false;
        }

        $point = ['lon' => $churchFrom->location->lon, 'lat' => $churchFrom->location->lat];

        // TODO: Delete BBOX-on belüli távolságok. Vagy minden távolság?

        for ($i = 1; $i < 10; ++$i) {
            $bbox = $this->getBBox($point, $maxDistance);
            $churchesInBBox = Model\Church::inBBox($bbox)->where('id', '!=', $churchFrom->id)->get();
            if (\count($churchesInBBox) > 12) {
                break;
            }
            $maxDistance *= (120 / 100);
        }

        $highestDistance = 0;
        foreach ($churchesInBBox as $churchTo) {
            $processingDistance = Model\Distance::findOrNew(
                ['fromLat' => $churchFrom->lat, 'fromLon' => $churchFrom->lon, 'toLat' => $churchTo->lat, 'toLon' => $churchTo->lon]
            )->first();
            $processingDistance = Model\Distance::where('fromLat', $churchFrom->lat)
                ->where('fromLon', $churchFrom->lon)
                ->where('toLat', $churchTo->lat)
                ->where('toLon', $churchTo->lon)->first();
            if (!$processingDistance) {
                $processingDistance = new Model\Distance();
                $processingDistance->fromLat = $churchFrom->lat;
                $processingDistance->fromLon = $churchFrom->lon;
                $processingDistance->toLat = $churchTo->lat;
                $processingDistance->toLon = $churchTo->lon;
            }
            if ($churchFrom->updated_at > $processingDistance->updated_at
                || $churchTo->updated_at > $processingDistance->updated_at) {
                $pointFrom = ['lat' => $churchFrom->location->lat, 'lon' => $churchFrom->location->lon];
                $pointTo = ['lat' => $churchTo->location->lat, 'lon' => $churchTo->location->lon];
                $rawDistance = $this->getRawDistance($pointFrom, $pointTo);
                if ($rawDistance < $maxDistance && $rawDistance > 0) {
                    $mapquest = new ExternalApi\MapquestApi();
                    $mapquestDistance = $mapquest->distance($pointFrom, $pointTo);
                    if (-2 == $mapquestDistance) {
                        return;
                    } elseif ($mapquestDistance > 0) {
                        $processingDistance->distance = $mapquestDistance;
                        if ($mapquestDistance > $highestDistance) {
                            $highestDistance = $mapquestDistance;
                        }
                        $processingDistance->save();
                    }
                } else {
                    // Pontatlant inkább soha senem mentünk el.
                    // $processingDistance->distance = $rawDistance;
                }

                ++$counter;
            }
        }
        /*
         * Ha találtunk olyat, hogy útvonalon annyival hosszabb, akkor
         * lehetséges, hogy van annál közelebbi is, ezért ki kell tágítani
         * a kört.
         */
        if ($highestDistance > $maxDistance) {
            // echo "Van nagyobb kör is. Bocsesz.";

            // TODO: duplicated code
            $bbox = $this->getBBox($point, $highestDistance);
            $churchesInBBox = Model\Church::inBBox($bbox)->where('id', '!=', $churchFrom->id);

            foreach ($churchesInBBox as $churchTo) {
                $processingDistance = Model\Distance::findOrNew(
                    ['fromLat' => $churchFrom->lat, 'fromLon' => $churchFrom->lon, 'toLat' => $churchTo->lat, 'toLon' => $churchTo->lon]
                )->first();
                $processingDistance = Model\Distance::where('fromLat', $churchFrom->lat)
                    ->where('fromLon', $churchFrom->lon)
                    ->where('toLat', $churchTo->lat)
                    ->where('toLon', $churchTo->lon)->first();
                if (!$processingDistance) {
                    $processingDistance = new Model\Distance();
                    $processingDistance->fromLat = $churchFrom->lat;
                    $processingDistance->fromLon = $churchFrom->lon;
                    $processingDistance->toLat = $churchTo->lat;
                    $processingDistance->toLon = $churchTo->lon;
                }
                $highestDistance = 0;
                if ($churchFrom->updated_at > $processingDistance->updated_at
                    || $churchTo->updated_at > $processingDistance->updated_at || 4 == 4) {
                    $pointFrom = ['lat' => $churchFrom->location->lat, 'lon' => $churchFrom->location->lon];
                    $pointTo = ['lat' => $churchTo->location->lat, 'lon' => $churchTo->location->lon];
                    $rawDistance = $this->getRawDistance($pointFrom, $pointTo);

                    if ($rawDistance < $maxDistance && $rawDistance > 0) {
                        $mapquest = new ExternalApi\MapquestApi();
                        $mapquestDistance = $mapquest->distance($pointFrom, $pointTo);
                        if (-2 == $mapquestDistance) {
                            return;
                        } elseif ($mapquestDistance > 0) {
                            $processingDistance->distance = $mapquestDistance;
                            $processingDistance->save();
                        }
                    } else {
                        // Pontatlant inkább soha senem mentünk el.
                        // $processingDistance->distance = $rawDistance;
                    }

                    ++$counter;
                }
            }
        }

        return $counter;
    }

    public function getRawDistance($pointFrom, $pointTo)
    {
        $this->validatePoint($pointFrom);
        $this->validatePoint($pointTo);

        $lat1 = $pointFrom['lat'] * \M_PI / 180;
        $lat2 = $pointTo['lat'] * \M_PI / 180;
        $long1 = $pointFrom['lon'] * \M_PI / 180;
        $long2 = $pointTo['lon'] * \M_PI / 180;

        if ($lat1 == $lat2 && $long1 == $long2) {
            return 0;
        }

        $R = 6371; // km
        $d = $R * acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($long2 - $long1)) * 1000;

        return $d;
    }

    public function getBBox($point, $distanceInM)
    {
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

    public function validatePoint($point)
    {
        if (!$this->isPoint($point)) {
            throw new \Exception('$point has wrong format: '.print_r($point, 1));
        } else {
            return true;
        }
    }

    public function isPoint($point)
    {
        if (!isset($point['lat']) || !isset($point['lon'])) {
            return false;
        }
        if ('' == $point['lat'] || '' == $point['lon']) {
            return false;
        }
        if (!is_numeric($point['lat']) || !is_numeric($point['lon'])) {
            return false;
        }
        if ($point['lat'] < -90 || $point['lat'] > 90) {
            return false;
        }
        if ($point['lon'] < -180 || $point['lon'] > 180) {
            return false;
        }

        return true;
    }
}
