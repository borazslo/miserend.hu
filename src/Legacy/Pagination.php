<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

class Pagination
{
    public $take = 20;
    public $active = 0;
    public $maxOptionsToShown = 10;

    public function set($countResults, $url = false)
    {
        $this->skip = (int) ($this->take * $this->active);

        $this->resultsCount = $countResults;
        $this->count = ceil($countResults / $this->take);

        if (1 == $this->count) {
            return;
        }

        if ($this->maxOptionsToShown >= $this->count) {
            $localMin = 0;
            $localMax = $this->count - 1;
        } else {
            if ($this->active < $this->maxOptionsToShown - 1) {
                $localMin = 0;
                $localMax = $this->maxOptionsToShown - 2;
                $this->next = $this->createPaginatedUrl($localMax + 1, $url);
            } elseif ($this->active > $this->count - $this->maxOptionsToShown) {
                $localMax = $this->count - 1;
                $localMin = $this->count - $this->maxOptionsToShown + 1;
                $this->previous = $this->createPaginatedUrl($localMin - 1, $url);
            } else {
                $nextCount = ceil($this->maxOptionsToShown / 2) - 2;
                $localMin = $this->active - $nextCount;
                $localMax = $this->active + $nextCount;
                $this->previous = $this->createPaginatedUrl($localMin - 1, $url);
                $this->next = $this->createPaginatedUrl($localMax + 1, $url);
            }
        }
        for ($page = $localMin; $page <= $localMax; ++$page) {
            $this->pages[$page] = $this->createPaginatedUrl($page, $url);
        }
    }

    private function createPaginatedUrl($page, $url = false)
    {
        return $this->qe(['page' => $page, 'take' => $this->take], $url);
    }

    public static function qe(array $new_params, $url = false, $overwrite = true)
    {
        if (false == $url) {
            $url = $_SERVER['REQUEST_URI'];
        }
        parse_str(parse_url($url, \PHP_URL_QUERY), $params);
        if ($overwrite) {
            $new_params += $params;
        } else {
            $new_params = $params + $new_params;
        }

        return parse_url($url, \PHP_URL_PATH).'?'.http_build_query($new_params);
    }
}
