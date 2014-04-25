<?php
 exit;
include 'db.php';
if(isset($_REQUEST['tid']) AND isset($_REQUEST['lat']) AND isset($_REQUEST['lng']) AND isset($_REQUEST['checked'])) {
	$query = "UPDATE terkep_geocode SET lat = '".$_REQUEST['lat']."', lng = '".$_REQUEST['lng']."', checked = '".$_REQUEST['checked']."' WHERE tid = ".$_REQUEST['tid']." LIMIT 1";
	db_query($query);
	$insert = "INSERT into terkep_geocode (lat,lng,checked,tid) VALUES ('".$_REQUEST['lat']."','".$_REQUEST['lng']."','".$_REQUEST['checked']."','".$_REQUEST['tid']."')"; 
	db_query($insert);
	echo "Frissítve \n<br>";
	echo '<a href="terkep_gyarto.php">gyárts</a>';

}

?>

<form action="terkep_updatetemplom.php" method="post">
	Templom id: <input type="text" name="tid" value="<?php echo $_REQUEST['tid']; ?>">
	Lat (46.): <input type="text" name="lat" value="<?php echo $_REQUEST['lat']; ?>">
	Lng (16.): <input type="text" name="lng" value="<?php echo $_REQUEST['lng']; ?>">
	<input type="hidden" name="checked" value="2">
	<input type="submit" name="hajrá">
</form>

<?php




?>