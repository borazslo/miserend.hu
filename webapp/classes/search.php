<?php

use Carbon\Carbon;
use ExternalApi\ElasticsearchApi;

class Search {

    public $query =  ["bool" => ["must" => [], "must_not" => []]];
    public $total = 0; // Találatok száma
    public $filters = []; 
    public $massOrChurch = 'church';
    
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
                break;
            case 'church':
            case 'churches':
                $this->massOrChurch = 'church';
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
        $this->filters[] = "Nyelvek: " . htmlspecialchars(implode(', ', $languageAbbrevs));
        if (is_array($languageAbbrevs) && count($languageAbbrevs) > 0) {
            $this->query['bool']['must'][] = [
                "terms" => ["lang" => $languageAbbrevs]
            ];
        }
    }

    function rites(array $rites) {        
        $this->filters[] = "Rítus legyen " . htmlspecialchars(implode(', ', $rites));
        if (is_array($rites) && count($rites) > 0) {
            $this->query['bool']['must'][] = [
                "terms" => ["rite" => $rites]
            ];
        }
    }

    function timeRange($fromDatetime, $toDatetime) {
        $this->filters[] = "Időpont: " . htmlspecialchars($fromDatetime) . " - " . htmlspecialchars($toDatetime);                
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
            "sort" => [
                [ "start_date" =>  [ "order" => "asc" ] ],
                [ "church_id" => [ "order" => "asc" ] ]
            ],       
        ];

        $elastic = new ElasticSearchApi();
        $elastic->curl_setopt(CURLOPT_CUSTOMREQUEST, "GET");
        $elastic->buildQuery('mass_index/_search', json_encode($esQuery));
        $elastic->run();

        $result = [];   

        if (isset($elastic->jsonData->hits->hits)) {
            $this->total = $elastic->jsonData->hits->total->value;    

            foreach ($elastic->jsonData->hits->hits as $hit) {
                $churchId = $hit->_source->church_id;

                $source = $hit->_source;

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
        }

        return $result;
    }

    function getFilters() {
        return $this->filters;
    }

}
