<?php

namespace Html\Ajax;

use Illuminate\Database\Capsule\Manager as DB;

class AutocompleteKeyword extends Ajax {

	public $format = "json";

    public function __construct() {
        $this->input = $_REQUEST;
		// TODO: kezeljük azért valahogy, nehogy bajt csináljon!
		
		$solr = new \ExternalApi\SolrApi();
				
		$return = [];
		$response = $solr->search($this->input['text']);		
		if(isset($response->docs)) {
		
			foreach ($response->docs as $doc) {
				$label = $doc->fullName;
				$return[$doc->id] = ['label' => $label, 'value' => '"'.$doc->nev[0].'"'." AND id:".$doc->id];
			}	 
			
			// Ha több találatunk van, mint amennyit megjelenítettünk, akkor 
			if($response->numFound > count($return)) {			
				$return[] = ['label' => 'Van még további '.( $response->numFound - count($return) ).' találat ...', 'value' => $this->input['text']];
			}
		} 
		
		
		$this->content = json_encode(array('results' => $return));
		
		return;
				
    }

}
