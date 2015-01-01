<?php


function chat_html() {
	global $user;
	//$urlap.="<input type=hidden name=kinek value='$kinek'>";
	//if(!empty($kinek)) $urlap.="<a href=chatadd.php?u_login=$loginkiir1&kinek= class=link title='visszavon'><img src=img/lakat.gif align=absmiddle border=0><i> $kinek</i></a><br>";
	

	
	$vars['comments'] = chat_getcomments();
	$vars['lastcomment'] = $vars['comments'][0]['datum_raw'];
	
	//$html.="<iframe name=chat width=100% height=500 frameborder=0 src=chat.php?u_login=$loginkiir$linkveg></iframe>";

	$vars['script'] .= <<<EOD
	var focus = true;
	var loadmore = false;
	var title = document.title;

 	$(document).ready(function() {
 		

		$(window).focus(function(){
			focus = true;
			$("#chat_comments").data('unread',0);
			setTimeout(function(){
			  document.title = title;
			}, 500);

		});
		$(window).blur(function(){
			focus = false;
		});



 		$('#chat_text').each(function(){
    		this.contentEditable = true;
		});

 		$('#chat_text').keydown(function(event) {
    		if (event.keyCode == 13) {
 				if (!event.shiftKey) chat_send();
     		}
     	});
 		$("body").on('click', '#chat_submit', function() {
			chat_send();
 		});

		$("body").on('click', '.response_closed', function() {
			if($("#chat_text").html().match(/^(<i>.*?<\/i>)$/ig) ) { $("#chat_text").html(''); }


			if($("#chat_text").text().match(/^([$]{1})/ig)) {}
			else {
				//$("#chat_text").prepend("<img class='lakat link' title='Válasz csak neki' src=img/lakat.gif align=absmiddle height='13' border=0 style='margin-right:3px'>" + $(this).data('to') + ": " );
				$("#chat_text").prepend("<span class='lakat link' title='Zárt válasz csak neki'>$</span>" + $(this).data('to') + ": " );
			}
 		});
		$("body").on('click', '.response_open', function() {
			if($("#chat_text").html().match(/^(<i>.*?<\/i>)$/ig) ) { $("#chat_text").html(''); }
			$("#chat_text").append("<span class='lakat link' title='Nyilvános válasz neki'>@</span>" + $(this).data('to') + " " );
 		});

		$("body").on('click', '#chat_loadnext', function() {
			loadmore = true;
			$.post( "ajax.php", { q: "ChatLoad", date: $( this ).prev().data("date"), rev: true }, function( data ) {
	  			if(data.result === "loaded") {  
	  				var index;
					for (index = 0; index < data.comments.length; ++index) {
					 	$('#chat_loadnext').before(data.comments[index].html);
					}
	  			}
	  			else {
					$("#chat_text").html('<i>Hiba történt betöltés közben.</i>');
	  				return false;
	  			} 
		},'json');
 		});

    });

	var c = 1;
	var lim = 10;
	setInterval(function(){
		chat_update('update');
		if(c === lim) {
			c = 1;
			chat_users();
		} else c++;
	},5000);

	
	function chat_send() {
		if($("#chat_text").text() != "") {

			var string = $("#chat_text").html();
			string = $('<div/>').html(string.replace(/(<br>)/ig,"\\n"));
			string = $('<div/>').html(string.html().replace(/^(<img.*?>)/ig,"$"));
	 		 		
	 		var text = $(string).text();
	 		$("#chat_text").html('<i>Küldés folyamatban...</i>');
	 		$.post( "ajax.php", { q: "ChatSave", text: text }, function( data ) {
	  			if(data.result === "saved") {
					$("#chat_text").html('<i>Frissítés folyamatban...</i>');
	  				chat_update('clear');
	  			} else {
	  				if(data.text != "") {
						$("#chat_text").html('<i>' + data.text + '</i>');
	  				} else {
						$("#chat_text").html('<i>Nem sikerült menteni. Elnézést.</i>');
					}
	  			}
			}, "json");
		}
	}

	function chat_update(clear) {
		$.post( "ajax.php", { q: "ChatLoad", date: $("#chat_comments").data("last") }, function( data ) {
	  			if(data.result === "loaded") {  
	  				var index;
					for (index = 0; index < data.comments.length; ++index) {
					    $(data.comments[index].html).hide().prependTo('#chat_comments').show('slow');
						
					}

					if(data.new > 0) {
						$("#chat_comments").data('last',data.comments[data.comments.length-1].datum_raw);
						if(loadmore != true ) $("#chat_comments div").slice(10,-1).each( function() { $(this).hide('slow',function() { $( this ).remove();});});
					}

					if(focus == false) {
						var unread = $("#chat_comments").data('unread') + data.new;
						$("#chat_comments").data('unread',unread);
					
						if (unread > 0) {
				      		setTimeout(function(){
				        		document.title = '(' + unread + ') ' + title;
				      		}, 500);
				    	}
				    } else 	$("#chat_comments").data('unread',0);

				    if(clear === 'clear') {
				    	$("#chat_text").html('');
				    }
	  				return true;
	  			}
	  			else {
					$("#chat_text").html('<i>Hiba történt a frissítés közben.</i>');
	  				return false;
	  			} 
		},'json');
	}

	function chat_users() {
		$.post( "ajax.php", { q: "ChatUsers" }, function( data ) {
	  			if(data.result === "loaded") { 
	  				console.log(data);
	  				$("#chat_users").html(data.text);
	  			}
	  	},'json');
	}
EOD;
	$vars['users'] = chat_getusers('html');
	global $twig;
    return $twig->render('chat.html',$vars);
}

function chat_getcomments($args = array()) {
	global $user;
	$limit = 10;

	$return = array();

	$loginkiir1 = urlencode($user->login);

	$query="select id,datum,user,kinek,szoveg from chat where (kinek='' or kinek='".$user->login."' or user='".$user->login."') ";
	if(isset($args['last'])) $query .= " AND datum > '".$args['last']."' ";
	if(isset($args['first'])) $query .= " AND datum < '".$args['first']."' ";

	$query .= " order by datum desc limit 0,".$limit;
	$lekerdez=mysql_query($query);
	while($row=mysql_fetch_array($lekerdez,MYSQL_ASSOC)) {
		$row['datum_raw'] = $row['datum'];
		if(date('Y',strtotime($row['datum'])) < date('Y')) $row['datum'] = date('Y.m.d.',strtotime($row['datum']));
		elseif(date('m',strtotime($row['datum'])) < date('m')) $row['datum'] = date('m.d.',strtotime($row['datum']));
		elseif(date('d',strtotime($row['datum'])) < date('d')) $row['datum'] = date('m.d. H:i',strtotime($row['datum']));
		else $row['datum'] = date('H:i',strtotime($row['datum']));

		if($row['user'] == $user->login) $row['color'] ='#394873';
		elseif($row['kinek'] == $user->login) $row['color'] ='red';
		elseif(preg_match('/@'.$user->login.'([^a-zA-Z]{1}|$)/i',$row['szoveg'])) $row['color']='red';

		if($row['kinek'] != '') {
			if($row['kinek']==$user->login) $loginkiir2=urlencode($user->login);
			else $loginkiir2=urlencode($row['kinek']);
			
			$row['jelzes'] = "<span class='response_closed link' title='Válasz csak neki' data-to='".$row['kinek']."' ><img src=img/lakat.gif align=absmiddle height='13' border=0><i> ".$row['kinek']."</i></span>: ";
			//$row['jelzes'] .= "<a class='response_open link' title='Nyilvános válasz / említés' data-to='".$row['kinek']."'><i> ".$row['kinek']."</i></a>: ";
		}


		$row['szoveg'] = preg_replace('/@(\w+)/i','<span class="response_open" data-to="$1" style="background-color: rgba(0,0,0,0.15);">$1</span>',$row['szoveg']);
		
		
		$return[] = $row;
	}

	return $return;
}

function chat_getusers($format = false) {
	global $user;
	$return = array();
	$query="select login from user where jogok!='' and lastactive >= '".date('Y-m-d H:i:s',strtotime("-5 minutes"))."' and login <> '".$user->login."' order by lastactive desc";
	if(!$lekerdez=mysql_query($query)) $online.="HIBA<br>$query<br>".mysql_error();
	if(mysql_num_rows($lekerdez)>0) {
		while(list($loginnev)=mysql_fetch_row($lekerdez)) {
			$return[] = $loginnev;
		}
	}
	if($format == 'html') {
		foreach($return as $k=>$i) $return[$k] = '<span class="response_closed" data-to="'.$i.'" style="background-color: rgba(0,0,0,0.15);">'.$i.'</span>';
    	$text = '<strong>Online adminok:</strong> '.implode(', ', $return);
    	if(count($return)==0) $text = '<strong><i>Nincs (más) admin online.</i></strong>';
    	$return = $text;
	}
	return $return;
}

function chat_balmenu() {
    global $user;

	$loginkiir=urlencode($u_login);
   
	//Tartalom létrehozása
	$kod_cim="<span class=admincimlink>Admin üzenőfal</span>";

	$kod_tartalom = chat_html();
    if($user->jogok == '') { $kod_tartalom = 'Hiányzó jogosultság.'; }

	
	$kodT[0]=$kod_cim;
	$kodT[1]=$kod_tartalom;

    return $kodT;

}

?>