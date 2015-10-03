<?php


header("Content-type: text/html; charset=utf-8");
require_once("turistautak/mapnikdb.php");
if (!empty($_GET) && !empty($_GET["miserend"]) && $_GET["miserend"] > 0 && $_GET["miserend"] < 102) {

$nev = urldecode($_GET["miserend"]);
$cs = '/(\(|}|\)|\/|{|<|>|]|\"|\*|\'|[a-z]|[A-Z])/i';
$nev = preg_replace($cs, '', $nev);


echo '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>JOSM miserend</title>  


</head>
<body>


<table style="margin: 1em 1em 1em 0; background: #f9f9f9; border: 1px #aaa solid; border-collapse: collapse; font-size: 95%;" border="1" cellpadding="4" cellspacing="0">


<tbody><tr style="background-color:#E9E9E9">
<th>OSM név
</th>
<th>Felekezet
</th>
<th>JOSM link
</th>
<th>MR név
</th>
<th>MR alt. név
</th>
<th>JOSM felül ír
</th>
<th>JOSM hozzáad
</th>
</tr>';

$i = 1;

   $duma= file_get_contents("miserend/".$nev.".txt");

   $tel = explode("\n", trim($duma));

   foreach ($tel as $telnev) {
$kk=explode('@', $telnev);
//ha van koordináta
if($kk[4] !='' && $kk[4] !='') {
//pontot keresünk 60 méteres körzetben  
$query1 ="select name,osm_id,denomination,\"url:miserend\" as mr,alt_name as al from planet_osm_point  where amenity='place_of_worship' and ST_DWithin(way,ST_Transform(ST_GeomFromText('POINT(" . $kk[4] . " " . $kk[5] . ")',4326),900913), 60);";
$pont = pg_query($query1);
//poligon középpontot keresünk 60 méteres körzetben  
$query2 ="select name,osm_id,denomination,\"url:miserend\" as mr,alt_name as al from planet_osm_polygon where amenity='place_of_worship' and ST_DWithin(ST_Centroid(way),ST_Transform(ST_GeomFromText('POINT(" . $kk[4] . " " . $kk[5] . ")',4326),900913), 60);";
$poly = pg_query($query2);

if(pg_num_rows($pont) || pg_num_rows($poly) > 0){
for($p = 0 ; $p < pg_num_rows($pont) ; $p++){
    $row = pg_fetch_assoc($pont);

if ($row['mr'] != '') {
$mr = '<br>(<a href="' . $row['mr']. '" target="_blank">' . $row['mr']. '</a>)';
}else { $mr = '';}

if ($row['al'] != '') {
$al = '<br>alt_name:<br>' . $row['al'];
}else { $al = '';}


echo '<tr><td>'.$row['name'].''.$al.''.$mr.'</td><td>' . $row['denomination'].'</td><td><a href="http://localhost:8111/load_object?new_layer=false&objects=n' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '" target="_blank">n' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '</a></td><td>' .$kk[0].'</td><td>' .$kk[1].'</td><td>Mindent: <br><a href="http://localhost:8111/load_object?new_layer=false&objects=n' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '&addtags=name=' .$kk[0].'%7Calt_name=' .$kk[1].'%7Cdenomination=' .$kk[2].'%7Curl:miserend=' .$kk[3].'%7Creligion=christian%7Camenity=place_of_worship" target="_blank">n' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '</a> <br>Vagy csak:<br><a href="http://localhost:8111/load_object?new_layer=false&objects=n' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '&addtags=name=' .$kk[0].'" target="_blank">' .$kk[0].'</a><hr><a href="http://localhost:8111/load_object?new_layer=false&objects=n' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '&addtags=alt_name=' .$kk[1].'" target="_blank">' .$kk[1].'</a><hr><a href="http://localhost:8111/load_object?new_layer=false&objects=n' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '&addtags=denomination=' .$kk[2].'" target="_blank">' .$kk[2].'</a><hr><a href="http://localhost:8111/load_object?new_layer=false&objects=n' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '&addtags=url:miserend=' .$kk[3].'" target="_blank">' .$kk[3].'</a></td></tr>';

}


for($p = 0 ; $p < pg_num_rows($poly) ; $p++){
    $row = pg_fetch_assoc($poly);

if ($row['mr'] != '') {
$mr = '<br>(<a href="' . $row['mr']. '" target="_blank">' . $row['mr']. '</a>)';
}else { $mr = '';}

if ($row['al'] != '') {
$al = '<br>alt_name:<br>' . $row['al'];
}else { $al = '';}

$str = $row['osm_id'];
$usz = str_replace('-','',$str);

if(substr($row['osm_id'],0,1) !='-') {

$jl = '<tr><td>'.$row['name'].''.$al.''.$mr.'</td><td>' . $row['denomination'].'</td><td><a href="http://localhost:8111/load_object?new_layer=false&objects=w' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '" target="_blank">w' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '</a></td><td>' .$kk[0].'</td><td>' .$kk[1].'</td><td>Mindent: <br><a href="http://localhost:8111/load_object?new_layer=false&objects=w' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '&addtags=name=' .$kk[0].'%7Calt_name=' .$kk[1].'%7Cdenomination=' .$kk[2].'%7Curl:miserend=' .$kk[3].'%7Creligion=christian%7Camenity=place_of_worship" target="_blank">w' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '</a> <br>Vagy csak:<br><a href="http://localhost:8111/load_object?new_layer=false&objects=w' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '&addtags=name=' .$kk[0].'" target="_blank">' .$kk[0].'</a><hr><a href="http://localhost:8111/load_object?new_layer=false&objects=w' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '&addtags=alt_name=' .$kk[1].'" target="_blank">' .$kk[1].'</a><hr><a href="http://localhost:8111/load_object?new_layer=false&objects=w' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '&addtags=denomination=' .$kk[2].'" target="_blank">' .$kk[2].'</a><hr><a href="http://localhost:8111/load_object?new_layer=false&objects=w' . sprintf("%u", (float)$row['osm_id'] & 0xffffffff). '&addtags=url:miserend=' .$kk[3].'" target="_blank">' .$kk[3].'</a></td></tr>';


} else {
$jl = '<tr><td>'.$row['name'].''.$al.''.$mr.'</td><td>' . $row['denomination'].'</td><td><a href="http://localhost:8111/load_object?new_layer=false&relation_members=true&objects=r'.$usz.'" target="_blank">r' .$usz. '</a><td>' .$kk[0].'</td><td>' .$kk[1].'</td><td>Mindent: <br><a href="http://localhost:8111/load_object?new_layer=false&relation_members=true&objects=r' .$usz. '&addtags=name=' .$kk[0].'%7Calt_name=' .$kk[1].'%7Cdenomination=' .$kk[2].'%7Curl:miserend=' .$kk[3].'%7Creligion=christian%7Camenity=place_of_worship" target="_blank">r' .$usz. '</a> <br>Vagy csak:<br><a href="http://localhost:8111/load_object?new_layer=false&relation_members=true&objects=r' .$usz. '&addtags=name=' .$kk[0].'" target="_blank">' .$kk[0].'</a><hr><a href="http://localhost:8111/load_object?new_layer=false&relation_members=true&objects=r' .$usz. '&addtags=alt_name=' .$kk[1].'" target="_blank">' .$kk[1].'</a><hr><a href="http://localhost:8111/load_object?new_layer=false&relation_members=true&objects=r' .$usz. '&addtags=denomination=' .$kk[2].'" target="_blank">' .$kk[2].'</a><hr><a href="http://localhost:8111/load_object?new_layer=false&relation_members=true&objects=r' .$usz. '&addtags=url:miserend=' .$kk[3].'" target="_blank">' .$kk[3].'</a></td></tr>';

}

echo $jl;

}
}else {echo '<tr><td>-</td><td>-</td><td>-</td><td>' .$kk[0].'</td><td>' .$kk[1].'</td><td>-</td><td><a href="http://localhost:8111/add_node?lon='. $kk[4] .'&lat='. $kk[5] .'&addtags=name=' .$kk[0].'%7Calt_name=' .$kk[1].'%7Cdenomination=' .$kk[2].'%7Curl:miserend=' .$kk[3].'%7Creligion=christian%7Camenity=place_of_worship" target="_blank">hozzáad</a></td></tr>';}

}else {echo '<tr><td>-</td><td>-</td><td>-</td><td>' .$kk[0].'</td><td>' .$kk[1].'</td><td>-</td><td>Nincs koordináta <a href="' .$kk[3].'" target="_blank">' .$kk[3].'</a></td></tr>';}



}

echo '
</tbody></table>
</body>
</html>';
}else { echo 'Így nem megy...<br>Így működik:http://data2.openstreetmap.hu/mrjosm.php?miserend=100 <br>A szám 1-101 közt lehet.';}

?>
