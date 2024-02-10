<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html;

use App\Legacy\Model\Church;
use App\Legacy\Model\OSM;
use App\Legacy\Services\ExternalApi\NominatimApi;
use App\Legacy\Services\ExternalApi\OverpassApi;
use App\Legacy\Templating\TemplateContextTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Map extends Html
{
    use TemplateContextTrait;

    public function __construct(
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
            OverpassApi::class => OverpassApi::class,
            NominatimApi::class => NominatimApi::class,
        ];
    }

    public function leaflet(Request $request): Response
    {
        $data = $this->getGeoJsonDioceses();

        $variables = [
            'dioceseslayer' => [
                'geoJson' => json_encode($data),
            ],
        ] + $this->createTitleVariables('OSM Térkép');

        if (isset($_REQUEST['tid'])) {
            $churchId = $request->query->getInt('tid');
            $church = Church::on($this->getDatabaseManager()->getConnections())->find($churchId);

            $variables['location'] = $church->location;
            $variables['church_id'] = $churchId;
        }

        if ($request->query->has('boundary')) {
            $variables['boundary'] = $request->query->has('boundary');
        }

        if ($request->query->has('map')) {
            $parts = explode('/', $request->query->get('map'));

            if (\count($parts) == 3) {
                $variables['center'] = [
                    'zoom' => (int) $parts[0],
                    'lat' => (float) $parts[1],
                    'lon' => (float) $parts[2],
                ];
            }

            if (\count($parts) == 2) {
                $variables['center'] = [
                    'lat' => (float) $parts[0],
                    'lon' => (float) $parts[1],
                ];
            }
        }

        return $this->render('map.twig', $variables);
    }

    public function getGeoJsonDioceses()
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
    }

    public function geoJsonDiocesesFromCache()
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
}
