<?php

use Carbon\Carbon;
use ExternalApi\ElasticsearchApi;

class Search {

    public $query =  ["bool" => ["must" => [], "must_not" => []]];
    public $sort = [];
    public $total = 0; // Találatok száma
    public $filters = []; 
    public $massOrChurch = 'church';
    public $pitId = false; 
    private $pit_keepAlive;
    public $search_after = false;    
    private $index;
    
    /**
     * Constructor
     *
     * Initialize the Search helper. Sets default runtime options like timezone
     * and accepts optional parameters that can be used to build the internal
     * query structure ($this->query).
     *
     * @param mixed $massOrChurch Mode selector (e.g. 'mass' or 'church') — caller may
     *                            use this to build different queries
     * @param array $params       Optional associative array of parameters to influence
     *                            query building (filters, ranges, pagination hints)
     */
    function __construct($massOrChurch, $params = []) {
        $this->timezone = 'Europe/Budapest';

        switch ($massOrChurch) {
            case 'mass':
            case 'masses':
                $this->massOrChurch = 'mass';
                $this->index = "mass_index";
                break;
            case 'church':
            case 'churches':
                $this->massOrChurch = 'church';
                $this->index = "churches";
                break;
            default:
                throw new Exception("Invalid massOrChurch parameter: " . $massOrChurch);

        }

        /*
                // templom ID-k
                $query['bool']['must'][] = [
                    "terms" => ["church_id" => array_map('intval', $this->tids)]
                ];
              
                // nyelv                
                if ($this->notLang)   $query['bool']['must_not'][] = ["term" => ["lang" => $this->notLang]];

                // típus
                if ($this->type)      $query['bool']['must'][]     = ["term" => ["types" => $this->type]];
                if ($this->notType)   $query['bool']['must_not'][] = ["term" => ["types" => $this->notType]];

                // rítus                
                if ($this->notRite)   $query['bool']['must_not'][] = ["term" => ["rite" => $this->notRite]];

                if ($this->title) {
                    $query['bool']['must'][] = [
                        "wildcard" => [
                            "title" => "*" . strtolower($this->title) . "*"
                        ]
                    ];
                }

                if ($this->notTitle) {
                    $query['bool']['must_not'][] = [
                        "wildcard" => [
                            "title" => "*" . strtolower($this->notTitle) . "*"
                        ]
                    ];
                }

                if ($this->comment) {
                    $query['bool']['must'][] = [
                        "wildcard" => [
                            "comment" => "*" . strtolower($this->comment) . "*"
                        ]
                    ];
                }

                if ($this->notComment) {
                    $query['bool']['must_not'][] = [
                        "wildcard" => [
                            "comment" => "*" . strtolower($this->notComment) . "*"
                        ]
                    ];
                }
                                
                */
            
                

    }

    function addMust($condition) {
        $this->query['bool']['must'][] = $condition;
    }

    function tids(array $tids) {
        $this->filters[] = "Templom ID-k: " . htmlspecialchars(implode(', ', $tids));
        if($this->massOrChurch === 'mass') {
            $field = "church_id";
        } else {
            $field = "id";
        }
        if (is_array($tids) && count($tids) > 0) {
            $this->query['bool']['must'][] = [
                "terms" => [$field => $tids]
            ];
        }
    }

    function keyword($keyword) {
        if (empty($keyword)) return;
        $this->filters[] = "Kulcsszó: " . htmlspecialchars($keyword);

        if($this->massOrChurch === 'mass') {
            $prefix = 'church.';
        } else 
            $prefix = false;
        
        $bool = [];
        
        $bool['should'][] = [
            "match" => [
                $prefix."varos" => [
                    "query" => $keyword,
                    "operator" => "or",
                    "boost" => 64
                ]
            ]
        ];

        $bool['should'][] = ['term'=>[$prefix.'names'=>[ 'value' => $keyword, 'boost'=>32 ]]];			
        $bool['should'][] = ['term'=>[$prefix.'varos'=>[ 'value' => $keyword, 'boost'=>18 ]]];
        $bool['should'][] = ['term'=>[$prefix.'alternative_names'=>[ 'value' => $keyword, 'boost'=>7 ]]];
        $bool['should'][] = ['match'=>[$prefix.'names'=>[ 'query' => $keyword, 'boost'=>30 ]]];
        $bool['should'][] = ['match'=>[$prefix.'varos'=>[ 'query' => $keyword, 'boost'=>15 ]]];
        $bool['should'][] = ['match'=>[$prefix.'alternative_names'=>[ 'query' => $keyword, 'boost'=>5 ]]];
        $bool['should'][] = ['wildcard'=>[$prefix.'names'=>[ 'value' => '*'.$keyword.'*', 'boost'=>28 ]]];			
        $bool['should'][] = ['wildcard'=>[$prefix.'varos'=>[ 'value' => '*'.$keyword.'*', 'boost'=>12 ]]];
        $bool['should'][] = ['wildcard'=>[$prefix.'alternative_names'=>[ 'value' => '*'.$keyword.'*', 'boost'=>4 ]]];
    
        $this->query['bool']['must'][] = ['bool' => $bool ];
    }

    function notTitle($notTitle) {
        $this->filters[] = "Ne legyen közte " . htmlspecialchars($notTitle);
        if (empty($notTitle)) return;        
        
        $this->query['bool']['must_not'][] = [
                "term" => ['title' => strtolower($notTitle) ]
        ];
                
    }

    function languages(array $languageAbbrevs) {
        $this->filters[] = "Nyelv lehet: <b>" . htmlspecialchars(implode(', ', t($languageAbbrevs))) . "</b>";
        if (is_array($languageAbbrevs) && count($languageAbbrevs) > 0) {
            $this->query['bool']['must'][] = [
                "terms" => ["lang" => $languageAbbrevs]
            ];
        }
    }

    function rites(array $rites) {
        $this->filters[] = "Rítus legyen: <b>" . htmlspecialchars(implode(' vagy ', t($rites))) . "</b>";
        if (is_array($rites) && count($rites) > 0) {
            $this->query['bool']['must'][] = [
                "terms" => ["rite" => $rites]
            ];
        }
    }

    function timeRange($fromDatetime, $toDatetime) {
        $this->filters[] = "Időpont: <b>" . htmlspecialchars(twig_hungarian_date_format($fromDatetime)) . "</b> - <b>" . htmlspecialchars(twig_hungarian_date_format($toDatetime)) . "</b>";                
        $this->query['bool']['must'][] = [
            "range" => [
                "start_date" => [
                    "gte" => $fromDatetime,
                    "lte" => $toDatetime
                ]
            ]
        ];
    }

    function dateRange($fromDate, $toDate) {
        $this->filters[] = "Dátum: " . htmlspecialchars($fromDate) . " - " . htmlspecialchars($toDate);                
        $this->query['bool']['must'][] = [
            "range" => [
                "start_date" => [
                    "gte" => $fromDate . "T00:00:00",
                    "lte" => $toDate . "T23:59:59"
                ]
            ]
        ];
    }
    
    /**
     * day()
     *
     * Convenience helper that sets the search time range to a single calendar day.
     *
     * Accepted input values for $whenDate:
     *  - a weekday name: 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
     *    (these are resolved to the next occurrence of that weekday as a YYYY-MM-DD date)
     *  - the strings 'today' or 'tomorrow'
     *  - an explicit date string in ISO format: 'YYYY-MM-DD'
     *
     * The function validates the resolved date and then calls $this->timeRange()
     * with the day's full interval (00:00 - 23:59). If the input cannot be
     * interpreted as a valid date it throws an exception.
     *
    * Note: api/nearby and api/search also call this function and perform their own local validation.
    * Ensure their validation logic remains consistent with this function's expectations.
     * 
     * @param string $whenDate day name, 'today', 'tomorrow' or date 'YYYY-MM-DD'
     * @throws \Exception when the provided value cannot be parsed to a valid date
     */
    function day($whenDate) {
        if (in_array($whenDate, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])) {
            $whenDate = date('Y-m-d', strtotime("next $whenDate"));
        }
        else if ($whenDate == 'today') {
            $whenDate = date('Y-m-d');
        } else if ($whenDate == 'tomorrow' ) {
            $whenDate = date('Y-m-d', strtotime('+1 day'));
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $whenDate)) {
            throw new \Exception("'whenDate' should be a day or today or a date (yyyy-mm-dd).");
        }
                
        $this->timeRange($whenDate."T00:00", $whenDate."T23:59");
    }

    function addSortBy($field, $order = 'asc') {
        $this->sort[] = [ $field => [ "order" => $order ] ];
    }

    /**
     * Execute the search and return results.
     *
     * The method builds an Elasticsearch query payload from $this->query,
     * executes it against the `mass_index/_search` endpoint and returns
     * the found documents. The start_date field of each hit is parsed as UTC
     * and converted to the configured timezone ($this->timezone). A helper
     * field `start_minutes` is computed (minutes since midnight) for easier
     * client-side filtering/sorting.
     *
     * If $groupByChurch is true the returned array is keyed by church id,
     * otherwise a flat list of result objects is returned.
     *
     * @param int  $from          Pagination offset (default 0)
     * @param int  $size          Number of results to return (default 10)
     * @param bool $groupByChurch When true, results are grouped by church id
     * @return array              Array of hits (or grouped hits) with normalized dates
     */
    function getResults($from = 0, $size = 10, $groupByChurch = false) {
        $this->total = 0;

        $esQuery = [
            "_source" => [
                "excludes" => ["church"]
            ],
            "query" => $this->query,
            "from"  => $from,
            "size"  => $size,
            "track_total_hits" => true
        ];

        // Nagy adatkupacoknál jobb PIT-et nyitni ( openPit() ) és azt használva kérdezgetni le
        if ($this->pitId) {
            $esQuery['pit'] = [
                'id' => $this->pitId,
                'keep_alive' => $this->pit_keepAlive
            ];
            if($this->search_after != false ) {
                $esQuery['search_after'] = $this->search_after;
                $esQuery['from'] = 0;
            }
            $url = '_search';
        } else {
            $url = $this->index.'/_search';
        }
        


        if($this->massOrChurch === 'mass') {
            
            $esQuery['sort'] = [
                [ "start_date" =>  [ "order" => "asc" ] ],
                [ "_score" =>  [ "order" => "desc" ] ],                
                [ "church_id" => [ "order" => "asc" ] ]
            ];        
        } else if ($this->massOrChurch === 'church') {
            
            $esQuery['sort'] = [
                [ "_score" =>  [ "order" => "desc" ] ],
                [ "nev.keyword" =>  [ "order" => "asc" ] ]            
            ];        
        }

        $elastic = new ElasticSearchApi();
        $elastic->curl_setopt(CURLOPT_CUSTOMREQUEST, "GET");
        $this->rawQuery = json_encode($esQuery);
        $elastic->buildQuery($url, json_encode($esQuery));
        $elastic->run();

        $result = [];   

        if (isset($elastic->jsonData->hits->hits)) {
            $this->countHits = count($elastic->jsonData->hits->hits);
            $this->total = $elastic->jsonData->hits->total->value;    
            if($this->massOrChurch === 'mass') {
                $result = $this->prepareMassesResults($elastic->jsonData->hits->hits, $groupByChurch);
            } else if ($this->massOrChurch === 'church') {
                $result = $this->prepareChurchesResults($elastic->jsonData->hits->hits);
            }                        
            $lastHit = end($elastic->jsonData->hits->hits);
            if($lastHit) {
                $this->search_after = $lastHit->sort;
            }
            
        }

        return $result;
    }

    public function openPit($keepAliveTime) {
        $this->pit_keepAlive = $keepAliveTime;

        $elastic = new ElasticSearchApi();
        $elastic->curl_setopt(CURLOPT_CUSTOMREQUEST, "POST");
        $elastic->buildQuery($this->index.'/_pit?keep_alive='.$this->pit_keepAlive);
        $elastic->run();

        if (isset($elastic->jsonData->id)) {
            $this->pitId = $elastic->jsonData->id;                        
        } else {
            throw new Exception("Failed to open PIT: " . $elastic->error);
        }
        return true;
    }

    public function closePit($pitId = false) {
        if (!$pitId) {
            $pitId = $this->pitId;
        }
        if (!$pitId) {
            throw new Exception("No PIT ID provided or opened.");
        }

        $elastic = new ElasticSearchApi();
        $elastic->curl_setopt(CURLOPT_CUSTOMREQUEST, "DELETE");
        $elastic->buildQuery('_pit', json_encode(['id' => [$pitId]]));
        $elastic->run();
 
        if (isset($elastic->jsonData->succeeded) && $elastic->jsonData->succeeded == true) {
            if ($pitId == $this->pitId) {
                $this->pitId = false;
            }
            return true;
        } else {
            throw new Exception("Failed to close PIT: " . $elastic->error);
        }
    }

    private function prepareMassesResults($hits, $groupByChurch) {
        $result = [];
        foreach ($hits as $hit) {
            $churchId = $hit->_source->church_id;

            $source = $hit->_source;
            $source->score = $hit->_score;

            $dateUtc = Carbon::parse($source->start_date)->setTimezone('UTC');

            if ($this->timezone !== 'UTC') {
                $dateLocal = $dateUtc->copy()->setTimezone($this->timezone);
                $source->start_date = $dateLocal->format('c');
                $source->start_minutes = $dateLocal->hour * 60 + $dateLocal->minute;
            } else {
                $source->start_date = $dateUtc->format('c');
                $source->start_minutes = $dateUtc->hour * 60 + $dateUtc->minute;
            }

            if($groupByChurch) {
                if (!isset($result[$churchId])) $result[$churchId] = [];
                $result[$churchId][] = $source;
            } else {
                $result[] = $source;
            }
        }
        return $result;

    }

    private function prepareChurchesResults($hits) {
        $result = [];
        foreach ($hits as $hit) {             
            $source = $hit->_source; 
            $source->score = $hit->_score;           
            $result[] = $source;
        }
        return $result;
    }

    function getFilters() {
        return $this->filters;
    }

}
