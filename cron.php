<?php
include 'load.php';
set_time_limit('300');
ini_set('memory_limit', '512M');


switch($_REQUEST['q']) {

	case 'hourly':
		updateDistances();
		break;

	case 'daily':
		for($v=1;$v<5;$v++) {
			$file = 'fajlok/sqlite/miserend_v'.$v.'.sqlite3';
			generateSqlite($v,$file);
		}
		break;


	case 'weekly':
		updateImageSizes();
		generateMassTmp();

		updateCleanMassLanguages();
		updateGorogkatolizalas();
		updateDeleteZeroMass();
		updateComments2Attributes();
		//not so fast!
		updateAttributesOptimalization();

		break;
}



?>