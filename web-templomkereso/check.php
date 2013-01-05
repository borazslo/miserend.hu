<?
include_once('db.php');

function percent_update() {
	$templom = db_query("SELECT * FROM templom");
	setvar('templom_max',count($templom));
	
	$checked = db_query("SELECT schecked FROM templom t, geocode_suggestion gs WHERE gs.tid = t.tid AND schecked > 0 ");
	setvar('templom_checked',count($checked));
	
	$checked = db_query("SELECT schecked FROM templom t, geocode_suggestion gs WHERE gs.tid = t.tid");
	setvar('templom_suggested',count($checked));
	}

	
function percent_html() {
	percent_update(); 
	
	$max = getvar('templom_max');
	$checked = getvar('templom_checked');
	$suggested = getvar('templom_suggested');
	
	$c = number_format($checked*97/$max);
	$uc = number_format(($suggested-$checked)*97/$max);
	$u =   number_format(($max-$suggested)*97/$max);
	$r = "<div id=\"percent\">".
		  "<div id=\"percent_u\"  style=\"width:".($u)."%\"><span>"; if($u > 4) { $r .= ($max-$suggested);} $r .= "</span></div>".
		  "<div id=\"percent_uc\"  style=\"width:".($uc)."%\"><span>"; if($uc > 4) { $r .= ($suggested-$checked);} $r .= "</span></div>".
		  "<div id=\"percent_c\" style=\"width:".($c)."%\"><span>"; if($c > 4) { $r .= $checked;} $r .= "</span></div>";
		  
	$r .= "</div>";
  return $r;
}

function welcome_html($login='',$user=array()) {
  $r ="<h2>Keressük a templomok helyét!</h2>
		<p>Ragadj meg egy templomot, vonszold a helyére, és legyél te a legpontosabb templomtologató!</p>";

  if($login) $r .="<p align='center'><a href='".$login."'><img class=\"welcome\" src='http://eleklaszlo.hu/teszt/icons/fb-lepj-be.png'></a></p>";
  elseif($user) $r .= "<center><h1>Hajrá ".$user['name']."!</h1></center>";
  else  $r .= "<center><h1>Akkor hát, hajrá!</h1></center>";
  $r .="<p><img class=\"welcome\" src='http://eleklaszlo.hu/teszt/icons/church-red.png'>Még senki sem próbálta pontosítani a helyét.</p>
		<p><img class=\"welcome\" src='http://eleklaszlo.hu/teszt/icons/church-yellow.png'>Valakinek már volt javaslata.</p>
		<p><img class=\"welcome\" src='http://eleklaszlo.hu/teszt/icons/church-green.png'>Már megtaláltuk közösen a helyét.</p>
		<p>Ha egyszer beléptél, akkor már küldhetsz be javaslatokat, amikért - ha elfogadjuk azokat - pontokat és jutalmakat kaphaszt.</p>";
   
  

  return $r;
}


?>