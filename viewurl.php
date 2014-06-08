<?

include_once('config.inc');
dbconnect();

$id=$_GET['id'];
$sessid=$_GET['sessid'];

if($id==0) {
    echo "<html><body><script><!-- JavaScript kód elrejtése \n close(); \n // --></SCRIPT></body></html>";
	exit;
}

else {
	$query="select url from reklam where id='$id'";
    list($url)=mysql_fetch_row(mysql_db_query($db_name,$query));
	mysql_db_query($db_name,"update reklam set klikk=klikk+1 where id='$id'");
	
	if(strstr($url,'http://')) header("Location: $url");
	else header("Location: http://www.magyarkurir.hu/$url&sessid=$sessid");
}

?>
