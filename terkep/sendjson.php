<?php
/*
	FOR DEBUG purpose only!
 */

switch ($_REQUEST['q']) {
	case 'report':
		$url = "http://miserend.hu/api/v".rand(3,3)."/report";

		$return = array(
			'0' => array('tid'=>rand(1,400),'pid'=>0,'text'=>'ÉN - Hibás pozíció'),
			'1' => array('tid'=>rand(401,800),'pid'=>1,'text'=>'ÉN - Hibás mise adatok'),
			'2' => array('tid'=>rand(801,1200),'pid'=>2,'text'=>'ÉN - Valami barátságos szöveg')
		);
		$ret = $return[rand(0,2)];
		$encode = array('tid'=>$ret['tid'],'pid'=>$ret['pid'],'text'=>$ret['text']);
		if(rand(1,1) == 1) $encode['dbdate'] = time();

		break;
	
	case 'templomok':
		$url = "http://miserend.hu/api/v".rand(3,3)."/templomok";
		$encode = array(
			'format'=>'text',
			'delimiter'=>'@',
			'columns'=>array('name','alt_name','denomination','url','lon','lat')
			);
		break;

	default:
		# code...
		break;
}

if(!isset($url))
	exit;

echo $url.": "; print_R($encode); echo "<br/>";
$content = json_encode($encode);
//echo $content;

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Content-type: application/json"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ( $status != 201 AND $status != 200 ) {
    die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
} else echo "..".$status;


curl_close($curl);

echo $json_response;
$response = json_decode($json_response, true);

echo "<br><br><h3>válasz:</h3>";
print_r($json_response);

?>