<?php

namespace Html\Ajax;

use Illuminate\Database\Capsule\Manager as DB;

class AutocompleteKeyword extends Ajax {

	public $format = "json";

    public function __construct() {
        $this->input = $_REQUEST;
		// TODO: kezeljük azért valahogy, nehogy bajt csináljon!

		$limit = 9;
		
		$return = [];
		$results = searchChurches(['kulcsszo'=>$this->input['text']], 0, $limit);
		// FIXME for Issue #257
		foreach ($results['results'] as $key => $result) {
			$label = $result['nev']. " (";
			if($result['ismertnev'])	$label .= $result['ismertnev'].", ";
			$label .= ( is_array($result['varos']) ? $result['varos'][0] : $result['varos']  ) .")";
			//$label .= " (score: ".$result['score'].")";

			$return[] = ['label' => $label, 'value' => $result['nev'] . ' id:' . $result['id']];
			
		}
	
		if($results['sum'] > $limit ) {			
			$return[] = ['label' => 'Van még további '.( $results['sum'] - $limit ).' találat ...', 'value' => $this->input['text']];
		}
		
		$this->content = json_encode(array('results' => $return));
		
		return;
				
    }

}
