<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\ExternalApi;

// http://wiki.openstreetmap.org/wiki/Overpass_API#Introduction

class OverpassApi extends ExternalApi
{
    public $name = 'overpass';
    public $apiUrl = 'http://overpass-api.de/api/interpreter';
    public $testQuery = 'nwr["name"="Tápiószecső"];out geom;';

    public function buildQuery(): void
    {
        $this->rawQuery = '[out:json][timeout:'.$this->queryTimeout.'];';
        $this->rawQuery .= $this->query;
        $this->rawQuery = '?data='.urlencode($this->rawQuery);
    }

    public function buildEnclosingBoundariesQuery($lat, $lon)
    {
        $this->queryFilter = "['type'='boundary']";
        $this->buildEnclosingFeaturesQuery($lat, $lon);
    }

    public function buildEnclosingFeaturesQuery($lat, $lon)
    {
        $this->query = 'is_in('.$lat.','.$lon.')->.a;'
                .'node'.$this->queryFilter.'(pivot.a);out bb center tags;'
                .'way'.$this->queryFilter.'(pivot.a);out bb center tags;'
                .'relation'.$this->queryFilter.'(pivot.a);out bb center tags;';
        $this->buildQuery();
    }

    public function buildSimpleQuery($filter = false)
    {
        if ($filter) {
            $this->queryFilter = $filter;
        }
        $this->query = '('
                .'node'.$this->queryFilter.';'
                .'way'.$this->queryFilter.';'
                .'relation'.$this->queryFilter.';);'
                .'out body qt center;';
        $this->buildQuery();
    }

    public function downloadEnclosingBoundaries($lat, $lon)
    {
        $this->buildEnclosingBoundariesQuery($lat, $lon);
        $this->run();
    }

    public function downloadUrlMiserend()
    {
        $this->buildSimpleQuery("['url:miserend']");
        $this->run();
    }

    /* Innen már nem csak OverpassApi */
    public function deleteUrlMiserend()
    {
        $tes = \App\Model\Osm::all()->filter(function ($model) {
            return ($model->tags()->where('name', 'url:miserend')->first()) ? true : false;
        });
        foreach ($tes as $t) {
            $t->delete();
        }
    }

    public function updateUrlMiserend()
    {
        $this->deleteUrlMiserend();
        $this->downloadUrlMiserend();
        $this->saveElement();
        $this->saveChurchOsmRelation();
    }

    public function updateEnclosing(\App\Model\OSM $osm)
    {
        $this->downloadEnclosingBoundaries($osm->lat, $osm->lon);
        $this->saveElement();
        $this->saveOsmEnclosingRelation($osm);
    }

    public function saveChurchOsmRelation()
    {
        $osmElements = \App\Model\Osm::whereHas('tags', function ($query) {
            $query->where('name', 'url:miserend');
        });
        foreach ($osmElements->get() as $element) {
            $urlMiserend = $element->tags->where('name', 'url:miserend')->first()->value;
            preg_match('/miserend\.hu\/\?{0,1}templom(\/|=)([0-9]{1,5})/i', $urlMiserend, $match);
            if (isset($match[2])) {
                $element->churches()->detach([$match[2]]);
                $element->churches()->attach([$match[2]]);
            }
        }
    }

    public function saveOsmEnclosingRelation(\App\Model\OSM $osm)
    {
        foreach ($this->jsonData->elements as $element) {
            $osmElement = \App\Model\OSM::where('osmid', $element->id)->where('osmtype', $element->type)->first();
            $sync[] = $osmElement->id;
        }
        $osm->enclosing()->sync($sync);
    }

    public function saveElement()
    {
        if (!$this->jsonData->elements) {
            throw new \Exception('Missing Json Elements from OverpassApi Query');
        }
        $now = date('Y-m-d H:i:s', time());

        foreach ($this->jsonData->elements as $element) {
            if (isset($element->center->lat)) {
                $element->lat = $element->center->lat;
            }
            if (isset($element->center->lon)) {
                $element->lon = $element->center->lon;
            }

            $check = \App\Model\OSMTag::where('osmtype', $element->type)
                    ->where('osmid', $element->id)
                    ->where('updated_at', '>', $now)
                    ->first();
            // Mi a csudáért kellett ez a !check rész? Ezért nem frissültek az adatok. Uuuupsz. e0c4c4e7b19e011c5aa4ac3c474da92536eea77a
            // if(!$check) {
            foreach ($element->tags as $name => $value) {
                // echo $element->type."-".$element->id."-".$name."<br/>";
                $tag = \App\Model\OSMTag::firstOrNew(['osmtype' => $element->type, 'osmid' => $element->id, 'name' => $name]);
                $tag->value = $value;
                $tag->save();
                $tag->touch();
            }
            $tags = \App\Model\OSMTag::where('osmtype', $element->type)
                    ->where('osmid', $element->id)
                    ->where('updated_at', '<', $now)->get();
            foreach ($tags as $tag) {
                $tag->delete();
            }
            // }
        }
    }
}
