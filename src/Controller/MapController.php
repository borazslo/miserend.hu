<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Church;
use App\Request\QueryParameterEntityResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class MapController extends AbstractController
{
    #[Route(path: '/terkep', name: 'map_main', methods: 'GET')]
    public function main(
        Request $request,
        #[MapQueryParameter(name: 'tid', resolver: QueryParameterEntityResolver::class)]
        ?Church $church = null,
    ) {
        $center = null;
        // TODO MapQueryParameter/+resolver terkep coord hoz legacy sorrend tamogatasa
        if ($request->query->has('map')) {
            $parts = explode('/', $request->query->get('map'));

            if (\count($parts) == 3) {
                $center = [
                    'center' => [
                        'zoom' => (int) $parts[0],
                        'lat' => (float) $parts[1],
                        'lon' => (float) $parts[2],
                    ],
                ];
            }

            if (\count($parts) == 2) {
                $center = [
                    'center' => [
                        'lat' => (float) $parts[0],
                        'lon' => (float) $parts[1],
                    ],
                ];
            }
        }

        // boundary
        return $this->render('map/leaflet.html.twig', [
            'church' => $church,
        ] + ($center ?? []));
        // api $data = $this->getGeoJsonDioceses();

        /*if ($request->query->has('boundary')) {
            $variables['boundary'] = $request->query->has('boundary');
        }*/
    }

    /* @todo http request legyen + client */
    /*public function getGeoJsonDioceses()
    {
        if (!$jsonData = $this->geoJsonDiocesesFromCache()) {
            $cacheTime = '1 week';

            $results = $this->getDatabaseManager()->table('egyhazmegye')
                ->whereNotNull('osm_relation')
                ->select('osm_relation')
                ->pluck('osm_relation');

            $osms = OSM::where('osmtype', 'relation')->whereIn('osmid', $results->toArray())->where('updated_at', '<', date('Y-m-d H:i:s', strtotime('-'.$cacheTime)));
            $osmids = $osms->pluck('osmid')->toArray();
            // Ha még nem tároljuk az osm adatait VAGY már régen akkor itt az ideje
            $diff = array_diff($results->toArray(), []); // $osmids);
            if (\count($diff) > 0) {
                foreach ($diff as $d) {
                    $overpass = $this->container->get(OverpassApi::class);
                    $overpass->setQuery('relation(id:'.$d.');out tags qt center;');
                    $overpass->buildQuery();
                    $overpass->run();
                    $overpass->saveElement();

                    $element = $overpass->getJsonData()->elements[0];

                    $osm = OSM::updateOrCreate([
                        'osmid' => $element->id,
                        'osmtype' => $element->type],
                        ['lat' => $element->lat,
                            'lon' => 'ss'.$element->lon,
                        ])->touch();

                    $osmids[] = $d;
                }
            }
            $geoJsons = [];
            foreach ($osmids as $osmid) {
                $nominatim = $this->container->get(NominatimApi::class);
                $geoJsons[] = json_encode($nominatim->OSM2GeoJson('R', $osmid));
            }

            if (\count($geoJsons) < 1) {
                $json = '{}';
            } else {
                $json = '['.implode(',', $geoJsons).']';
            }

            $cacheDir = $this->projectDir.'/var/tmp/'; // Vigyázz! Egyezzen: geoJsonDiocesesFromCache();
            $cacheFilePath = $cacheDir.'GeojsonDioceses';  // Vigyázz! Egyezzen: geoJsonDiocesesFromCache();
            if (!file_put_contents($cacheFilePath, $json)) {
                throw new \Exception('We could not save the cacheFile to '.$cacheFilePath);
            }

            return json_decode($json);
        } else {
            return $jsonData;
        }
    }*/

    /*
     * public function geoJsonDiocesesFromCache()
    {
        $cacheDir = $this->projectDir.'/var/tmp/';
        $cacheFilePath = $cacheDir.'GeojsonDioceses';
        $cacheTime = '1 sec'; // Ez hiába rövid, ha az externalApi cache-e hosszú
        if (file_exists($cacheFilePath)) {
            if (filemtime($cacheFilePath) > strtotime('-'.$cacheTime)) {
                $rawData = file_get_contents($cacheFilePath);
                if (!$jsonData = json_decode($rawData)) {
                    throw new \Exception('Saved Geojsondioceses is not a valid JSON!');
                } else {
                    return $jsonData;
                }
            } else {
                unlink($cacheFilePath);

                return false;
            }
        } else {
            return false;
        }
    }
     */
}
