<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

use App\Legacy\Response\HttpResponseInterface;
use App\Legacy\Response\HttpResponseTrait;
use App\Model\Church;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChurchesInBBox extends Ajax implements HttpResponseInterface
{
    use HttpResponseTrait;

    public function __construct()
    {
        $bbox = explode(';', $_REQUEST['bbox']);
        if (4 != \count($bbox)) {
            return;
        }
        foreach ($bbox as $int) {
            if (!is_numeric($int)) {
                return;
            }
        }

        $churchesInBBox = Church::inBBox([
            'latMin' => $bbox[0],
            'lonMin' => $bbox[1],
            'latMax' => $bbox[2],
            'lonMax' => $bbox[3],
        ])->get();

        $return = [];
        foreach ($churchesInBBox as $church) {
            if (isset($church->photos[0])) {
                $thumbnail = $church->photos[0]->smallUrl;
            } else {
                $thumbnail = false;
            }

            $return[] = [
                'id' => $church->id,
                'nev' => $church->nev,
                'thumbnail' => $thumbnail,
                'denomination' => $church->denomination,
                'active' => $church->miseaktiv,
                'lat' => $church->location->lat,
                'lon' => $church->location->lon,
            ];
        }

        $this->response = new JsonResponse($return);
    }
}
