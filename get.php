<?

include_once('config.inc');
dbconnect();

$id=$_GET['id'];
$kod=$_GET['kod'];

if($id==0) {
    echo '';
}


//hírek
if ($kod=='hirek') {
    $query="select szoveg from hirek where id='$id'";
    list($leiras)=mysql_fetch_row(mysql_db_query($db_name,$query));

    echo $leiras;
}
elseif ($kod=='fomenu') {
    $query="select leiras from fomenu where id='$id'";
    list($leiras)=mysql_fetch_row(mysql_db_query($db_name,$query));

    echo $leiras;
}
?>
