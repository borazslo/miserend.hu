<?php

include("config.inc");
dbconnect();

$id=$_GET['id'];

$query="select id from templomok where turistautak='$id'";
$lekerdez=mysql_db_query($db_name,$query);
list($tid)=mysql_fetch_row($lekerdez);

echo $tid;


?>