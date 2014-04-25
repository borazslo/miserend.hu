<?php

function hirek_index() {
	global $linkveg,$m_id,$db_name;

	$kod=hirek_adminmenu();

	return $kod;
}

function hirek_adminmenu() {
	global $m_id,$linkveg,$u_beosztas;

	$menu.='<span class=alcim>Hírek szerkesztése</span><br><br>';
	$menu.="<a href=../?m_id=$m_id$linkveg class=menulink><font color=red>Új szerkesztõablakos módosítás</font></a><br><br>";
	$menu.="<a href=?m_id=$m_id&m_op=add$linkveg class=kismenulink>Új hír feltöltése</a><br>";
	$menu.="<a href=?m_id=$m_id&m_op=mod$linkveg class=kismenulink>Meglévõ hír módosítása, törlése</a><br>";
	if($u_beosztas=='fsz' or $u_beosztas=='sz') {
		$menu.="<a href=?m_id=$m_id&m_op=mod&ok=n$linkveg class=kismenulink>Feltöltött hírek engedélyezése</a><br>";
	}
	if($u_beosztas=='fsz') {
		$menu.="<a href=?m_id=$m_id&m_op=rovat$linkveg class=kismenulink>Rovatok (fõmenü) szerkesztése</a><br>";
		$menu.="<a href=?m_id=$m_id&m_op=fokat$linkveg class=kismenulink>Fõkategóriák szerkesztése</a><br>";
		$menu.="<a href=?m_id=$m_id&m_op=kat$linkveg class=kismenulink>Kategóriák szerkesztése</a><br>";
		$menu.="<a href=?m_id=$m_id&m_op=alkat$linkveg class=kismenulink>Alkategóriák szerkesztése</a><br>";
	}

	$adatT[2]=$menu;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	

	return $tartalom;
}

function hirek_add($hid) {
	global $sessid,$linkveg,$m_id,$db_name,$onload,$u_beosztas;

	if($hid>0) {
		$onload=" onload=\"idEdit.document.designMode='On';\"";
		$urlap.=include('editscript.php');

		$query="select cim,intro,szoveg,kerdes,datum,aktualis,szamlalo,rovat,fokat,kat,alkat,kulcsszavak,galeria,kiemelt,kiemeles,nyelv,ok,megjelenhet,hirlevel,log from hirek where id='$hid'";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();	list($cim,$intro,$szoveg,$kerdes,$datum,$aktualis,$szamlalo,$rovat,$fokat,$kat,$alkat,$kulcsszavak,$galeria,$kiemelt,$kiemeles,$nyelv,$ok,$megjelenhet,$hirlevel,$log)=mysql_fetch_row($lekerdez);
		$datum=substr($datum,0,16);
		if($u_beosztas=='hb' and $ok=='i') {
			$hid=0;
			$cim='';
			$intro='';
			$szoveg='';
			$kerdes='';
			$datum=date('Y-m-d H:i');
			$aktualis='';
			$nyelv='';
		}
	}
	else {
		$datum=date('Y-m-d H:i');
		$aktualis='';
	}

	$urlap.="\n<FORM ENCTYPE='multipart/form-data' name='frmSaveContents' onsubmit=\"document.frmSaveContents.strHtml.value=document.frames('idEdit').document.body.innerHTML;\" method=post>";

	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sessid value=$sessid>";
	$urlap.="\n<input type=hidden name=m_op value=adding><input type=hidden name=hid value=$hid>";
	
	$urlap.='<table cellpadding=4>';

//elõnézet
	if($hid>0) $urlap.="\n<tr><td bgcolor='#efefef'>&nbsp;</td><td bgcolor='#efefef'><a href=?m_id=19&id=$hid$linkveg class=link><b>>> Hír megtekintése (elõnézet) <<</b></a></td></tr>";

//cím
	$urlap.="\n<tr><td><div class=kiscim align=right>Hír címe:</div></td><td><input type=text name=cim value=\"$cim\" class=urlap size=80 maxlength=250><span class=alap> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Nyelve:</span><select name=nyelv class=urlap>";
	$lekerdez=mysql_db_query($db_name,"select kod,nevhu from nyelvek");
	while(list($nyelvkod,$nyelvnev)=mysql_fetch_row($lekerdez)) {
		$urlap.="<option value=$nyelvkod";
		if($nyelvkod==$nyelv) $urlap.= ' selected';
		$urlap.=">$nyelvnev</option>";
	}
	$urlap.='</select></td></tr>';

//dátum
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Dátum, idõ:</div></td><td bgcolor=#efefef><input type=text name=datum value=\"$datum\" class=urlap size=16 maxlength=16></td></tr>";

//hírlevélben
	if($u_beosztas=='fsz' or $u_beosztas=='sz') {
		//Csak szerkesztõktõl láthatják
		$urlap.="\n<tr><td><div class=kiscim align=right>Hírlevélben:</div></td><td><input type=radio name=hirlevel value=0 ";
		if($hirlevel=='c')  $urlap.=' checked';
		$urlap.="><span class=alap>nincs</span> <input type=radio name=hirlevel value=c ";
		if(empty($hirlevel) or $hirlevel=='c') $urlap.=' checked';
		$urlap.="><span class=alap>csak cím</span> <input type=radio name=hirlevel value=i ";
		if($hirlevel=='i') $urlap.=' checked';
		$urlap.="><span class=alap>cím és bevezetõ</span> <input type=radio name=hirlevel value=t ";
		if($hirlevel=='t') $urlap.=' checked';
		$urlap.="><span class=alap>teljes szöveg</span></td></tr>";
	}

//aktuális
	$urlap.="\n<tr><td bgcolor=#ECE5C8><div class=kiscim align=right>Aktuális:</div></td><td bgcolor=#ECE5C8><input type=text name=aktualis value=\"$aktualis\" class=urlap size=60 maxlength=255><span class=alap> (Ha az aktualitás ki van töltve, megjelenik <br>a naptárban! Több idõpont is felvihetõ + jellel elválasztva! Pl.: 2005-05-05+2005-05-16)</span></td></tr>";

//intro	
	$urlap.="\n<tr><td><div class=kiscim align=right>Rövid bevezetõ:</div></td><td><textarea name=intro class=urlap cols=75 rows=10>$intro</textarea></td></tr>";

//kérdés	
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Kérdés (tudta-e):</div></td><td bgcolor=#efefef><input type=text name=kerdes value=\"$kerdes\" class=urlap size=80 maxlength=250></td></tr>";

//Kategóriák
	if($u_beosztas=='fsz' or $u_beosztas=='sz') {
		$urlap.="\n<tr><td><div align=right><span class=kiscim>Kategórizálás:</span><br><span class=alap>(k1, k2, k3, normál)</span></div></td><td><table width=100% cellspacing=0 cellpadding=2><tr>";
		//rovat
		$urlap.="\n<td width=25% valign=top><span class=kiscim>Rovat</span><span class=alap> (k1,k2,k3,n)</span><br><table cellpadding=0 cellspacing=0>";
		$query="select id,nev from rovatok where ok='i' order by sorszam";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		while(list($rid,$rnev)=mysql_fetch_row($lekerdez)) {
			$a++;
			if($a%2==0) $bg='bgcolor=#efefef';
			else $bg='';
			$urlap.="\n<tr><td align=right $bg><span class=kicsi>$rnev </span></td><td $bg>";
			$urlap.="<input type=checkbox name=k1[] value=rr$rid";
			if(strstr($kiemeles,"k1*rr$rid")) $urlap.=' checked';
			$urlap.="><input type=checkbox name=k2[] value=rr$rid";
			if(strstr($kiemeles,"k2*rr$rid")) $urlap.=' checked';
			$urlap.="><input type=checkbox name=k3[] value=rr$rid";
			if(strstr($kiemeles,"k3*rr$rid")) $urlap.=' checked';
			$urlap.=">";
			$urlap.="<input type=checkbox name=rovat[] value=$rid";
			if(strstr($rovat,"-$rid-")) $urlap.=' checked';
			$urlap.="></td></tr>";	
		}
		$urlap.='</table></td>';
	
		//fõkat
		$urlap.="\n<td width=25% valign=top><span class=kiscim>Fõkategória</span><br><table cellpadding=0 cellspacing=0>";
		$query="select id,nev from fokat where ok='i' order by sorszam";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		while(list($fkid,$fknev)=mysql_fetch_row($lekerdez)) {
			if($a%2==0) $bg='bgcolor=#efefef';
			else $bg='';
			$urlap.="<tr><td align=right $bg><span class=kicsi>$fknev </span></td><td $bg>";	
			$urlap.="<input type=checkbox name=k1[] value=fk$fkid";
			if(strstr($kiemeles,"k1*fk$fkid")) $urlap.=' checked';
			$urlap.="><input type=checkbox name=k2[] value=fk$fkid";
			if(strstr($kiemeles,"k2*fk$fkid")) $urlap.=' checked';
			$urlap.="><input type=checkbox name=k3[] value=fk$fkid";
			if(strstr($kiemeles,"k3*fk$fkid")) $urlap.=' checked';
			$urlap.="><input type=checkbox name=fokat[] value=$fkid";
			if(strstr($fokat,"-$fkid-")) $urlap.=' checked';
			$urlap.="></td></tr>";
			$a++;
		}
		$urlap.='</table></td>';

		//kat
		$urlap.="\n<td width=25% valign=top><span class=kiscim>Kategória</span><br><table cellpadding=0 cellspacing=0>";
		$query="select id,nev from kat where ok='i' order by sorszam";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		while(list($akid,$aknev)=mysql_fetch_row($lekerdez)) {
			$a++;
			if($a%2==0) $bg='bgcolor=#efefef';
			else $bg='';
			$urlap.="<tr><td align=right $bg><span class=kicsi>$aknev </span></td><td $bg>";
			$urlap.="<input type=checkbox name=k1[] value=kk$akid";
			if(strstr($kiemeles,"k1*kk$akid")) $urlap.=' checked';
			$urlap.="><input type=checkbox name=k2[] value=kk$akid";
			if(strstr($kiemeles,"k2*kk$akid")) $urlap.=' checked';
			$urlap.="><input type=checkbox name=k3[] value=kk$akid";
			if(strstr($kiemeles,"k3*kk$akid")) $urlap.=' checked';
			$urlap.="><input type=checkbox name=kat[] value=$akid";
			if(strstr($kat,"-$akid-")) $urlap.=' checked';
			$urlap.="></td></tr>";
		}
		$urlap.='</table></td>';

		//alkat
		$urlap.="\n<td width=25% valign=top><span class=kiscim>Alkategória</span><br><table cellpadding=0 cellspacing=0>";
		$query="select id,nev from alkat where ok='i' order by sorszam";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		while(list($akid,$aknev)=mysql_fetch_row($lekerdez)) {
			if($a%2==0) $bg='bgcolor=#efefef';
			else $bg='';
			$a++;
			$urlap.="<tr><td align=right $bg><span class=kicsi>$aknev </span></td><td $bg>";
			$urlap.="<input type=checkbox name=k1[] value=ak$akid";
			if(strstr($kiemeles,"k1*ak$akid")) $urlap.=' checked';
			$urlap.="><input type=checkbox name=k2[] value=ak$akid";
			if(strstr($kiemeles,"k2*ak$akid")) $urlap.=' checked';
			$urlap.="><input type=checkbox name=k3[] value=ak$akid";
			if(strstr($kiemeles,"k3*ak$akid")) $urlap.=' checked';
			$urlap.="><input type=checkbox name=alkat[] value=$akid";
			if(strstr($alkat,"-$akid-")) $urlap.=' checked';
			$urlap.="></td></tr>";	
		}
		$urlap.='</table></td>';

		$urlap.='</tr></table></td></tr>';

		$urlap.="\n<tr><td colspan=2 bgcolor=#efefef height=2><img src=img/space.gif width=5 height=2></td></tr>";
	}

//Kulcsszó	
	if($u_beosztas=='fsz' or $u_beosztas=='sz') {
		$urlap.="\n<tr><td><div class=kiscim align=right>Kulcsszó<br>(kapcsolódó hírek):</div></td><td><span class=alap>új: </span><input type=text name=ujkulcsszo class=urlap size=40 maxlength=40><br><select name=kulcsszavak[] class=urlap multiple size=8><option value='0'";
		$urlap.=">Nincs</option>";

		$query="select distinct(kulcsszavak) from hirek where kulcsszavak!=''";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		while(list($h_kulcsszo)=mysql_fetch_row($lekerdez)) {
			$kT=explode('!*!',$h_kulcsszo);
			if(is_array($kT)) {
				foreach($kT as $ertek) {
					$kulcs_T[]=$ertek;
				}
			}
		}	
		if(is_array($kulcs_T)) {
			$kulcs_T=array_unique($kulcs_T);
			natcasesort($kulcs_T); //Sorbarendezés
			$mitT=array('á','Á','é','É','í','Í','ó','Ó','ö','Õ','ü','Ü','û','Û','ú','Ú');
			$mireT=array('a','A','e','E','i','I','o','O','o','O','u','U','u','U','u','U');
			$kulcs1_T=str_replace($mitT,$mireT,$kulcs_T); //ékezetes betûk kiszûrése
			natcasesort($kulcs1_T); //újabb sorbarendezés

			foreach($kulcs1_T as $ix=>$ertek) { //új sorrend szerint, de a régi (ékezetes) nevek listázása
				$urlap.="<option value='$ertek'";
				if(strstr($kulcsszavak,$kulcs_T[$ix])) $urlap.=' selected';
				$urlap.=">$kulcs_T[$ix]</option>";
			}
		}
		$urlap.='</select></td></tr>';
	}

//Kapcsolódó galéria
	if($u_beosztas=='fsz' or $u_beosztas=='sz') {
		$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Kapcsolódó galéria:</div></td><td bgcolor=#efefef><select name=galeria[] class=urlap multiple><option value=0";
		if(empty($galeria)) $urlap.=' selected';
		$urlap.=">Nincs</option>";
		$query="select id,cim,datum from galeria where ok='i' order by cim";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		while(list($gid,$gcim,$gdatum)=mysql_fetch_row($lekerdez)) {
			$urlap.="<option value='$gid'";
			if(strstr($galeria,$gid)) $urlap.=' selected';
			$urlap.=">$gcim ($gdatum)</option>";
		}
		$urlap.='</select></td></tr>';
	}

//Megjelenhet (jogosultság!)
	if($u_beosztas=='fsz' or $u_beosztas=='sz') {
		$urlap.="\n<tr><td><div class=kiscim align=right>Megjelenhet:</div></td><td>";
		$query="select kod,nev from enghirkat";
		$lekerdez=mysql_db_query($db_name,$query);
		while(list($e_kod,$e_nev)=mysql_fetch_row($lekerdez)) {
			$urlap.="\n<input type=checkbox name=megjelenhetT[] value='$e_kod' class=urlap";
			if(strstr($megjelenhet,"$e_kod") or empty($hid)) $urlap.=' checked';
			$urlap.="><span class=urlap>$e_nev</span> ";
		}
		$urlap.="</td></tr>";
	}


//Fõkép
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Fõoldali kép:</div></td><td bgcolor=#efefef><input type=file name=fokep class=urlap size=50>";
	if($hid>0) {
		if(is_file("kepek/hirek/$hid/fokep/n.jpg")) {
			$urlap.="<br><img src=kepek/hirek/$hid/fokep/n.jpg><input type=checkbox name=delfokep value=i class=urlap><span class=urlap>töröl</span>";
		}
	}
	$urlap.='</td></tr>';

//További képek
	$urlap.="\n<tr><td valign=top><div class=kiscim align=right>Képfeltöltés:</div></td><td valign=top><span class=alap>(A kép bekerül a szövegmezõbe, ahol mozgatható. CSAK <b>jpg</b> fájl!)</span>";
	$urlap.="\n<br><input type=file size=60 name=kep class=urlap>";

	$urlap.="\n<br><br><span class=kiscim>Képfelirat:</span>";
	$urlap.="\n<br><input type=text name=alt size=30 class=urlap>";

	$urlap.="\n<br><br><span class=kiscim>Kicsinyítés:</span>";
	$urlap.="\n<br><input type=text name=kicsinyit value=120 size=3 class=urlap>";

	$urlap.="\n<br><br><span class=alap><b>Kép igazítása</b> (szöveg-körbefuttatással)</span>";
	$urlap.="<br><select name=align class=urlap><option value='0'>kép külön</option><option value=left>balra</option><option value=right>jobbra</option></select>";

	$urlap.='</td></tr>';

//Fájlok
	$urlap.="\n<tr><td bgcolor=#efefef valign=top><div class=kiscim align=right>Letölthetõ fájl(ok):</td><td valign=top bgcolor='#efefef'>";
	$urlap.="\n<span class=alap>Kapcsolódó dokumentum, ha van ilyen:</span><br>";
	$urlap.="\n<span class=alap>Új fájl: </span><input type=file size=60 name=fajl class=urlap><br>";
	//Könyvtár tartalmát beolvassa
	if($hid>0) {
		$konyvtar="fajlok/hirek/$hid";
		if(is_dir($konyvtar)) {
			$handle=opendir($konyvtar);
			while ($file = readdir($handle)) {
				if ($file!='.' and $file!='..') {
					$meret=intval((filesize("$konyvtar/$file")/1024));
					if($meret>1000) {
						$meret=intval(($meret/1024)*10)/10;
						$meret.=' MB';
					}
					else $meret.=' kB';
					$filekiir=rawurlencode($file);
					$urlap.="<br><li><a href='$konyvtar/$filekiir' class=alap><b>$file</b></a><span class=alap> ($meret) </span><input type=checkbox class=urlap name=delfajl[] value='$file'><span class=alap>Töröl</span></li>";
				}
			}
			closedir($handle);
		}
	}


//Szöveg
if($hid>0) {
	//Szerkesztõablak CSAK akkor, ha már fel van töltve a hír!
$urlap.="<tr><td valign=top><div class=kiscim align=right>Hír részletes szövege:</div></td><td valign=top><DIV class=toolbar id=idBox style=\"WIDTH: 100%; TEXT-ALIGN: left\">
  <TABLE id=tb1 style=\"MARGIN-BOTTOM: 2pt; PADDING-TOP: 1pt\" cellSpacing=2 
  cellPadding=0>
    <TBODY>
    <TR>
      <TD vAlign=center noWrap><SELECT class=urlap
        onchange=\"format('formatBlock',this[this.selectedIndex].value);this.selectedIndex=0\"> 
          <OPTION class=heading selected>Paragraph<OPTION value=\"<P>\">Normal 
          &lt;P&gt;<OPTION value=\"<H1>\">Heading 1 &lt;H1&gt;<OPTION 
          value=\"<H2>\">Heading 2 &lt;H2&gt;<OPTION value=\"<H3>\">Heading 3 
          &lt;H3&gt;<OPTION value=\"<H4>\">Heading 4 &lt;H4&gt;<OPTION 
          value=\"<H5>\">Heading 5 &lt;H5&gt;<OPTION value=\"<H6>\">Heading 6 
          &lt;H6&gt;<OPTION value=\"<PRE>\">Pre &lt;PRE&gt;<OPTION 
          style=\"COLOR: darkred\" value=removeFormat>Clear 
        Formatting</OPTION></SELECT> &nbsp;&nbsp;&nbsp; <SELECT class=urlap
        onchange=\"format('fontname',this[this.selectedIndex].value);this.selectedIndex=0\"> 
          <OPTION class=heading selected>Font<OPTION 
          value=Geneva,Arial,Sans-Serif>Arial<OPTION 
          value=Verdana,Geneva,Arial,Helvetica,Sans-Serif>Verd<OPTION 
          value=\"Times New Roman,Times,Serif\">Time<OPTION 
          value=\"Courier, Monospace\">Cour</OPTION></SELECT> <SELECT class=urlap
        onchange=\"format('fontSize',this[this.selectedIndex].text);this.selectedIndex=0\"> 
          <OPTION class=heading 
          selected>Size<OPTION>1<OPTION>2<OPTION>3<OPTION>4<OPTION>5<OPTION>6<OPTION>7</OPTION></SELECT><SELECT class=urlap
        onchange=\"format('forecolor',this[this.selectedIndex].style.color);this.selectedIndex=0\"> 
          <OPTION class=heading selected>Color<OPTION>White<OPTION 
          style=\"COLOR: black\">Black<OPTION style=\"COLOR: gray\">Gray<OPTION 
          style=\"COLOR: darkred\">Dark Red<OPTION style=\"COLOR: navy\">Navy<OPTION 
          style=\"COLOR: darkgreen\">Dark Green</OPTION></SELECT> 
  </TD></TR></TBODY></TABLE></DIV>";
	$urlap.="<DIV class=toolbar onselectstart=\"return false\" id=tb2 ondragstart=\"return false\" SSTYLE=\"width: 270\">
		  <A onmouseover=\"MM_swapImage('open3','','img/buttons/cutup.gif',1)\" onmouseout=MM_swapImgRestore() href=\"#\"><IMG
		onclick=\"format('cut')\" alt=kivág src=\"img/buttons/cut.gif\" border=0 name=open3></A><A
		onmouseover=\"MM_swapImage('open2','','img/buttons/copyup.gif',1)\" onmouseout=MM_swapImgRestore() href=\"#\"><IMG
		onclick=\"format('copy')\" alt=másol src=\"img/buttons/copy.gif\" border=0 name=open2></A><A
		onmouseover=\"MM_swapImage('paste.gif','','img/buttons/pasteup.gif',1)\" onmouseout=MM_swapImgRestore() href=\"#\"><IMG
		onclick=\"format('paste')\" alt=beilleszt src=\"img/buttons/paste.gif\" border=0 name=paste.gif></A><A
		onmouseover=\"MM_swapImage('asdf','','img/buttons/deleteup.gif',1)\" onmouseout=MM_swapImgRestore() href=\"#\"><IMG
		onclick=\"format('delete')\" alt=töröl src=\"img/buttons/delete.gif\" border=0 name=asdf></A><A
		onmouseover=\"MM_swapImage('aa','','img/buttons/undoup.gif',1)\" onmouseout=MM_swapImgRestore() href=\"#\"><IMG
		onclick=\"format('undo')\" alt=visszavon src=\"img/buttons/undo.gif\" border=0 name=aa></A></DIV>";

	$urlap.='<SCRIPT>
		var buttons=new Array(24,23,23,4,23,23,23,4,23,23,23,23,4,23,23),action=new Array("bold","italic","underline","","justifyleft","justifycenter","justifyright","","insertorderedlist","insertunorderedlist","outdent","indent","","createLink","saveEntry"),tooltip=new Array("Félkövér","Dõlt","Aláhúzott","","Balra igazít","Középre igazít","Jobbra igazít","","Számozott felsorolás","Sima felsorolás","Behúzás vissza","Behúzás","","WEB hivatkozás",""),left=0,s=""
		for (var i=0;i<buttons.length;i++) {';
	$urlap.="s+=\"<SPAN STYLE='position:relative;height:26;width: \" + buttons[i] + \"'><SPAN STYLE='position:absolute;margin:0px;padding:0;height:26;top:0;left:0;width:\" + (buttons[i]) + \";clip:rect(0 \"+buttons[i]+\" 25 \"+0+\");overflow:hidden'><IMG BORDER=0 SRC='img/buttons/toolbar.gif' STYLE='position:absolute;top:0;left:-\" + left + \"' WIDTH=290 HEIGHT=50\"
			if (buttons[i]!=4) {
				s+=\" onmouseover='this.style.top=-25' onmouseout='this.style.top=0'";
	$urlap.=' ONCLICK=\""';
	$urlap.="\n	if (action[i]!=\"createLink\")
					s+=\"format('\" + action[i] + \"');"; 
	$urlap.='this.style.top=0\" "
				else
					s+="createLink();this.style.top=0\" "
				s+="TITLE=\"" + tooltip[i] + "\""
			}
			s+="></SPAN></SPAN>"
			left+=buttons[i]
		}
		document.write(s)
		</SCRIPT>';

	$urlap.="<DIV class=clsExample><INPUT type=hidden name=strHtml></DIV>";
	$urlap.="<DIV class=clsExample><IFRAME name=idEdit src='get.php?id=$hid&kod=hirek' width=580 height=400></IFRAME>";

	$urlap.="<DIV class=mode id=tb3><NOBR> <INPUT id=mW onclick=setMode(true) type=radio CHECKED name=rMode><LABEL class=current
		id=modeA for=mw><span class=alap>Megjelenítési nézet</span></LABEL> <INPUT id=mH onclick=setMode(false) type=radio name=rMode><LABEL id=modeB for=mH><span class=alap>HTML nézet</span></LABEL></NOBR>";
	}
	else {
		//Az elsõ feltöltésnél csak textarea!
		$urlap.="<tr><td valign=top><div class=kiscim align=right>Hír részletes szövege:</div></td><td valign=top><span class=alap>Elsõ feltöltéskor az alábbi szövegdobozba írd be a hírt, a módosítások során <br>már lehetõség van formázásra, képek beszúrására is.</span><br><textarea name=szoveg class=urlap cols=100 rows=30></textarea><input type=hidden name=szovegtipus value=normal>";
	}

	$urlap.="\n</td></tr>";
	
//Log
	if(!empty($log) and ($u_beosztas=='fsz' or $u_beosztas=='sz')) {
		$urlap.="\n<tr><td valign=top><div class=kiscim align=right>történet:</div></td><td valign=top><textarea cols=70 rows=6 disabled>Számláló: $szamlalo\n$log</textarea></td></tr>";
	}

	$urlap.='</table>';

	$urlap.="\n<br><input type=submit value=Mehet class=urlap></form>";

	$adatT[2]='<span class=alcim>Hír feltöltése</span><br><br>'.$urlap;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_adding() {
	global $_POST,$db_name,$_FILES,$u_login,$u_beosztas;

	$hiba=false;
	$hid=$_POST['hid'];
	$cim=$_POST['cim'];
	$fokatT=$_POST['fokat'];
	if(is_array($fokatT)) $fokat='-'.implode('--',$fokatT).'-';
	$alkatT=$_POST['alkat'];
	if(is_array($alkatT)) $alkat='-'.implode('--',$alkatT).'-';
	$katT=$_POST['kat'];
	if(is_array($katT)) $kat='-'.implode('--',$katT).'-';
	$rovatT=$_POST['rovat'];
	if(is_array($rovatT)) $rovat='-'.implode('--',$rovatT).'-';
	
	$k1T=$_POST['k1'];
	if(is_array($k1T)) $kiemeltT[]='-k1*'.implode('--k1*',$k1T).'-';
	$k2T=$_POST['k2'];
	if(is_array($k2T)) $kiemeltT[]='-k2*'.implode('--k2*',$k2T).'-';
	$k3T=$_POST['k3'];
	if(is_array($k3T)) $kiemeltT[]='-k3*'.implode('--k3*',$k3T).'-';

	if(is_array($kiemeltT)) $kiemelt=implode('',$kiemeltT);

	$intro=$_POST['intro'];
	$datum=$_POST['datum'];
	$aktualis=$_POST['aktualis'];
	$kerdes=$_POST['kerdes'];
	$megjelenhetT=$_POST['megjelenhetT'];
	$hirlevel=$_POST['hirlevel'];
	if(is_array($megjelenhetT)) $megjelenhet=implode('-',$megjelenhetT);
	if(!empty($megjelenhet)) $ok=" ok='i',"; //Ha valahova engedélyezték, akkor már CSAK szerkesztõ nyúlhat hozzá
	if(($u_beosztas=='fsz' or $u_beosztas=='sz')) $ok=" ok='i',";

	//$kiemeltT=$_POST['kiemelt'];
	//if(is_array($kiemeltT)) $kiemelt=implode('',$kiemeltT);

	$szoveg=$_POST['szoveg'];
	$szovegtipus=$_POST['szovegtipus'];
	if($szovegtipus=='normal') $szoveg=nl2br($szoveg);
	$nyelv=$_POST['nyelv'];
	$kulcsszavakT=$_POST['kulcsszavak'];
	$ujkulcsszo=$_POST['ujkulcsszo'];
	if(is_array($kulcsszavakT)) $kulcsszavak=implode('!*!',$kulcsszavakT);
	if($kulcsszavakT[0]=='0') $kulcsszavak='';
	if(!empty($kulcsszavak) and !empty($ujkulcsszo)) $kulcsszavak.='!*!';
	if(!empty($ujkulcsszo)) $kulcsszavak.=$ujkulcsszo;
	$galeriaT=$_POST['galeria'];
	if(is_array($galeriaT)) $galeria='-'.implode('--',$galeriaT).'-';
	$alt=$_POST['alt'];

	$strHtml=$_POST['strHtml'];
	$strHtml=stripslashes($strHtml);
	$strHtml=ereg_replace("%3C","<",$strHtml);
	$strHtml=ereg_replace("%3E",">",$strHtml);
	$strHtml=ereg_replace("%2F","/",$strHtml);
	$strHtml=ereg_replace("'","\'",$strHtml);
	if(empty($szoveg)) $szoveg=$strHtml;

	$fokep=$_FILES['fokep']['tmp_name'];
	$kep=$_FILES['kep']['tmp_name'];
	$kepnev=$_FILES['kep']['name'];
	$kicsinyit=$_POST['kicsinyit'];
	if(empty($kicsinyit)) $kicsinyit=120;
	$align=$_POST['align'];
	if($align!='0') $align="align=$align";

	$fajl=$_FILES['fajl']['tmp_name'];
	$fajlnev=$_FILES['fajl']['name'];
	$delfajl=$_POST['delfajl'];

	if(is_array($delfajl)) {
		foreach($delfajl as $ertek) {
			unlink("fajlok/hirek/$hid/$ertek");
		}
	}

	$delfokep=$_POST['delfokep'];
	if($delfokep=='i') {
		$konyvtar="kepek/hirek/$hid/fokep";
		@unlink("$konyvtar/k1.jpg");
		@unlink("$konyvtar/k2.jpg");
		@unlink("$konyvtar/k3.jpg");
		@unlink("$konyvtar/n.jpg");
		@unlink("$konyvtar/kep.jpg");
	}

	if(empty($cim)) {
		$hiba=true;
		$hibauzenet.='<br>Nem lett kitöltve a cím mezõ!';
	}
	if(empty($intro) and empty($szoveg)) {
		$hiba=true;
		$hibauzenet.='<br>Nem lett kitöltve sem bevezetõ, sem szövegmezõ!';
	}

	if($hiba) {
		$txt.="<span class=hiba>HIBA a hírek feltöltésénél!</span><br>";
		$txt.='<span class=alap>'.$hibauzenet.'</span>';
		$txt.="<br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";
	
		$adatT[2]='<span class=alcim>Hírek admin</span><br><br>'.$txt;
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);	
	}
	else {
		$most=date('Y-m-d H:i:s');
		if($hid>0) {
			$uj=false;
			$parameter1='update';
			list($log)=mysql_fetch_row(mysql_db_query($db_name,"select log from hirek where id='$hid'"));
			$ujlog=$log."\nMod: $u_login ($most)";
			$parameter2=", modositotta='$u_login', moddatum='$most', log='$ujlog' where id='$hid'";
		}
		else {
			$uj=true;
			$parameter1='insert';
			$parameter2=", feltette='$u_login', fdatum='$most', log='Add: $u_login ($most)'";
		}

		$query="$parameter1 hirek set cim='$cim', intro='$intro', szoveg='$szoveg', kerdes='$kerdes', datum='$datum', aktualis='$aktualis', rovat='$rovat', fokat='$fokat', kat='$kat', alkat='$alkat', kulcsszavak='$kulcsszavak', galeria='$galeria', kiemeles='$kiemelt', nyelv='$nyelv', $ok megjelenhet='$megjelenhet', hirlevel='$hirlevel' $parameter2";
		if(!mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		if($uj) $hid=mysql_insert_id();

		if(!empty($fajl)) {
		$konyvtar="fajlok/hirek";
		//Könyvtár ellenõrzése
		if(!is_dir("$konyvtar/$hid")) {
			//létre kell hozni
			if(!mkdir("$konyvtar/$hid",0775)) {
				echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
			}
		}
		//Másolás
		if(!copy($fajl,"$konyvtar/$hid/$fajlnev")) echo '<p>HIBA a másolásnál!</p>';
		unlink($fajl);
	}


		if(!empty($fokep)) {
			$konyvtar="kepek/hirek";
			//Könyvtár ellenõrzése
			if(!is_dir("$konyvtar/$hid")) {
				//létre kell hozni
				if(!mkdir("$konyvtar/$hid",0775)) {
					echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
				}
				if(!mkdir("$konyvtar/$hid/fokep",0775)) {
					echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
				}
				if(!mkdir("$konyvtar/$hid/kicsi",0775)) {
					echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
				}
			}

			$kimenetT[0]="$konyvtar/$hid/fokep/kep.jpg"; $maxT[0]=600;
			$kimenetT[1]="$konyvtar/$hid/fokep/k1.jpg"; $maxT[1]=200; //kurír 1
			$kimenetT[2]="$konyvtar/$hid/fokep/k2.jpg"; $maxT[2]=140; //kurír 2
			$kimenetT[3]="$konyvtar/$hid/fokep/k3.jpg"; $maxT[3]=120; //kurír 3
			$kimenetT[4]="$konyvtar/$hid/fokep/n.jpg";  $maxT[4]=90;  //normál

			if ( !copy($fokep, "$kimenetT[0]") )
				print("HIBA a másolásnál ($kimenet)!<br>\n");

			$info=getimagesize($kimenetT[0]);
			$w=$info[0];
			$h=$info[1];

			foreach($kimenetT as $x=>$y) {
				if($w>$maxT[$x] or $h>$maxT[$x]) {
				    kicsinyites($fokep,$y,$maxT[$x]);		
				}
				else  {
				    if(!copy($fokep, $y)) echo 'HIBA a masolasnal';
				}
			}
			unlink($fokep);
		}

		if(!empty($kep)) {
			$konyvtar="kepek/hirek/$hid";
			//Könyvtár ellenõrzése
			if(!is_dir("$konyvtar")) {
				//létre kell hozni
				if(!mkdir("$konyvtar",0775)) {
					echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
				}
				if(!mkdir("$konyvtar/fokep",0775)) {
					echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
				}
				if(!mkdir("$konyvtar/kicsi",0775)) {
					echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
				}
			}

			$kimenet="$konyvtar/$kepnev";
			$kimenet1="$konyvtar/kicsi/$kepnev";

			if ( !copy($kep, "$kimenet") )
				print("HIBA a másolásnál ($kimenet)!<br>\n");
			
			unlink($kep);

			$info=getimagesize($kimenet);
			$w=$info[0];
			$h=$info[1];
      
			if($w>800 or $h>600) kicsinyites($kimenet,$kimenet,600);
	  		kicsinyites($kimenet,$kimenet1,$kicsinyit);

			$info=getimagesize($kimenet);
			$w=$info[0];
			if($w>800) $w=820;
			else $w=$w+20;
			$h=$info[1];
			if($h>600) $h=600;
			$info=getimagesize($kimenet1);
			$whinfo=$info[2];
			$kepkod="<a href=\'javascript:OpenNewWindow(\"view.php?kep=$kimenet\",$w,$h)\';><img src=\"$kimenet1\" $whinfo border=0 alt=\'$alt\' $align vspace=10 hspace=10></a>";

			$szoveg=$kepkod.$szoveg;
		
			$query="update hirek set szoveg='$szoveg' where id='$hid'";
			if(!mysql_db_query($db_name,$query)) echo '<p class=hiba>HIBA!<br>'.mysql_error();
		}

		$kod=hirek_add($hid);
	}

	return $kod;
}

function hirek_mod() {
	global $db_name,$linkveg,$m_id,$_POST,$u_beosztas,$u_login;

	$hirkat=$_POST['hirkat'];
	if(empty($hirkat)) $hirkat='mind';
	$kulcsszo=$_POST['kulcsszo'];	

	$ok=$_POST['ok'];
	if(!isset($ok)) $ok=$_GET['ok'];

	$min=$_POST['min'];
	if(!isset($min)) $min=$_GET['min'];
	if($min<0 or !isset($min)) $min=0;

	$leptet=$_POST['leptet'];
	if(!isset($leptet)) $leptet=$_GET['leptet'];
	if(!isset($leptet)) $leptet=30;

	$next=$min+$leptet;
	$prev=$min-$leptet;
	
	$most=date('Y-m-d H:i:s');


	$kiir.="<span class=kiscim>A lista szûkíthetõ rovatok szerint, illetve kulcsszó alapján:</span><br>";

	$kiir.="\n<form method=post><input type=hidden name=m_id value='$m_id'><input type=hidden name=m_op value=mod>";
	if(($u_beosztas=='fsz' or $u_beosztas=='sz') and $ok!='n') {
		$kiir.="\n<select name=hirkat class=urlap><option value=mind>Mind</option>";
		$query_kat="select id,nev from rovatok where ok='i' order by sorszam";
		$lekerdez_kat=mysql_db_query($db_name,$query_kat);
		while(list($kid,$knev)=mysql_fetch_row($lekerdez_kat)) {
			$kiir.="<option value=$kid";
			if($kid==$hirkat) $kiir.=" selected";
			$kiir.=">$knev</option>";
		}
		$kiir.="</select>";
	}
	if(isset($ok)) $kiir.="<input type=hidden name=ok value=$ok>";
	$kiir.=" <input type=text name=kulcsszo value='$kulcsszo' class=urlap size=20> <input type=submit value=Lista class=urlap></form><br>";

	if($hirkat!='mind' and isset($hirkat)) $feltetelT[]="rovat like '%-$hirkat-%'";
	if(!empty($kulcsszo)) $feltetelT[]="(cim like '%$kulcsszo%' or intro like '%$kulcsszo%' or szoveg like '%$kulcsszo%')";
	if($u_beosztas=='hb') {
		$feltetelT[]="(feltette='$u_login' and ok='n')";
	}
	else {
		if($ok=='n') $feltetelT[]="ok='n'";
		elseif($ok=='i') $feltetelT[]="ok='i'";
	}

	if(is_array($feltetelT)) $feltetel=' where '.implode(' and ',$feltetelT);

	$query="select id,cim,datum from hirek $feltetel order by datum desc";
	$lekerdez=mysql_db_query($db_name,$query);
	$mennyi=mysql_num_rows($lekerdez);
	if($mennyi>$leptet) {
		$query.=" limit $min,$leptet";
		$lekerdez=mysql_db_query($db_name,$query);
	}
	$kezd=$min+1;
	$veg=$min+$leptet;
	if($veg>$mennyi) $veg=$mennyi;
	if($mennyi>0) $kiir.="<span class=alap>Összesen: $mennyi találat<br>Listázás: $kezd - $veg</span><br><br>";
	else $kiir.="<span class=alap>Jelenleg nincs módosítható hír az adatbázisban.</span>";
	while(list($mid,$cim,$datum)=mysql_fetch_row($lekerdez)) {
		if($datum<$most and !$vonal) {
		    $kiir.='<hr>';
		    $vonal=true;
		}
		$datum=substr($datum,0,16);
		$kiir.="\n<a href=?m_id=$m_id&m_op=add&hid=$mid$linkveg class=link><b>- $cim</b>($datum)</a> - <a href=?m_id=$m_id&m_op=del&hid=$mid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";
	}

	$kiir.='<br>';
	if($min>0) {
		$kiir.="\n<form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=$m_op><input type=hidden name=sessid value=$sessid><input type=hidden name=kulcsszo value='$kulcsszo'><input type=hidden name=hirkat value=$hirkat><input type=hidden name=min value=$prev>";
		if(isset($ok)) $kiir.="<input type=hidden name=ok value=$ok>";
		$kiir.="\n<input type=submit value=Elõzõ class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
	}
	if($mennyi>$min+$leptet) {
		$kiir.="\n<form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=$m_op><input type=hidden name=sessid value=$sessid><input type=hidden name=kulcsszo value='$kulcsszo'><input type=hidden name=hirkat value=$hirkat><input type=hidden name=min value=$next>";
		if(isset($ok)) $kiir.="<input type=hidden name=ok value=$ok>";
		$kiir.="\n<input type=submit value=Következõ class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
	}

	$adatT[2]='<span class=alcim>Hírek módosítása</span><br><br>'.$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;


	return $kod;
}



function hirek_del() {
	global $_GET,$db_name,$linkveg,$m_id,$u_login,$u_beosztas;

	$hid=$_GET['hid'];

	$kiir="<span class=alcim>Hírek törlése</span><br><br>";
	$kiir.="\n<span class=kiscim>Biztosan törölni akarod a következõ hírt?</span>";
		
	$query="select cim from hirek where id='$hid'";
	if($u_beosztas=='hb')
		$query.=" and feltette='$u_login' and ok='n'";
	list($cim)=mysql_fetch_row(mysql_db_query($db_name,$query));

	if(!empty($cim)) {
		$kiir.="\n<br><br><span class=alap>$cim</span>";

		$kiir.="<br><br><a href=?m_id=$m_id&m_op=delete&hid=$hid$linkveg class=link>Igen</a> - <a href=?m_id=$m_id&m_op=mod$linkveg class=link>NEM</a>";
	}
	else {
		$kiir.="<br><br><span class=hiba>HIBA! Ilyen hír nincs!</span>";
	}

	$adatT[2]=$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_delete() {
	global $_GET,$db_name,$u_login,$u_beosztas;

	$id=$_GET['hid'];
	$query="delete from hirek where id='$id'";
	if($u_beosztas=='hb')
		$query.=" and feltette='$u_login' and ok='n'";
	mysql_db_query($db_name,$query);

	//Fájlokat és képeket is törölni kell!

	//Könyvtár tartalmát beolvassa
	$konyvtar="fajlok/hirek/$id";
		if(is_dir($konyvtar)) {
			$handle=opendir($konyvtar);
			while ($file = readdir($handle)) {
				if ($file!='.' and $file!='..') {
					@unlink("$konyvtar/$file");
				}
			}
			closedir($handle);
		}
	

	//Könyvtár tartalmát beolvassa
	$konyvtar="kepek/hirek/$id";
		if(is_dir($konyvtar)) {
			$handle=opendir($konyvtar);
			while ($file = readdir($handle)) {
				if ($file!='.' and $file!='..' and $file!='fokep' and $file!='kicsi') {
					unlink("$konyvtar/$file");
				}
			}
			closedir($handle);
		}
	$konyvtar="kepek/hirek/$id/kicsi";
		if(is_dir($konyvtar)) {
			$handle=opendir($konyvtar);
			while ($file = readdir($handle)) {
				if ($file!='.' and $file!='..') {
					unlink("$konyvtar/$file");
				}
			}
			closedir($handle);
		}

	@unlink("kepek/hirek/$id/fokep/kep.jpg");
	@unlink("kepek/hirek/$id/fokep/k1.jpg");
	@unlink("kepek/hirek/$id/fokep/k2.jpg");
	@unlink("kepek/hirek/$id/fokep/k3.jpg");
	@unlink("kepek/hirek/$id/fokep/n.jpg");

	$kod=hirek_mod();

	return $kod;
}

function hirek_rovat() {
	global $db_name,$linkveg,$m_id,$_POST;

	$kiir.="<a href=?m_id=$m_id&m_op=rovatadd$linkveg class=link><b>Új rovat hozzáadása</b></a><br><br>";

	$query="select id,nev,menuben,ok from rovatok order by menuben,sorszam";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($rid,$rnev,$rmenuben,$rok)=mysql_fetch_row($lekerdez)) {
		if($rok!='i') $OK="<span class=alap><font color=red>(várakozó)</font></span>";
		else $OK="<span class=alap><font color=green>(aktív)</font></span>";
		if($rmenuben=='b') $menu='Bal hasáb';
		elseif($rmenuben=='j') $menu='Jobb hasáb';
		elseif($rmenuben=='f') $menu='Felsõ menüsor';
		else $menu='Nem jelenik meg';
		if($ell!=$rmenuben) $kiir.="<br><span class=alap>-----------_<b>$menu</b>_----------</span><br>";
		$kiir.="\n<a href=?m_id=$m_id&m_op=rovatadd&rid=$rid$linkveg class=link><b>- $rnev</b></a> $OK - <a href=?m_id=$m_id&m_op=rovatdel&rid=$rid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";		
		$ell=$rmenuben;
	}

	$adatT[2]='<span class=alcim>Rovatok szerkesztése</span><br><br>'.$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;


	return $kod;
}

function hirek_rovatadd($rid) {
	global $sessid,$linkveg,$m_id,$db_name;

	if($rid>0) {
		$query="select nev,sorszam,menuben,friss,k3,lista,listao,lang,ok from rovatok where id='$rid'";	list($nev,$sorszam,$menuben,$friss,$k3,$lista,$listao,$nyelv,$ok)=mysql_fetch_row(mysql_db_query($db_name,$query));
	}

	$urlap="\n<form method=post>";

	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sessid value=$sessid>";
	$urlap.="\n<input type=hidden name=m_op value=rovatadding><input type=hidden name=rid value=$rid>";
	
	$urlap.='<table>';
//Cím
	$urlap.="\n<tr><td><div class=kiscim align=right>Rovat címe:</div></td><td><input type=text name=nev value=\"$nev\" class=urlap size=50 maxlength=50></td></tr>";
//Nyelv
	$urlap.="\n<tr><td><div class=kiscim align=right>Nyelve:</div></td><td><select name=nyelv class=urlap>";
	$lekerdez=mysql_db_query($db_name,"select kod,nevhu from nyelvek");
	while(list($nyelvkod,$nyelvnev)=mysql_fetch_row($lekerdez)) {
		$urlap.="<option value=$nyelvkod";
		if($nyelvkod==$nyelv) $urlap.= ' selected';
		$urlap.=">$nyelvnev</option>";
	}
	$urlap.='</select></td></tr>';

	$urlap.="\n<tr><td><div class=kiscim align=right>Sorszám (sorrend):</div></td><td><input type=text name=sorszam value=\"$sorszam\" class=urlap size=2 maxlength=2></td></tr>";

//Menüben	
	$urlap.="\n<tr><td><div class=kiscim align=right>Megjelenés:</div></td><td><select name=menuben class=urlap>";
	$urlap.='<option value=b';
	if($menuben=='b') $urlap.=' selected';
	$urlap.='>balmenuben</option><option value=f';
	if($menuben=='f') $urlap.=' selected';
	$urlap.='>felsõ menüben</option><option value=j';
	if($menuben=='j' or !isset($menuben)) $urlap.=' selected';
	$urlap.='>jobb menüben</option><option value=0';
	if($menuben=='0') $urlap.=' selected';
	$urlap.='>sehol, csak hírkategória</option></select>';
	$urlap.="</td></tr>";

//Listabal
	$urlap.="\n<tr><td><div class=kiscim align=right>Listázandó hírek száma oldalt:</div></td><td><input type=text name=listao value=\"$listao\" class=urlap size=2 maxlength=2></td></tr>";
//Lista
	$urlap.="\n<tr><td><div class=kiscim align=right>Listázandó hírek száma középen:</div></td><td><input type=text name=lista value=\"$lista\" class=urlap size=2 maxlength=2></td></tr>";

//címek
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right><i>Friss hírek:</i></div></td><td bgcolor=#efefef><input type=text name=friss value=\"$friss\" class=urlap size=40 maxlength=50></td></tr>";
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right><i>További fontos:</i></div></td><td bgcolor=#efefef><input type=text name=k3 value=\"$k3\" class=urlap size=40 maxlength=50></td></tr>";

//Engedélyezés (jogosultság!)
	$urlap.="\n<tr><td><div class=kiscim align=right>Engedélyezés:</div></td><td><input type=checkbox name=ok value='i' class=urlap";
	if($ok!='n') $urlap.=' checked';
	$urlap.="></td></tr>";

	$urlap.='</table>';

	$urlap.="\n<br><input type=submit value=Mehet class=urlap></form>";

	$adatT[2]='<span class=alcim>Rovat szerkesztése</span><br><br>'.$urlap;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_rovatadding() {
	global $_POST,$db_name;

	$hiba=false;
	$rid=$_POST['rid'];
	$nev=$_POST['nev'];
	$menuben=$_POST['menuben'];
	$friss=$_POST['friss'];
	$k3=$_POST['k3'];
	$lista=$_POST['lista'];
	$listao=$_POST['listao'];
	$nyelv=$_POST['nyelv'];
	$ok=$_POST['ok'];
	if($ok!='i') $ok='n';
	$sorszam=$_POST['sorszam'];

	if(empty($nev)) {
		$hiba=true;
		$hibauzenet.='<br>Nem lett kitöltve a cím mezõ!';
	}

	if($hiba) {
		$txt.="<span class=hiba>HIBA a rovat szerkesztésénél!</span><br>";
		$txt.='<span class=alap>'.$hibauzenet.'</span>';
		$txt.="<br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";
	
		$adatT[2]='<span class=alcim>Rovatok szerkesztése</span><br><br>'.$txt;
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);	
	}
	else {
		if($rid>0) {
			$uj=false;
			$parameter1='update';
			$parameter2="where id='$rid'";
		}
		else {
			$uj=true;
			$parameter1='insert';
			$parameter2="";
		}

		$query="$parameter1 rovatok set nev='$nev', sorszam='$sorszam', menuben='$menuben', friss='$friss', k3='$k3', lista='$lista', listao='$listao', lang='$nyelv', ok='$ok' $parameter2";
		mysql_db_query($db_name,$query);
		if($uj) $rid=mysql_insert_id();

		$kod=hirek_rovatadd($rid);
	}

	return $kod;
}

function hirek_rovatdel() {
	global $_GET,$db_name,$linkveg,$m_id;

	$rid=$_GET['rid'];

	$kiir="<span class=alcim>Rovat törlése</span><br><br>";
	$kiir.="\n<span class=kiscim>Biztosan törölni akarod a következõ rovatot?</span>";
		
	$query="select nev from rovatok where id='$rid'";
	list($nev)=mysql_fetch_row(mysql_db_query($db_name,$query));

	$kiir.="\n<br><br><span class=alap>$nev</span>";

	$kiir.="<br><br><a href=?m_id=$m_id&m_op=rovatdelete&rid=$rid$linkveg class=link>Igen</a> - <a href=?m_id=$m_id&m_op=rovat$linkveg class=link>NEM</a>";

	$adatT[2]=$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_rovatdelete() {
	global $_GET,$db_name;

	$id=$_GET['rid'];
	$query="delete from rovatok where id='$id'";
	mysql_db_query($db_name,$query);

	$kod=hirek_rovat();

	return $kod;
}

function hirek_fokat() {
	global $db_name,$linkveg,$m_id,$_POST;

	$rovat=$_POST['rovat'];

	$urlap.="\n<form method=post><input type=hidden name=m_id value='$m_id'><input type=hidden name=m_op value=fokat>";
	$urlap.="<span class=kiscim>Listázás rovatok szerint:</span><br>";
	$urlap.="\n<select name=rovat class=urlap><option value=mind>Mind</option>";
	$query_kat="select id,nev from rovatok where ok='i' order by sorszam";
	$lekerdez_kat=mysql_db_query($db_name,$query_kat);
	while(list($kid,$knev)=mysql_fetch_row($lekerdez_kat)) {
		$urlap.="<option value=$kid";
		if($kid==$rovat) $urlap.=" selected";
		$urlap.=">$knev</option>";
	}
	$urlap.="</select><input type=submit value=Lista class=urlap></form><br>";

	if($rovat!='mind' and isset($rovat)) $feltetel="where rovat='$rovat'";

	$kiir.="<a href=?m_id=$m_id&m_op=fokatadd$linkveg class=link><b>Új fõkategória hozzáadása</b></a><br><br>";

	$kiir.=$urlap;

	$query="select id,nev,ok from fokat $feltetel order by sorszam";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($fkid,$fknev,$fkok)=mysql_fetch_row($lekerdez)) {
		if($fkok!='i') $OK="<span class=alap><font color=red>(várakozó)</font></span>";
		else $OK="<span class=alap><font color=green>(aktív)</font></span>";
		$kiir.="\n<a href=?m_id=$m_id&m_op=fokatadd&fkid=$fkid$linkveg class=link><b>- $fknev</b></a> $OK - <a href=?m_id=$m_id&m_op=fokatdel&fkid=$fkid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";
	}

	$adatT[2]='<span class=alcim>Fõkategóriák szerkesztése</span><br><br>'.$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;


	return $kod;
}

function hirek_fokatadd($fkid) {
	global $sessid,$linkveg,$m_id,$db_name;

	if($fkid>0) {
		$query="select nev,rovat,sorszam,menuben,lang,ok,lista,friss,k3 from fokat where id='$fkid'";	list($nev,$rovat,$sorszam,$menuben,$nyelv,$ok,$lista,$friss,$k3)=mysql_fetch_row(mysql_db_query($db_name,$query));
	}

	$urlap="\n<form method=post>";

	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sessid value=$sessid>";
	$urlap.="\n<input type=hidden name=m_op value=fokatadding><input type=hidden name=fkid value=$fkid>";
	
	$urlap.='<table>';

	$urlap.="\n<tr><td><div class=kiscim align=right>Fõkategória címe:</div></td><td><input type=text name=nev value=\"$nev\" class=urlap size=50 maxlength=50></td></tr>";
//Nyelv
	$urlap.="\n<tr><td><div class=kiscim align=right>Nyelve:</div></td><td><select name=nyelv class=urlap>";
	$lekerdez=mysql_db_query($db_name,"select kod,nevhu from nyelvek");
	while(list($nyelvkod,$nyelvnev)=mysql_fetch_row($lekerdez)) {
		$urlap.="<option value=$nyelvkod";
		if($nyelvkod==$nyelv) $urlap.= ' selected';
		$urlap.=">$nyelvnev</option>";
	}
	$urlap.='</select></td></tr>';

	$urlap.="\n<tr><td><div class=kiscim align=right>Sorszám (sorrend):</div></td><td><input type=text name=sorszam value=\"$sorszam\" class=urlap size=2 maxlength=2></td></tr>";

//rovat
	$urlap.="\n<tr><td><div class=kiscim align=right>Rovat:</div></td><td><select name=rovat class=urlap>";
	$query_r="select id,nev from rovatok order by menuben,sorszam";
	$lekerdez_r=mysql_db_query($db_name,$query_r);
	while(list($rid,$rnev)=mysql_fetch_row($lekerdez_r)) {
		$urlap.="<option value=$rid";
		if($rid==$rovat) $urlap.=' selected';
		$urlap.=">$rnev</option>";
	}
	$urlap.="</select></td></tr>";

//Menüben	
	$urlap.="\n<tr><td><div class=kiscim align=right>Menüben látszik:</div></td><td><input type=checkbox name=menuben value='i' class=urlap";
	if($ok!='n') $urlap.=' checked';
	$urlap.="></td></tr>";

//Lista
	$urlap.="\n<tr><td bgcolor='#efefef'><div class=kiscim align=right>Listázandó hírek száma középen:</div></td><td bgcolor='#efefef'><input type=text name=lista value=\"$lista\" class=urlap size=2 maxlength=2><span class=alap> alapban a rovatnál megadott értékeket kapják</span></td></tr>";

//címek
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right><i>Friss hírek:</i></div></td><td bgcolor=#efefef><input type=text name=friss value=\"$friss\" class=urlap size=40 maxlength=50></td></tr>";
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right><i>További fontos:</i></div></td><td bgcolor=#efefef><input type=text name=k3 value=\"$k3\" class=urlap size=40 maxlength=50></td></tr>";

//Engedélyezés (jogosultság!)
	$urlap.="\n<tr><td><div class=kiscim align=right>Engedélyezés:</div></td><td><input type=checkbox name=ok value='i' class=urlap";
	if($ok!='n') $urlap.=' checked';
	$urlap.="></td></tr>";

	$urlap.='</table>';

	$urlap.="\n<br><input type=submit value=Mehet class=urlap></form>";

	$adatT[2]='<span class=alcim>Fõkategória szerkesztése</span><br><br>'.$urlap;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_fokatadding() {
	global $_POST,$db_name;

	$hiba=false;
	$fkid=$_POST['fkid'];
	$nev=$_POST['nev'];
	$rovat=$_POST['rovat'];
	$menuben=$_POST['menuben'];
	if($menuben!='i') $menuben='n';
	$nyelv=$_POST['nyelv'];
	$ok=$_POST['ok'];
	if($ok!='i') $ok='n';
	$sorszam=$_POST['sorszam'];
	$lista=$_POST['lista'];
	$friss=$_POST['friss'];
	$k3=$_POST['k3'];

	if(empty($nev)) {
		$hiba=true;
		$hibauzenet.='<br>Nem lett kitöltve a cím mezõ!';
	}

	if($hiba) {
		$txt.="<span class=hiba>HIBA a fõkategória szerkesztésénél!</span><br>";
		$txt.='<span class=alap>'.$hibauzenet.'</span>';
		$txt.="<br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";
	
		$adatT[2]='<span class=alcim>Fõkategória szerkesztése</span><br><br>'.$txt;
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);	
	}
	else {
		if($fkid>0) {
			$uj=false;
			$parameter1='update';
			$parameter2="where id='$fkid'";
		}
		else {
			$uj=true;
			$parameter1='insert';
			$parameter2="";
		}

		$query="$parameter1 fokat set nev='$nev', rovat='$rovat', sorszam='$sorszam', menuben='$menuben', lang='$nyelv', ok='$ok', lista='$lista', friss='$friss', k3='$k3' $parameter2";
		mysql_db_query($db_name,$query);
		if($uj) $fkid=mysql_insert_id();

		$kod=hirek_fokatadd($fkid);
	}

	return $kod;
}

function hirek_fokatdel() {
	global $_GET,$db_name,$linkveg,$m_id;

	$fkid=$_GET['fkid'];

	$kiir="<span class=alcim>Fõkategória törlése</span><br><br>";
	$kiir.="\n<span class=kiscim>Biztosan törölni akarod a következõ fõkategóriát?</span>";
		
	$query="select nev from fokat where id='$fkid'";
	list($nev)=mysql_fetch_row(mysql_db_query($db_name,$query));

	$kiir.="\n<br><br><span class=alap>$nev</span>";

	$kiir.="<br><br><a href=?m_id=$m_id&m_op=fokatdelete&fkid=$fkid$linkveg class=link>Igen</a> - <a href=?m_id=$m_id&m_op=fokat$linkveg class=link>NEM</a>";

	$adatT[2]=$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_fokatdelete() {
	global $_GET,$db_name;

	$id=$_GET['fkid'];
	$query="delete from fokat where id='$id'";
	mysql_db_query($db_name,$query);

	$kod=hirek_fokat();

	return $kod;
}


function hirek_kat() {
	global $db_name,$linkveg,$m_id,$_POST;

	$fokat=$_POST['fokat'];

	$urlap.="\n<form method=post><input type=hidden name=m_id value='$m_id'><input type=hidden name=m_op value=kat>";
	$urlap.="<span class=kiscim>Listázás fõkategóriák szerint:</span><br>";
	$urlap.="\n<select name=fokat class=urlap><option value=mind>Mind</option>";
	$query_kat="select id,nev from fokat where ok='i' order by sorszam";
	$lekerdez_kat=mysql_db_query($db_name,$query_kat);
	while(list($kid,$knev)=mysql_fetch_row($lekerdez_kat)) {
		$urlap.="<option value=$kid";
		if($kid==$fokat) $urlap.=" selected";
		$urlap.=">$knev</option>";
	}
	$urlap.="</select><input type=submit value=Lista class=urlap></form><br>";

	if($fokat!='mind' and isset($fokat)) $feltetel="where fokat='$fokat'";

	$kiir.="<a href=?m_id=$m_id&m_op=katadd$linkveg class=link><b>Új alkategória hozzáadása</b></a><br><br>";

	$kiir.=$urlap;

	$query="select id,nev,ok from kat $feltetel order by sorszam";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($kid,$knev,$kok)=mysql_fetch_row($lekerdez)) {
		if($kok!='i') $OK="<span class=alap><font color=red>(várakozó)</font></span>";
		else $OK="<span class=alap><font color=green>(aktív)</font></span>";
		$kiir.="\n<a href=?m_id=$m_id&m_op=katadd&kid=$kid$linkveg class=link><b>- $knev</b></a> $OK - <a href=?m_id=$m_id&m_op=katdel&kid=$kid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";
	}

	$adatT[2]='<span class=alcim>Kategóriák szerkesztése</span><br><br>'.$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;


	return $kod;
}

function hirek_katadd($kid) {
	global $sessid,$linkveg,$m_id,$db_name;

	if($kid>0) {
		$query="select nev,fokat,sorszam,menuben,ok,lista,friss,k3 from kat where id='$kid'";	list($nev,$fokat,$sorszam,$menuben,$ok,$lista,$friss,$k3)=mysql_fetch_row(mysql_db_query($db_name,$query));
	}

	$urlap="\n<form method=post>";

	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sessid value=$sessid>";
	$urlap.="\n<input type=hidden name=m_op value=katadding><input type=hidden name=kid value=$kid>";
	
	$urlap.='<table>';

	$urlap.="\n<tr><td><div class=kiscim align=right>Kategória címe:</div></td><td><input type=text name=nev value=\"$nev\" class=urlap size=50 maxlength=50></td></tr>";
	$urlap.="\n<tr><td><div class=kiscim align=right>Sorszám (sorrend):</div></td><td><input type=text name=sorszam value=\"$sorszam\" class=urlap size=2 maxlength=2></td></tr>";

//fõkat
	$urlap.="\n<tr><td><div class=kiscim align=right>Fõkategória:</div></td><td><select name=fokat class=urlap>";
	$query_r="select id,nev,rovat from fokat order by rovat,sorszam";
	$lekerdez_r=mysql_db_query($db_name,$query_r);
	while(list($fkid,$fknev,$fkrovat)=mysql_fetch_row($lekerdez_r)) {
		if(empty($rovatnev[$fkrovat])) {
			list($rnev)=mysql_fetch_row(mysql_db_query($db_name,"select nev from rovatok where id='$fkrovat'"));
			$rovatnev[$fkrovat]=$rnev;
		}
		$urlap.="<option value=$fkid";
		if($fkid==$fokat) $urlap.=' selected';
		$urlap.=">$fknev ($rovatnev[$fkrovat])</option>";
	}
	$urlap.="</select></td></tr>";

//Menüben	
	$urlap.="\n<tr><td><div class=kiscim align=right>Menüben látszik:</div></td><td><input type=checkbox name=menuben value='i' class=urlap";
	if($ok!='n') $urlap.=' checked';
	$urlap.="></td></tr>";

//Lista
	$urlap.="\n<tr><td bgcolor='#efefef'><div class=kiscim align=right>Listázandó hírek száma középen:</div></td><td bgcolor='#efefef'><input type=text name=lista value=\"$lista\" class=urlap size=2 maxlength=2><span class=alap> alapban a fõkategória/rovat értékei</span></td></tr>";

//címek
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right><i>Friss hírek:</i></div></td><td bgcolor=#efefef><input type=text name=friss value=\"$friss\" class=urlap size=40 maxlength=50></td></tr>";
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right><i>További fontos:</i></div></td><td bgcolor=#efefef><input type=text name=k3 value=\"$k3\" class=urlap size=40 maxlength=50></td></tr>";

//Engedélyezés (jogosultság!)
	$urlap.="\n<tr><td><div class=kiscim align=right>Engedélyezés:</div></td><td><input type=checkbox name=ok value='i' class=urlap";
	if($ok!='n') $urlap.=' checked';
	$urlap.="></td></tr>";

	$urlap.='</table>';

	$urlap.="\n<br><input type=submit value=Mehet class=urlap></form>";

	$adatT[2]='<span class=alcim>Kategória szerkesztése</span><br><br>'.$urlap;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_katadding() {
	global $_POST,$db_name;

	$hiba=false;
	$kid=$_POST['kid'];
	$nev=$_POST['nev'];
	$fokat=$_POST['fokat'];
	list($rovat)=mysql_fetch_row(mysql_db_query($db_name,"select rovat from fokat where id='$fokat'"));
	$menuben=$_POST['menuben'];
	if($menuben!='i') $menuben='n';
	$ok=$_POST['ok'];
	if($ok!='i') $ok='n';
	$sorszam=$_POST['sorszam'];
	$lista=$_POST['lista'];
	$friss=$_POST['friss'];
	$k3=$_POST['k3'];

	if(empty($nev)) {
		$hiba=true;
		$hibauzenet.='<br>Nem lett kitöltve a cím mezõ!';
	}

	if($hiba) {
		$txt.="<span class=hiba>HIBA a kategória szerkesztésénél!</span><br>";
		$txt.='<span class=alap>'.$hibauzenet.'</span>';
		$txt.="<br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";
	
		$adatT[2]='<span class=alcim>Kategória szerkesztése</span><br><br>'.$txt;
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);	
	}
	else {
		if($kid>0) {
			$uj=false;
			$parameter1='update';
			$parameter2="where id='$kid'";
		}
		else {
			$uj=true;
			$parameter1='insert';
			$parameter2="";
		}

		$query="$parameter1 kat set nev='$nev', fokat='$fokat', rovat='$rovat', sorszam='$sorszam', menuben='$menuben', ok='$ok', lista='$lista', friss='$friss', k3='$k3' $parameter2";
		mysql_db_query($db_name,$query);
		if($uj) $kid=mysql_insert_id();

		$kod=hirek_katadd($kid);
	}

	return $kod;
}

function hirek_katdel() {
	global $_GET,$db_name,$linkveg,$m_id;

	$kid=$_GET['kid'];

	$kiir="<span class=alcim>Kategória törlése</span><br><br>";
	$kiir.="\n<span class=kiscim>Biztosan törölni akarod a következõ kategóriát?</span>";
		
	$query="select nev from kat where id='$kid'";
	list($nev)=mysql_fetch_row(mysql_db_query($db_name,$query));

	$kiir.="\n<br><br><span class=alap>$nev</span>";

	$kiir.="<br><br><a href=?m_id=$m_id&m_op=katdelete&kid=$kid$linkveg class=link>Igen</a> - <a href=?m_id=$m_id&m_op=kat$linkveg class=link>NEM</a>";

	$adatT[2]=$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_katdelete() {
	global $_GET,$db_name;

	$id=$_GET['kid'];
	$query="delete from kat where id='$id'";
	mysql_db_query($db_name,$query);

	$kod=hirek_kat();

	return $kod;
}


function hirek_alkat() {
	global $db_name,$linkveg,$m_id,$_POST;

	$kat=$_POST['kat'];

	$urlap.="\n<form method=post><input type=hidden name=m_id value='$m_id'><input type=hidden name=m_op value=alkat>";
	$urlap.="<span class=kiscim>Listázás fõkategóriák szerint:</span><br>";
	$urlap.="\n<select name=kat class=urlap><option value=mind>Mind</option>";
	$query_kat="select id,nev from kat where ok='i' order by sorszam";
	$lekerdez_kat=mysql_db_query($db_name,$query_kat);
	while(list($kid,$knev)=mysql_fetch_row($lekerdez_kat)) {
		$urlap.="<option value=$kid";
		if($kid==$fokat) $urlap.=" selected";
		$urlap.=">$knev</option>";
	}
	$urlap.="</select><input type=submit value=Lista class=urlap></form><br>";

	if($kat!='mind' and isset($kat)) $feltetel="where kat='$kat'";

	$kiir.="<a href=?m_id=$m_id&m_op=alkatadd$linkveg class=link><b>Új alkategória hozzáadása</b></a><br><br>";

	$kiir.=$urlap;

	$query="select id,nev,ok from alkat $feltetel order by sorszam";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($akid,$aknev,$akok)=mysql_fetch_row($lekerdez)) {
		if($akok!='i') $OK="<span class=alap><font color=red>(várakozó)</font></span>";
		else $OK="<span class=alap><font color=green>(aktív)</font></span>";
		$kiir.="\n<a href=?m_id=$m_id&m_op=alkatadd&akid=$akid$linkveg class=link><b>- $aknev</b></a> $OK - <a href=?m_id=$m_id&m_op=alkatdel&akid=$akid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";
	}

	$adatT[2]='<span class=alcim>Alkategóriák szerkesztése</span><br><br>'.$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;


	return $kod;
}

function hirek_alkatadd($akid) {
	global $sessid,$linkveg,$m_id,$db_name;

	if($akid>0) {
		$query="select nev,kat,sorszam,menuben,ok,lista,friss,k3 from alkat where id='$akid'";	list($nev,$kat,$sorszam,$menuben,$ok,$lista,$friss,$k3)=mysql_fetch_row(mysql_db_query($db_name,$query));
	}

	$urlap="\n<form method=post>";

	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sessid value=$sessid>";
	$urlap.="\n<input type=hidden name=m_op value=alkatadding><input type=hidden name=akid value=$akid>";
	
	$urlap.='<table>';

	$urlap.="\n<tr><td><div class=kiscim align=right>Alkategória címe:</div></td><td><input type=text name=nev value=\"$nev\" class=urlap size=50 maxlength=50></td></tr>";
	$urlap.="\n<tr><td><div class=kiscim align=right>Sorszám (sorrend):</div></td><td><input type=text name=sorszam value=\"$sorszam\" class=urlap size=2 maxlength=2></td></tr>";

//fõkat
	$urlap.="\n<tr><td><div class=kiscim align=right>Kategória:</div></td><td><select name=kat class=urlap>";
	$query_r="select id,nev,rovat,fokat from kat order by rovat,sorszam";
	$lekerdez_r=mysql_db_query($db_name,$query_r);
	while(list($kid,$knev,$krovat,$fkat)=mysql_fetch_row($lekerdez_r)) {
		if(empty($rovatnev[$krovat])) {
			list($rnev)=mysql_fetch_row(mysql_db_query($db_name,"select nev from rovatok where id='$krovat'"));
			$rovatnev[$krovat]=$rnev;
		}
		if(empty($fokatnev[$fkat])) {
			list($fknev)=mysql_fetch_row(mysql_db_query($db_name,"select nev from fokat where id='$fkat'"));
			$fokatnev[$fkat]=$fknev;
		}
		$urlap.="<option value=$kid";
		if($kid==$kat) $urlap.=' selected';
		$urlap.=">$knev ($rovatnev[$krovat] / $fokatnev[$fkat])</option>";
	}
	$urlap.="</select></td></tr>";

//Menüben	
	$urlap.="\n<tr><td><div class=kiscim align=right>Menüben látszik:</div></td><td><input type=checkbox name=menuben value='i' class=urlap";
	if($ok!='n') $urlap.=' checked';
	$urlap.="></td></tr>";

//Lista
	$urlap.="\n<tr><td bgcolor='#efefef'><div class=kiscim align=right>Listázandó hírek száma középen:</div></td><td bgcolor='#efefef'><input type=text name=lista value=\"$lista\" class=urlap size=2 maxlength=2><span class=alap> alapban a rovat/fõkat/kat értékei</span></td></tr>";

//címek
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right><i>Friss hírek:</i></div></td><td bgcolor=#efefef><input type=text name=friss value=\"$friss\" class=urlap size=40 maxlength=50></td></tr>";
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right><i>További fontos:</i></div></td><td bgcolor=#efefef><input type=text name=k3 value=\"$k3\" class=urlap size=40 maxlength=50></td></tr>";

//Engedélyezés (jogosultság!)
	$urlap.="\n<tr><td><div class=kiscim align=right>Engedélyezés:</div></td><td><input type=checkbox name=ok value='i' class=urlap";
	if($ok!='n') $urlap.=' checked';
	$urlap.="></td></tr>";

	$urlap.='</table>';

	$urlap.="\n<br><input type=submit value=Mehet class=urlap></form>";

	$adatT[2]='<span class=alcim>Alkategória szerkesztése</span><br><br>'.$urlap;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_alkatadding() {
	global $_POST,$db_name;

	$hiba=false;
	$akid=$_POST['akid'];
	$nev=$_POST['nev'];
	$kat=$_POST['kat'];
	list($rovat,$fokat)=mysql_fetch_row(mysql_db_query($db_name,"select rovat,fokat from kat where id='$kat'"));
	$menuben=$_POST['menuben'];
	if($menuben!='i') $menuben='n';
	$ok=$_POST['ok'];
	if($ok!='i') $ok='n';
	$sorszam=$_POST['sorszam'];
	$lista=$_POST['lista'];
	$friss=$_POST['friss'];
	$k3=$_POST['k3'];

	if(empty($nev)) {
		$hiba=true;
		$hibauzenet.='<br>Nem lett kitöltve a cím mezõ!';
	}

	if($hiba) {
		$txt.="<span class=hiba>HIBA a alkategória szerkesztésénél!</span><br>";
		$txt.='<span class=alap>'.$hibauzenet.'</span>';
		$txt.="<br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";
	
		$adatT[2]='<span class=alcim>Alkategória szerkesztése</span><br><br>'.$txt;
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);	
	}
	else {
		if($akid>0) {
			$uj=false;
			$parameter1='update';
			$parameter2="where id='$akid'";
		}
		else {
			$uj=true;
			$parameter1='insert';
			$parameter2="";
		}

		$query="$parameter1 alkat set nev='$nev', fokat='$fokat', rovat='$rovat', kat='$kat', sorszam='$sorszam', menuben='$menuben', ok='$ok', lista='$lista', friss='$friss', k3='$k3' $parameter2";
		mysql_db_query($db_name,$query);
		if($uj) $akid=mysql_insert_id();

		$kod=hirek_alkatadd($akid);
	}

	return $kod;
}

function hirek_alkatdel() {
	global $_GET,$db_name,$linkveg,$m_id;

	$akid=$_GET['akid'];

	$kiir="<span class=alcim>Alkategória törlése</span><br><br>";
	$kiir.="\n<span class=kiscim>Biztosan törölni akarod a következõ alkategóriát?</span>";
		
	$query="select nev from alkat where id='$akid'";
	list($nev)=mysql_fetch_row(mysql_db_query($db_name,$query));

	$kiir.="\n<br><br><span class=alap>$nev</span>";

	$kiir.="<br><br><a href=?m_id=$m_id&m_op=alkatdelete&akid=$akid$linkveg class=link>Igen</a> - <a href=?m_id=$m_id&m_op=alkat$linkveg class=link>NEM</a>";

	$adatT[2]=$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_alkatdelete() {
	global $_GET,$db_name;

	$id=$_GET['akid'];
	$query="delete from alkat where id='$id'";
	mysql_db_query($db_name,$query);

	$kod=hirek_alkat();

	return $kod;
}

//Jogosultság ellenõrzése
if(strstr($u_jogok,'hirek')) {

switch($m_op) {
    case 'index':
        $tartalom=hirek_index();
        break;

	case 'add':
		$hid=$_GET['hid'];
        $tartalom=hirek_add($hid);
        break;

    case 'mod':
        $tartalom=hirek_mod();
        break;

    case 'adding':
        $tartalom=hirek_adding();
        break;

    case 'del':
        $tartalom=hirek_del();
        break;

	case 'delete':
        $tartalom=hirek_delete();
        break;

    case 'rovat':
		if($u_beosztas=='fsz') {
			$tartalom=hirek_rovat();
		}
        break;

    case 'rovatadd':
		if($u_beosztas=='fsz') {
			$rid=$_GET['rid'];
		    $tartalom=hirek_rovatadd($rid);
		}
        break;

    case 'rovatadding':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_rovatadding();
		}
        break;

    case 'rovatdel':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_rovatdel();
		}
        break;

    case 'rovatdelete':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_rovatdelete();
		}
        break;

    case 'fokat':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_fokat();
		}
        break;

    case 'fokatadd':
		if($u_beosztas=='fsz') {
			$fkid=$_GET['fkid'];
		    $tartalom=hirek_fokatadd($fkid);
		}
        break;

    case 'fokatadding':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_fokatadding();
		}
        break;

    case 'fokatdel':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_fokatdel();
		}
        break;

    case 'fokatdelete':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_fokatdelete();
		}
        break;

    case 'kat':
		if($u_beosztas=='fsz') {
			$tartalom=hirek_kat();
		}
        break;

    case 'katadd':
		if($u_beosztas=='fsz') {
			$kid=$_GET['kid'];
		    $tartalom=hirek_katadd($kid);
		}
        break;

    case 'katadding':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_katadding();
		}
        break;

    case 'katdel':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_katdel();
		}
        break;

    case 'katdelete':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_katdelete();
		}
        break;

	case 'alkat':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_alkat();
		}
        break;

    case 'alkatadd':
		if($u_beosztas=='fsz') {
			$akid=$_GET['akid'];
		    $tartalom=hirek_alkatadd($akid);
		}
        break;

    case 'alkatadding':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_alkatadding();
		}
        break;

    case 'alkatdel':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_alkatdel();
		}
        break;

    case 'alkatdelete':
		if($u_beosztas=='fsz') {
	        $tartalom=hirek_alkatdelete();
		}
        break;

}
}
else {
	$tartalom="\n<span class=hiba>HIBA! Nincs hozzá jogosultságod!</span>";
}

?>
