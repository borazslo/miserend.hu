<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\ExternalApi;

https:// github.com/molnarm/zsolozsma#api

class LiturgiatvApi extends ExternalApi
{
    public $name = 'liturgiatv';
    public $apiUrl = 'https://liturgia.tv/';
    public $cache = '6 hours';
    public $testQuery = ''; // Ez a minden templomot lekérő query
    public $strictFormat = false; // Ha létezik a templom, de nincs hozzá liturgia.tv, akkor is 404-et de szöveggel

    public function getByChurch($church_id)
    {
        $this->query = 'miserend/'.$church_id.'/';

        $this->runQuery();

        return $this->jsonData;
    }

    public function buildQuery()
    {
        $this->rawQuery = $this->query.'?json';
    }
}
