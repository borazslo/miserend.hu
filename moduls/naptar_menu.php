<?php

function naptar_jobbmenu() {
    global $linkveg,$db_name,$elso,$m_id,$m_op,$sid,$_POST,$_GET,$bgcolor;

	$tartalom=naptari();
	$kodT[0]="<a href=?m_id=15$linkveg class=hasabcimlink>Eseménynaptár</a>";
	$kodT[1]=$tartalom;

	return $kodT;
}


function naptari() {
	global $dateh, $linkveg,$_GET,$db_name,$szabadnap;

	//ünnepek:
	$unnepT=array('01-01'=>'Újév', '03-15'=>'Nemzeti ünnep', '05-01'=>'Munka ünnepe', '08-20'=>'Nemzeti Ünnep', '10-23'=>'Nemzeti ünnep', '11-01'=>'Mindenszentek ünnepe', '12-25'=>'Karácsony', '12-26'=>'Karácsony');	
	$szabadnapT=array('01-01'=>1, '03-15'=>1, '05-01'=>1, '08-20'=>1, '10-23'=>1, '11-01'=>1, '12-25'=>1, '12-26'=>1);

	if(is_array($_GET)) {
		foreach($_GET as $kulcs=>$ertek) {
			if($kulcs!='sid' and $kulcs!='dateh') $parameterekT[]="$kulcs=$ertek";
		}
		if(is_array($parameterekT)) $parameterek='&'.implode('&',$parameterekT);
	}
	
	$vars = monthi();
	$day_name_short = array('H', 'K', 'Sz', 'Cs', 'P', 'Sz', 'V');

	$datumT=explode('-',$_GET['date']);

	$text .= '<table width=100%>';
	$text .= '<tr>';
	$text .= '<td align="right">';
	$text .= '<a href="?dateh=' . $vars['prevYear'] . '-' . $vars['prevMonth'] . $parameterek.$linkveg.'" title="előző hónap" class="linkkicsi">&lt;&lt;</a>';
	$text .= '</td>';
	$text .= '<td align="center" colspan="5">';
	$text .= "<a href='?m_id=15&ev=$vars[currYear]&honap=$vars[currMonth]$linkveg' class=link><b>" . $vars['currYear'] . '. ' . $vars['months'][$vars['currMonth']] . '</b></a>';
	$text .= '</td>';

	$text .= '<td align="left">';
	$text .= '<a href="?dateh=' . $vars['nextYear']. '-' . $vars['nextMonth'] .$parameterek.$linkveg.'" title="következő hónap" class="linkkicsi">&gt;&gt;</a>';
	$text .= '</td>';
	$text .= '</tr>';
	
	$text .= '<tr><td colspan=7><img src=img/space.gif width=5 height=4></td></tr>';
	
	$text .= '<tr>';
	for ($x = 0; $x < 7; $x++) {
		if($x<5) $arany='14%';
		else $arany='15%';
		$text .= '<td align="center" width="'.$arany.'" class="kicsi">'.$day_name_short[$x].'</td>';
	}
	$text .= '</tr>';

	$text .= '<tr>';

	$rowCount = 0;

	$ureskockak = $vars['firstDayOfMonth'];
	if($ureskockak == (-1)) $ureskockak = 6;

	for ($x = 1; $x <= $ureskockak; $x++)
		{
		$rowCount++;
		$text .= '<td height=16><img src=img/space.gif width=1 height=1></td>';
		}

	$dayCount=1;

/*
//Színek
	$szin_query = "SELECT datum,szin FROM lnaptar WHERE datum LIKE '".$vars['currYear']."-".str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT)."-%' ORDER BY datum;";

	$_szin = mysql_query($szin_query) or die(mysql_error());
	while($szin = mysql_fetch_array($_szin))
		{
		$n=substr($szin[0],-2);
		if($n[0]==0) $n=$n[1];
		$col = $szin[1];
		$kesz_szin[$n] = array($col);	
		}
*/	


	//mozgó ünnepek (adatbázisból)
	$ev=$vars['currYear'];
	$honap=str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT);

	$kezd="$ev-$honap-01";
	$veg="$ev-$honap-31";
	$query="select datum,unnep,szabadnap from unnepnaptar where datum>'$kezd' and datum<'$veg'";
	if(!$lekerdez=mysql_query($query)) echo "HIBA!<br>$query<br>".mysql_error();
	while(list($datum,$unnep,$szabadnap)=mysql_fetch_row($lekerdez)) {
		$unnep_ho=substr($datum,5,2);
		$unnep_nap=substr($datum,8,2);
		if($szabadnap=='i' and !empty($unnep)) {
			$szabadnapT["$unnep_ho-$unnep_nap"]=1;
		}
		$unnepT["$unnep_ho-$unnep_nap"]=$unnep;	
	}

	while ($dayCount <= $vars['totalDays'][$vars['currMonth']]) 
		{
		$nap=str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT)  . '-' . str_pad($dayCount, 2, '0', STR_PAD_LEFT);
		//$class = $kesz_szin[$dayCount][0];
		//if(empty($class)) $class='zold';
		if ($rowCount % 7 == 0 && $rowCount >= 7) 
			{
			$text .= '</tr><tr>';
			}
		if ($dayCount == date("j") && $vars['currYear'] == date("Y") && $vars['currMonth'] == date("n") and ($szabadnapT[$nap] or $rowCount%7 == 6))  // today + ünnep
			{
			$text .= '<td align="center" class="maiunnepnaptar" style="border-width: 2px;"><a href="?m_id=15&m_op=view&date=' . $vars['currYear'] . '-' . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT)  . '-' . str_pad($dayCount, 2, '0', STR_PAD_LEFT) . "". $linkveg .'" class="linkkicsi" title="'.$unnepT[$nap].'">' .  $dayCount .  '</a></td>';
			}
		elseif ($dayCount == date("j") && $vars['currYear'] == date("Y") && $vars['currMonth'] == date("n"))  // today
			{
			$text .= '<td align="center" class="mainaptar" style="border-width: 2px;"><a href="?m_id=15&m_op=view&date=' . $vars['currYear'] . '-' . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT)  . '-' . str_pad($dayCount, 2, '0', STR_PAD_LEFT) . "". $linkveg .'" class="linkkicsi" title="'.$unnepT[$nap].'">' .  $dayCount .  '</a></td>';
			}
		elseif ($dayCount == $datumT[2] && $vars['currYear'] == $datumT[0] && $vars['currMonth'] == $datumT[1] and ($szabadnapT[$nap] or $rowCount%7 == 6))  // select + ünnep
			{
			$text .= '<td align="center" class="selunnepnaptar"><a href="?m_id=15&m_op=view&date=' . $vars['currYear'] . '-' . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT)  . '-' . str_pad($dayCount, 2, '0', STR_PAD_LEFT) . "" . $linkveg .'" class="linkkicsi" title="'.$unnepT[$nap].'">' .  $dayCount .  '</a></td>';
			}
		elseif ($dayCount == $datumT[2] && $vars['currYear'] == $datumT[0] && $vars['currMonth'] == $datumT[1])  // select
			{
			$text .= '<td align="center" class="selnaptar"><a href="?m_id=15&m_op=view&date=' . $vars['currYear'] . '-' . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT)  . '-' . str_pad($dayCount, 2, '0', STR_PAD_LEFT) . "" . $linkveg .'" class="linkkicsi" title="'.$unnepT[$nap].'">' .  $dayCount .  '</a></td>';
			}
		elseif($szabadnapT[$nap]>0 or $rowCount%7 == 6)
			{
			$text .= '<td align="center" class="unnepnaptar"><a href="?m_id=15&m_op=view&date=' . $vars['currYear'] . '-' . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT)  . '-' . str_pad($dayCount, 2, '0', STR_PAD_LEFT) . "" . $linkveg .'" class="linkkicsi" title="'.$unnepT[$nap].'">' .  $dayCount .  '</a></td>';
			}
		else 
			{
			$text .= '<td align="center" class="naptar"><a href="?m_id=15&m_op=view&date=' . $vars['currYear'] . '-' . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dayCount, 2, '0', STR_PAD_LEFT) . "" . $linkveg .'" class="linkkicsi" title="'.$unnepT[$nap].'">' . $dayCount . '</a></td>';
			}
		$dayCount++;
		$rowCount++;
	}

	while ($rowCount % 7 != 0) 
		{
		$text .= '<td height=16><img src=img/space.gif width=1 height=1></td>';
		$rowCount++;
		}

	$text .= '</tr>';
	$text .= '</table>';

	return $text;
}

function monthi() {
	$months = array('', 'január', 'február', 'március', 'április', 'május', 'június', 'július', 'augusztus', 'szeptember', 'október', 'november', 'december');

	if(isset($_GET['dateh']) or isset($_GET['date']))
		{
		if(!empty($_GET['dateh'])) $valtozo=$_GET['dateh'];
		else $valtozo=$_GET['date'];
			
		$datum = explode('-', $valtozo);

		if(isset($datum[0]))
			{
			$currYear = $datum[0];
			}
		if(isset($datum[1]))
			{
			if($datum[1][0] == '0')
			$datum[1] = $datum[1][1];
			$currMonth = $datum[1];
			}
		if(isset($datum[2]))
			{
			if($datum[2][0] == '0')
			$datum[2] = $datum[2][1];
			$currDay = $datum[2];
			}
		}

		// set up some variables to identify the month, date and year to display
		if(!isset($currYear) || !isset($valtozo)  || $valtozo == '') 
			{
			$currYear = date("Y");
			}
		if(!isset($currMonth) || !isset($valtozo)  || $valtozo == '') 
			{
			$currMonth = date("n");
			}
		if(!isset($currDay) || !isset($valtozo)  || $valtozo == '') 
			{
			$currDay = date("j");
			}

	// number of days in each month
	$totalDays = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

	// if leap year, modify $totaldays array appropriately
	if (date("L", mktime(0,0,0,$currMonth,1,$currYear))) 
		{
		$totalDays[2] = 29;
		}

	// set up variables to display previous and next months correctly

	// defaults for previous month
	$prevMonth = $currMonth-1;
	$prevYear = $currYear;

	// if January, decrement year and set month to December
	if ($prevMonth < 1) 
		{
		$prevMonth = 12;
		$prevYear--;
		} 

	// defaults for next month
	$nextMonth = $currMonth+1;
	$nextYear = $currYear;

	// if December, increment year and set month to January
	if ($nextMonth > 12) 
		{
		$nextMonth = 1;
		$nextYear++;
		} 

	// get down to displaying the calendar
	// find out which day the first of the month falls on
	// set -1 on the end, while 'w' means English method 
	
// itt eredetileg a sor végén volt egy -1, de nemtom minek. rudanj
	
	$firstDayOfMonth = date("w", mktime(0,0,0,$currMonth,1,$currYear)) - 1;

	$vars = compact('months', 'currYear', 'currMonth', 'currDay', 'totalDays', 'nextYear', 'nextMonth', 'prevYear', 'prevMonth', 'firstDayOfMonth');

	return $vars;
}


switch($op) {
	case '1':
		$hmenuT=naptar_jobbmenu();
		break;
	
	case '2':
		$hmenuT=naptar_jobbmenu();
		break;
}

?>
