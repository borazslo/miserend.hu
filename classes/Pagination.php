<?php

class Pagination {

    public $take = 10;
    public $active = 0;
    public $maxOptionsToShown = 10;

    function set($countResults, $url = false) {
        $this->max = ceil($countResults / $this->take);

        if ($this->max == 1) {
            return;
        }

        if ($this->maxOptionsToShown > $this->max) {
            $localMin = 0;
            $localMax = $this->max;
        } else {
            if ($this->maxOptionsToShown % 2 == 0) {
                $this->maxOptionsToShown--;
            }
            $showNext = ( $this->maxOptionsToShown + 1 ) / 2;

            $localMin = $this->active - $showNext + 2;
            $localMax = $this->active + $showNext - 1;

            if ($localMin < 0) {
                $localMin = 0;
                $localMax = $this->maxOptionsToShown;
            }
            if ($localMax > $this->max) {
                $localMin = $this->max - $this->maxOptionsToShown;
                $localMax = $this->max;

                if ($localMin < 0) {
                    $localMin = 0;
                }
            }
        }

        for ($page = $localMin; $page < $localMax; $page++) {
            $this->pages[$page] = $this->createPaginatedUrl($page, $url);
        }

        if ($this->active > $showNext) {
            $this->previous = $this->createPaginatedUrl(($this->active - $showNext), $url);
        }
        if ($this->active + $showNext < ( $this->max - 1 )) {
            $this->next = $this->createPaginatedUrl(($this->active + $showNext), $url);
        }
    }

    private function createPaginatedUrl($page, $url = false) {
        if ($url == false) {
            $url = $_SERVER['REQUEST_URI'];
        }
        return $this->qe($url, ['page' => $page, 'take' => $this->take]);
    }

    private function qe($url, array $new_params, $overwrite = true) {
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        if ($overwrite) {
            $new_params = $new_params + $params;
        } else {
            $new_params = $params + $new_params;
        }
        return parse_url($url, PHP_URL_PATH) . '?' . http_build_query($new_params);
    }

}
