<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Ajax;

use App\Html\Ajax\Ajax;
use App\Legacy\Services\ExternalApi\NominatimApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BoundaryGeoJson extends Ajax
{
    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
            NominatimApi::class => NominatimApi::class,
        ];
    }

    public function main(Request $request): Response
    {
        if (!$request->request->has('osm')) {
            return new Response('');
        }

        $nominatim = $this->container->get(NominatimApi::class);

        $osm = $request->request->get('osm');

        $buffer = '[';
        $osmData = explode(';', $osm);
        foreach ($osmData as $key => $osmDataItem) {
            preg_match('/(node|way|relation|N|W|R):([0-9]{1,8})$/i', $osmDataItem, $osm);
            if ('relation' == $osm[1]) {
                $osm[1] = 'R';
            } elseif ('way' == $osm[1]) {
                $osm[1] = 'W';
            } elseif ('node' == $osm[1]) {
                $osm[1] = 'N';
            }

            $geoJson = $nominatim->OSM2GeoJson($osm[1], $osm[2]);

            $buffer .= json_encode($geoJson);

            if ($key + 1 < \count($osmData)) {
                $buffer .= ',';
            }
        }
        $buffer .= ']';

        return JsonResponse::fromJsonString($buffer);
    }
}
