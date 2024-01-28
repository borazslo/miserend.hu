<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Church;

use App\Html\Html;
use App\Html\Map;
use App\Legacy\Application;
use App\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Church extends Html
{
    public function view(Request $request): Response
    {
        $user = $this->getUser();

        $tid = $request->attributes->getInt('church_id');

        if (0 === $tid) {
            throw new \Exception('Church does not exist.');
        }



        $church = Model\Church::find($tid);
        if (!$church && $user->checkRole('miserend')) {
            $church = Model\Church::withTrashed()->find($tid);
            if ($church) {
                addMessage('Ez a templom törölve van. Nem létezik. Elhunyt. Vége.', 'danger');
            }
        }

        if (!$church) {
            throw new \Exception("Church with tid = '$tid' does not exist.");
        }
        $church = $church->append(['readAccess', 'writeAccess', 'liturgiatv']);

        if (!$church->readAccess) {
            throw new \Exception("Read access denied to church tid = '$tid'");
        }

        if ('n' == $church->ok) {
            addMessage('Ez a templom le van tiltva! Csak adminisztrátorok számára látható ez az oldal.', 'warning');
        } elseif ('f' == $church->ok) {
            addMessage('Ez a templom áttekintésre vár. Csak adminisztrátorok számára látható ez az oldal.', 'warning');
        }

        $church->photos = $church->photos()->get();

        if ($church->osm) {
            $church->accessibility = $church->osm->tagList;
        }

        $_honapok = Application::getMonths();

        if ('' != $church->lat && !isset($church->location->city)) {
            $church->MdownloadOSMBoundaries();
        }

        $church->MgetReligious_administration();

        if (isset($this->location->city)) {
            $title = $this->nev.' ('.$this->location->city['name'].')';
        } else {
            $title = $church->nev;
        }

        $service_times = null;
        // Convert to OSM ServiceTimes
        if (1 == $user->getIsadmin()) {
            $serviceTimes = new \App\ServiceTimes();
            $serviceTimes->loadMasses($tid);
            if (!isset($serviceTimes->error)) {
                $service_times = print_r(preg_replace('/;/', ";\n", $serviceTimes->string), 1)."\n".$serviceTimes->linkForDetails;
            } else {
                $service_times = $serviceTimes->error;
            }
        }

        $favorite = $user->checkFavorite($tid);

        return $this->render('church/church.twig', [
            'title' => $title,
            'pageTitle' => $title.' | Miserend',
            'og_image' => isset($church->photos[0]) ? '/kepek/templomok/'.$tid.'/'.$church->photos[0]->fajlnev : null,
            'alert' => LiturgicalDayAlert('html'),
            'isChurchHolder' => $user->getHoldingData($church->id),
            'church' => $church,
            'remarksicon' => $church->remarksicon,
            'id' => $church->id,
            'updated' => str_replace('-', '.', $church->frissites).'.',
            'favorite' => $favorite,
            'dioceseslayer' => [
                'geoJson' => json_encode(Map::getGeoJsonDioceses()),
            ],
            'miserend' => getMasses($tid),
            'service_times' => $service_times,
        ]);
    }

    public function inBbox(Request $request): Response
    {
        if (!$request->query->has('bbox')) {
            throw new \RuntimeException('missing query parameter bbox');
        }

        $bbox = explode(';', $request->query->get('bbox'));
        if (4 != \count($bbox)) {
            throw new \RuntimeException('invalid bbox format');
        }

        $bbox = array_map(function ($value) {
            return (float) $value;
        }, $bbox);

        $churchesInBBox = Model\Church::on($this->getDatabaseManager()->getConnections())->inBBox([
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

        return new JsonResponse($return);
    }
}
