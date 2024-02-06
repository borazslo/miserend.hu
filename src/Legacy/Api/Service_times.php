<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Api;

class Service_times extends Api
{
    public function run()
    {
        parent::run();

        $this->return = [];

        $churches = \App\Legacy\Model\Church::limit(10000)->get();
        set_time_limit('600');
        foreach ($churches as $church) {
            $serviceTimes = new \App\Legacy\ServiceTimes();
            $serviceTimes->loadMasses($church->id, ['skipvalidation']);

            $syntax = 'horariosdemisa';

            if ('horariosdemisa' == $syntax) {
                $return = [
                    'church_id' => $church->id,
                    'name' => $church->nev,
                    'address' => $church->location->address,
                    'city, state' => $church->location->city['name'],
                    'country' => $church->location->country['name'],
                    'phone' => false,
                    'email' => $church->pleb_eml,
                    'url' => false,
                    'location' => [$church->osm->lat, $church->osm->lon],
                    'service_times' => $serviceTimes->string,
                    'confessions' => false,
                    'adoration' => false,
                    'additional_information' => $church->misemegj,
                    'last_confirmation' => $church->frissites,
                ];

                if (\count($church->links) > 1) {
                    foreach ($church->links as $link) {
                        $return['url'][] = $link->href;
                    }
                } else {
                    $return['url'] = $church->links[0]->href;
                }

                if (isset($church->osm->taglist)) {
                    if (\array_key_exists('name:en', $church->osm->taglist)) {
                        $return['name'] .= ' / '.$church->osm->taglist['name:en'];
                    }

                    if (\array_key_exists('contact:phone', $church->osm->taglist)) {
                        $return['phone'] = $church->osm->taglist['contact:phone'];
                    } elseif (\array_key_exists('phone', $church->osm->taglist)) {
                        $return['phone'] = $church->osm->taglist['phone'];
                    }

                    if (\array_key_exists('contact:email', $church->osm->taglist)) {
                        $return['email'] = $church->osm->taglist['contact:email'];
                    } elseif (\array_key_exists('email', $church->osm->taglist)) {
                        $return['email'] = $church->osm->taglist['email'];
                    }
                }
                // printr($church);

                $return['additional_information'] = 'Source: https://miserend.hu/templom/'.$church->id;

                $this->return[] = $return;
            } else {
                $this->return[] = [
                    'church_id' => $church->id,
                    'service_times' => $serviceTimes->string,
                ];
            }
        }
    }
}
