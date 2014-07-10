<?php
echo "<br/>";
$handle =  file_get_contents("http://miserend.hu/?templom=".$_REQUEST['templom'], "r"); 

preg_match('/<table([^>]*)id="szentmisek">(.*?)<\/table>/si',$handle,$match);

$doboz = $match[0]; //iconv('iso-8859-2','utf-8',$match[0]);
$doboz = preg_replace('/src=("|)img\//','src=$1http://miserend.hu/img/',$doboz);
echo $doboz;
//echo $handle;

?>