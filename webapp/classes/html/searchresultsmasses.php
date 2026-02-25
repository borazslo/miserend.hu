<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class SearchResultsMasses extends Html {

    public function __construct() {
        parent::__construct();
        global $user, $config;

        $search = new \Search('masses', $_REQUEST);
        if(isset($_REQUEST['timezone']) AND $_REQUEST['timezone'] != '') {
            $search->timezone = $_REQUEST['timezone'];
        }

        // Diocese filter
        $ehm = isset($_REQUEST['ehm']) ? $_REQUEST['ehm'] : 0;
        if ($ehm > 0) {
            $ehmnev = DB::table('egyhazmegye')->where('id',$ehm)->pluck('nev')[0];
            $search->addMust(["wildcard" => ['church.egyhazmegye.keyword' => $ehmnev ]]); 
            $search->filters[] = "Egyházmegye: <b>" . htmlspecialchars($ehmnev) ." egyházmegye</b>";                              
        }
            
        // nyelvek filter
        $tnyelv = isset($_REQUEST['tnyelv']) ? $_REQUEST['tnyelv'] : false;
        if($tnyelv == "h") $tnyelv = "hu";
        if ($tnyelv AND $tnyelv != '0') {
            $search->addMust(["term" => ['church.nyelvek' => $tnyelv ]]); 
            $search->filters[] = "Amelyik templomban van <b>" . htmlspecialchars(t('LANGUAGES.'.$tnyelv)) . "</b> nyelvű mise.";                              
        }
        
        // Main keyword search
        if (isset($_REQUEST['kulcsszo']) AND $_REQUEST['kulcsszo'] != '') {            
            $search->keyword($_REQUEST['kulcsszo']);
        }
    
        // Time range search       
        if(isset($_REQUEST['mikordatum']) AND $_REQUEST['mikordatum'] != '') {            
            $mikordatum = $_REQUEST['mikordatum'];         
            $hourFrom = ( isset($_REQUEST['mikortol']) and $_REQUEST['mikortol'] != '') ? $_REQUEST['mikortol'] : '00:00';
            $hourTo = "23:59";
            $search->timeRange($mikordatum."T".$hourFrom.":00", $mikordatum."T".$hourTo.":00");
        } else 
            $search->timeRange(date('Y-m-d')."T00:00", date('Y-m-d',strtotime("+ 6 days"))."T23:59");
        // Languages
        $nyelv = isset($_REQUEST['nyelv']) ? $_REQUEST['nyelv'] : false;        
        if (!empty($nyelv)) {
            $search->languages([$nyelv]);
        }
      
        // Exclude 'Igeliturgia' masses unless specifically requested
        $ige = isset($_REQUEST['liturgy']) ? $_REQUEST['liturgy'] : false;
        if (empty($ige)) {
            $search->notTitle('Igeliturgia'); 
        }

        // Process advanced rites/types filters (if provided)
        $typesReq = isset($_REQUEST['types']) ? $_REQUEST['types'] : [];
        $ritesReq = isset($_REQUEST['rites']) ? $_REQUEST['rites'] : [];

        if (!empty($typesReq) || !empty($ritesReq)) {
            // 1) Handle rites.must_not - exclude these rites entirely
            if (!empty($ritesReq['must_not'])) {
                $mustNotRites = array_filter(array_map('trim', explode(',', $ritesReq['must_not'])));
                foreach ($mustNotRites as $r) {
                    if ($r === '') continue;
                    $search->filters[] = "A rítus nem lehet: <i>" . htmlspecialchars(t($r)) . "</i>";
                    // add to query must_not
                    $search->query['bool']['must_not'][] = [ 'term' => ['rite' => $r] ];
                }
            }

            // 2) Handle rites.should - at least one of these rite+type combinations must match
            if (!empty($ritesReq['should'])) {
                $shouldRites = array_filter(array_map('trim', explode(',', $ritesReq['should'])));
                $shouldClauses = [];

                // Add a human-readable filter listing allowed rites (translated)
                if (!empty($shouldRites)) {
                    $translated = array_map(function($r){ return t($r); }, $shouldRites);
                    $search->filters[] = 'A rítus lehet <i>' . implode('</i> vagy <i>', $translated) . '</i>';
                }
                foreach ($shouldRites as $r) {
                    
                    if ($r === '') continue;
                    // Build clause requiring this rite
                    $cl = [ 'bool' => [ 'must' => [ [ 'term' => ['rite' => $r] ] ] ] ];

                    // If types specification exists for this rite, apply its should/must_not rules
                    if (!empty($typesReq[$r]) && is_array($typesReq[$r])) {
                        // parse comma separated lists
                        $tShould = [];
                        if (!empty($typesReq[$r]['should'])) {
                            if (is_array($typesReq[$r]['should'])) {
                                $tShould = $typesReq[$r]['should'];
                            } else {
                                $tShould = array_filter(array_map('trim', explode(',', $typesReq[$r]['should'])));
                            }
                            
                        }
                        $tMustNot = [];
                        if (!empty($typesReq[$r]['must_not'])) {
                            if (is_array($typesReq[$r]['must_not'])) {
                                $tMustNot = $typesReq[$r]['must_not'];
                            } else {
                                $tMustNot = array_filter(array_map('trim', explode(',', $typesReq[$r]['must_not'])));
                            }
                        }

                        // If there are positive type constraints, require that the event has at least one of them
                        if (!empty($tShould)) {
                            // use 'terms' to require any of the types
                            $shouldTerms = [];
                            foreach ($tShould as $tt) {
                                if ($tt === '') continue;
                                $shouldTerms[] = [ 'term' => ['types' => $tt] ];
                            }
                            $cl['bool']['must'][] = [ 'bool' => [ 
                                'should' => $shouldTerms, 
                                'minimum_should_match' => 1 
                            ]];                            
                        }

                        // If there are negative type constraints, add must_not for each
                        if (!empty($tMustNot)) {
                            foreach ($tMustNot as $tt) {
                                $cl['bool']['must_not'][] = [ 'term' => ['types' => $tt] ];
                            }                            
                        }
                        foreach($tShould as $k => $ts)  $tShould[$k] = t($ts);
                        foreach($tMustNot as $k => $ts)  $tMustNot[$k] = t($ts);

                        if (!empty($tShould) or !empty($tMustNot)) {
                            $search->filters[] = "Ha <b>".t($r)."</b> rítus, akkor  " . 
                                (!empty($tShould) ? "legyen: <b>" . implode('</b> vagy <b>', $tShould) . "</b>" : '') . 
                                (!empty($tShould) && !empty($tMustNot) ? ", de " : '') .
                                (!empty($tMustNot) ? "ne legyen: <b>" . implode('</b> vagy <b>', $tMustNot) . "</b>" : '');
                        }
                    }

                    $shouldClauses[] = $cl;
                }

                if (!empty($shouldClauses)) {
                    // Ensure at least one of the should clauses matches
                    $search->query['bool']['must'][] = [ 'bool' => [ 'should' => $shouldClauses, 'minimum_should_match' => 1 ] ];                    
                }
            }
            
        }

        
        $templomurlap = "<img src=/img/space.gif width=5 height=6><br><a href=\"/\" class=link><img src=/img/search.gif width=16 height=16 border=0 align=absmiddle hspace=2><b>Vissza a főoldali keresőhöz</b></a><br><img src=/img/space.gif width=5 height=6>";


        $min = isset($_REQUEST['min']) ? $_REQUEST['min'] : 0;       
		$leptet = isset($_REQUEST['leptet']) ? $_REQUEST['leptet'] : 25;	
        $offset = $this->pagination->take * $this->pagination->active;
        $limit = $this->pagination->take;     	        
        $results = $search->getResults($offset, $limit, false);
                
                        
        if ($search->total != 0) {                   
            foreach ($results as &$result) {
                $result->church = \Eloquent\Church::find($result->church_id)->toArray();       
                
            }
        }

        //Data for pagination
		$params = [];
		foreach( ['varos','tavolsag','hely','kulcsszo','gorog','tnyelv','espker','ehm','types','rites',
            'mikordatum', 'mikortol','nyelv','zene','kor','ritus','tnyelv'] as $param ) {
		
			if( isset($_REQUEST[$param]) AND $_REQUEST[$param] != ''  AND $_REQUEST[$param] != '0' ) {
				$params[$param] = $_REQUEST[$param];
			}
		}
		$params['q'] = 'SearchResultsMasses';
        $url = \Pagination::qe($params, '/?' );
        $this->pagination->set($search->total, $url );

        $this->filters = $search->getFilters();

        $this->alert = (new \ExternalApi\BreviarskApi())->LiturgicalAlert();

        $this->setTitle("Szentmise kereső");
        
        $this->templomurlap = $templomurlap;
        $this->template = 'search/resultsmasses.twig';
        
        $this->results = $results;                
    }

}
