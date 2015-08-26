<?php

function design(&$vars) {
    global $design_url,$db_name,$tartalom,$m_oldalsablon,$script,$meta,$titlekieg,$m_id;

    global $twig,$user;

    if(!is_array($meta)) $vars['meta'][] = $meta;
    else $vars['meta'] = $meta;

    if(!is_array($script)) $vars['script'][] = $script;
    else $vars['script'] = $script;

    
   /*
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
    */
    $vars['pagetitle'] = 'VPP - miserend';
    if(isset($titlekieg)) $vars['pagetitle'] = preg_replace("/^( - )/i","",$titlekieg)." | ".$vars['pagetitle'];
    	

	$emaillink_lablec="<A HREF=\"javascript:linkTo_UnCryptMailto('ocknvq%3CkphqBokugtgpf0jw');\" class=emllink>info<img src=img/kukaclent.gif align=absmiddle border=0>miserend.hu</a>";
	

	$vars['user'] = $user;
	//BLOCK - BLOCKS
	$vars['sidebar']['right']['blocks'] = array();
	$vars['sidebar']['left']['blocks'] = array();

	//BLOCK - ADMINMENU
	if($user->checkRole("'any'")) {
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
						array('title' => 'új felhasználó','url' => '?m_id=28&m_op=edit','permission' => 'user' ),
						array('title' => 'módosítás','url' => '?m_id=28&m_op=list','permission' => 'user' ),
					)
				),
			);

		$vars['adminmenu'] = clearMenu($adminmenuitems);
	
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
    	$vars['user']->isadmin = true;
    
    
    //Főhasáb összeállítása


    $vars['messages'] = getMessages();

    if(is_array($tartalom)) {
    	if(!isset($tartalom['template'])) {
    			$template = "page.html";
    	} else 
    		$template = $tartalom['template'].".twig";
    	return $twig->render($template,array_merge($vars,$tartalom));
    }
    else  $vars['content'] = $tartalom;

    //TODO: ez most még kell a page.html vs layout.twig miatt.
   if(!isset($_SESSION['template']) OR $_SESSION['template'] == 'templates2' AND $m_id == 28)
			return $twig->render('layout.twig',$vars);

    $sablon = "page";
    if($m_oldalsablon == 'aloldal') $sablon .= '_subadminpage';
    elseif(!empty($m_oldalsablon) AND $m_oldalsablon != 'alap') $sablon .= '_'.$m_oldalsablon;

    return $twig->render($sablon.'.html',$vars);


	
}



?>
