<?

function katradio_balmenu() {	
	$kod_tartalom.="<iframe src=http://www.katradio.hu/nowrunning.php width=175 height=350 frameborder=0 scrolling=no></iframe>";
	$kodT[1]=$kod_tartalom;

    return $kodT;
}

switch($op) {
	case '1':
        $hmenuT=katradio_balmenu();
        break;

	case '2':
        $hmenuT=katradio_balmenu();
        break;


}

?>
