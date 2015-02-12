<?php
include 'load.php';
set_time_limit('300');

switch($_REQUEST['q']) {

	case 'daily':
		for($v=1;$v<4;$v++) {
			$file = 'fajlok/sqlite/miserend_v'.$v.'.sqlite3';
			generateSqlite($v,$file);
			//upload2ftp('*host*','*username*','*password*','web/'.$file,$file);
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

		//veeeery sloooow
		neighboursUpdate();
		break;
}

?>