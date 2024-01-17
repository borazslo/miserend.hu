<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\ExternalApi;

class KozossegekApi extends ExternalApi
{
    public $name = 'kozossegek';
    public $apiUrl = 'https://kozossegek.hu/api/v1/';
    public $cache = '1 week'; // false or any time in strtotime() format
    public $testQuery = 'miserend/1168';

    public function buildQuery(): void
    {
        global $config;
        $this->rawQuery = $this->query;
    }
}
