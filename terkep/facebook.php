<?
//
//session_start();

require_once("library/facebook.php");
		
	$fb = array(
		'appId' => '292565247493923',
		'secret' => 'c1bebf710f66979485fcf0be62eb5be3',
		'cookie' => true,
	);
	$facebook = new Facebook($fb);

	//print_R($facebook);
	
	if($_GET) $get = '?';
	foreach($_GET as $k=>$i) {
		if($k != 'state' AND $k != 'code' AND $k != 'arg') {
		$get .= $k."=".$i;
		if(count($_GET)-1<$i) $get .= "&";
		}
	}
	$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$get;
	$url = str_replace('%26arg%3Ddestroy','',$url); 
	$redirect = 'http://terkep.miserend.hu'; //$url;
	//exit;
	
	if($_GET['arg']=='destroy') {$facebook->destroySession();	unset($_SESSION['token']);}
	if(preg_match('/\?/i',$redirect)) $and = '&'; else $and = '?';
	
	//print_R($_SESSION);
	
	if($_COOKIE['token']!='') $token = $_COOKIE['token'];
	elseif($_SESSION['token']!='') $token = $_SESSION['token'];
	if(!$token) {
		if($_GET['code']!='') {
			$url = "https://graph.facebook.com/oauth/access_token?client_id=".$fb['appId']."&redirect_uri=".$redirect."&client_secret=".$fb['secret']."&code=".$_GET['code'];
			$return = curlRedir($url);
			preg_match('/access_token=([^&]*)/i',$return,$match);
			$token = $match[1];
			$_SESSION['token'] = $token;
			setcookie("token", $token, time()+60*60*24);
		}
	
	}
	if($token) setcookie("token", $token, time()+60*60*24);
	
	$facebook->setAccessToken($token);
	// make protected to public getUserFromAccessToken() in base_facebook.php!!
	$user_id = $facebook->getUserFromAccessToken();	
	if (!$user_id) {    
		$url = $facebook->getLoginUrl(array(
			'canvas' => 1,
			'fbconnect' => 0,
			'redirect' => $redirect,
			'scope' => 'offline_access',
		));	
		$url = str_replace('%26arg%3Ddestroy','',$url); 
		
		$fb_login_html = " <a href=".$url.">belépés</a> ";
		$fb_login_url = $url;
		$facebook = false;
		//$fb_logout_html = " <a href=\"".$redirect.$and."arg=destroy\">destroy</a> ";
		//exit;
	}
	else {
		$fb_logout_html = " <a href=\"".$redirect.$and."arg=destroy\">kilépés</a> ";
		try{
			$me = $facebook->api("/me");
			$user = $me;
		}
		catch (FacebookApiException $e) {
			print_R($e);   
		}
		
  }
  //print_R($_SESSION);
	//$user = array('id'=>'12345','name'=>'macko'); //$user = array('id'=>$user_id,'name'=>$user_id); exit;
 
function curlRedir($url) {
    $go = curl_init($url);
    curl_setopt ($go, CURLOPT_URL, $url);

    static $curl_loops = 0;
    static $curl_max_loops = 20;

    if ($curl_loops++>= $curl_max_loops)
    {
        $curl_loops = 0;
        return FALSE;
    }

	$agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

curl_setopt($go, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($go, CURLOPT_VERBOSE, true);
curl_setopt($go, CURLOPT_RETURNTRANSFER, true);
curl_setopt($go, CURLOPT_USERAGENT, $agent);
    curl_setopt($go, CURLOPT_HEADER, true);
    curl_setopt($go, CURLOPT_RETURNTRANSFER, true);

    $data = curl_exec($go);
    $pattern = '/self\.location\.href=\'(.+)\';/';
    preg_match($pattern, $data, $matches);
    curl_close($go);
    return $data;
	return $matches[1];
}


//echo $fb_logout_html.$fb_login_html; 


	?>