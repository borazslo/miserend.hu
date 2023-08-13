<?php

$url="https://www.openstreetmap.org/api/0.6/node/5896856932";
$url="https://www.openstreetmap.org/api/0.6/permissions";
//$url= "https://master.apis.dev.openstreetmap.org/api/0.6/node/5896856932";
define('OAUTH_CONSUMER_KEY',"EJO3d5uHnMOmjKkgkufhfJpeg4BsmPdfFWdTOwid");
define('OAUTH_CONSUMER_SECRET',"iNVVrLCEIlNCa8GdCoPZKamjJM1m7DlAyWUsXluZ");


$req_url = 'http://www.openstreetmap.org/oauth/request_token';     // OSM Request Token URL
$authurl = 'https://www.openstreetmap.org/oauth/authorize';         // OSM Authorize URL
$acc_url = 'https://www.openstreetmap.org/oauth/access_token';      // OSM Access Token URL
$api_url = 'http://api.openstreetmap.org/api/0.6/';                // OSM API URL


define("CALLBACK_URL", "http://localhost/oauth2client.php");
define("AUTH_URL", "https://www.openstreetmap.org/oauth2/authorize");
define("ACCESS_TOKEN_URL", "https://www.openstreetmap.org/oauth2/access_token");
define("CLIENT_ID", "Qb_8V2E0aQEjs8eFXgHXDJSwUS6SML6htqs96NcsTCA");
define("CLIENT_SECRET", "g2OKQ9isj2pcaextQdjx5xW3KoAa");
define("SCOPE", ""); // optional

  $ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("cache-control: no-cache","Content-Type: application/json"));
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_USERPWD, "borazslo:Maci76Laci");
//curl_setopt($ch, CURLOPT_USERPWD, "borazslo:3X9v2UJVMCVknBX");


curl_setopt($ch, CURLOPT_TIMEOUT, 30);
//curl_setopt($ch, CURLOPT_POST, 1);
//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//curl_setopt($ch, CURLOPT_POSTREDIR, 3);


//curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadName);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  
$curl = $ch;  
  //curl_setopt_array($curl, $params);
	curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

  $response = curl_exec($curl);
  $err = curl_error($curl);
 
  curl_close($curl);
 
  if ($err) {
    echo "cURL Error #01: " . $err;
  } else {
	echo $response;
	exit;
    $response = json_decode($response, true);    
    
	if(array_key_exists("access_token", $response)) return $response;
    if(array_key_exists("error", $response)) echo $response["error_description"];
    echo "cURL Error #02: Something went wrong! Please contact admin.";
  }
