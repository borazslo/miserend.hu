<?


function design(&$vars) {
    global $design_url,$db_name,$tartalom,$m_oldalsablon,$balkeret,$jobbkeret,$onload,$sid,$linkveg,$loginhiba,$script,$meta,$titlekieg;

    global $twig,$user;

    if(!is_array($meta)) $vars['meta'][] = $meta;
    else $vars['meta'] = $meta;

    if(!is_array($script)) $vars['script'][] = $script;
    else $vars['script'] = $script;

    $vars['script'][] = '<link href="css/jquery-ui.icon-font.css" rel="stylesheet" type="text/css" />';
    $vars['script'][] = '<script src="/bower_components/jquery/dist/jquery.min.js"></script>';
    $vars['script'][] = '<script src="/bower_components/jquery-ui/jquery-ui.js"></script>';
    $vars['script'][] = '<script src="/bower_components/jquery-ui/ui/autocomplete.js"></script>';
    $vars['script'][] = '<script src="/bower_components/jquery-colorbox/jquery.colorbox.js"></script>';
    $vars['script'][] = '<script src="/bower_components/jquery-colorbox/i18n/jquery.colorbox-hu.js"></script>';
    $vars['script'][] = '<script src="js/als/jquery.als-1.5.min.js"></script>';

    
    $vars['script'][] = '<link rel="stylesheet" href="templates/colorbox.css" />';
    $vars['script'][] = '<link rel="stylesheet" href="templates/als.css" />';

    $vars['script'][] = '<link rel="stylesheet" href="/bower_components/jquery-ui/themes/smoothness/jquery-ui.css">';

    $vars['script'][] = '<script src="js/miserend.js"></script>';
    $vars['script'][] = '<script src="js/somethingidontknow.js"></script>';
    $vars['script'][] = '<script language="JavaScript" type="text/javascript">
        var trackOutboundLink = function(url) {
        ga(\'send\', \'event\', \'outbound\', \'click\', url, {\'hitCallback\':
            function () {
            document.location = url;
            }
        });
        }
        </script>';

    
    $vars['pagetitle'] = 'VPP - miserend';
    if(isset($titlekieg)) $vars['pagetitle'] = preg_replace("/^( - )/i","",$titlekieg)." | ".$vars['pagetitle'];
    	

	if(!$user->loggedin) {
		if(empty($vars['body']['onload'])) $vars['body']['onload']='onload="fokusz();"';
		else $vars['body']['onload']="onload=\"".$vars['body']['onload']." fokusz();\"";
	}
	elseif(!empty($vars['body']['onload'])) {
		$vars['body']['onload']="onload=\"".$vars['body']['onload'].";\"";
	}

	
	$emaillink_lablec="<A HREF=\"javascript:linkTo_UnCryptMailto('ocknvq%3CkphqBokugtgpf0jw');\" class=emllink>info<img src=img/kukaclent.gif align=absmiddle border=0>miserend.hu</a>";
	

//Impresszum link
	$impkiir='Impresszum';
	$impfm='impfm';
	$impresszumlink="<a href=?m_id=17&fm=12 class=implink>$impkiir</a>";
    $vars['bottom']['left']['content'] = $impresszumlink."<br/>".$emaillink_lablec;
    
	$vars['bottom']['right']['content'] = "<a href=http://www.b-gs.hu class=implink title='BGS artPart' target=_blank>design</a><br><a href=http://www.florka.hu class=implink title='Florka Kft.' target=_blank>programozás</a>";

    
//Loginűrlap
	if($belepve) {
        $vars['login']['loggedin'] = true;
		//Ha bent van
        $vars['login']['vars'] = array('linkveg' => $linkveg, 'design_url' => $design_url,'u_login'=>$user->login);
    }
	else {
		//Belépés
        $vars['login']['vars'] = array('linkveg' => $linkveg, 'design_url' => $design_url,'login'=>$_POST['login'],'loginhiba'=>$loginhiba,'sid'=>$sid);       
	}



	//BLOCK - BLOCKS
	$vars['sidebar']['right']['blocks'] = array();
	$vars['sidebar']['left']['blocks'] = array();

	//BLOCK - ADMINMENU
	if(preg_replace('/-/i','',$user->jogok) != '' ) {
		$adminmenuitems = array(
				array(
					'title'=> 'Miserend','url'=> '?m_id=27','permission' => 'miserend','mid'=>27,
					'items' => array(
						array('title' => 'új templom','url' => '?m_id=27&m_op=addtemplom','permission' => '' ),
						array('title' => 'módosítás','url' => '?m_id=27&m_op=modtemplom','permission' => '' ),				
						array('title' => 'egyházmegyei lista','url' => '?m_id=27&m_op=ehmlista','permission' => 'miserend' ),
						array('title' => 'kifejezések és dátumok','url' => '?m_id=27&m_op=events','permission' => 'miserend' ),				
					)
				),
				array(
					'title'=> 'Igenaptár','url'=> '?m_id=31','permission' => 'igenaptar','mid'=>31,
					'items' => array(
						array('title' => 'naptár beállítása','url' => '?m_id=31&m_op=naptar','permission' => 'igenaptar' ),
						array('title' => 'gondolatok','url' => '?m_id=31&m_op=gondolatok','permission' => 'igenaptar' ),
						array('title' => 'szentek','url' => '?m_id=31&m_op=szentek','permission' => 'igenaptar' ),
					)
				),
				array(
					'title'=> 'Felhasználók','url'=> '?m_id=21','permission' => 'user','mid'=>21,
					'items' => array(
						array('title' => 'új felhasználó','url' => '?m_id=21&m_op=add','permission' => '' ),
						array('title' => 'módosítás','url' => '?m_id=21&m_op=mod','permission' => '' ),
					)
				),
			);
		$adminmenu = array(
			'content' => menuHtml($adminmenuitems), 
			'bgcolor'=>'#ECE5C8',
			'header'=>array(
				'content'=>'Adminisztráció',
				'bgcolor'=>'#F5CC4C',
				'class' => 'dobozcim_fekete',
			),
			'op'=>$helyzet,
		);
		$vars['sidebar']['left']['blocks'][] = $adminmenu;
	}

	//BLOCK - CHAT
	if(preg_replace('/-/i','',$user->jogok) != '' ) {
		$chat = array(
			'content' => chat_html(),
			'bgcolor'=>'#ECE5C8',
			'header'=>array(
				'content'=>'Admin üzenőfal',
				'bgcolor'=>'#F5CC4C',
				'class' => 'dobozcim_fekete',
			),
			'op'=>$helyzet,
		);
		$vars['sidebar']['left']['blocks'][] = $chat;		
    }

	//BLOCK - USER'S CHURCHES
 	if($user->loggedin) {
		$feltoltes = array(
			'content' => feltoltes_block(),
			'bgcolor'=>'#F4F2F5',
			'header'=>array(
				'content'=>'Módosítás',
				'bgcolor' => '#8D317C',
				'class' => 'dobozcim_feher',
				),
			'op'=>$helyzet,
		);
		$vars['sidebar']['left']['blocks'][] = $feltoltes;
 	}

    if($user->checkRole('miserend'))
    	$vars['user']['isadmin'] = true;
    
    //Főhasáb összeállítása

    if(is_array($tartalom)) return $twig->render($tartalom['template'].".twig",array_merge($vars,$tartalom));
    else  $vars['content'] = $tartalom;


    $sablon = "page";
    if($m_oldalsablon == 'aloldal') $sablon .= '_subadminpage';
    elseif(!empty($m_oldalsablon) AND $m_oldalsablon != 'alap') $sablon .= '_'.$m_oldalsablon;

    return $twig->render($sablon.'.html',$vars);


	
}



?>
