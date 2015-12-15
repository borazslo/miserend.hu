<?php

// -------------------------------
//
// HAVI NAPTAR MEGJELENITESE
//
// -------------------------------

function naptari() {
    global $dateh, $linkveg, $_GET;

    $vars = monthi();
    $day_name_short = array(_MO, _TU, _WE, _TH, _FR, _SA, _SU);

    $datumT = explode('-', $_GET['date']);

    $text .= '<table width=85%>';
    $text .= '<tr>';
    $text .= '<td align="right">';
    $text .= '<a href="?m_id=1&op=aktiv&dateh=' . $vars['prevYear'] . '-' . $vars['prevMonth'] . $linkveg . '" title="' . _PREV_MONTH . '" class="kiscimlink">&lt;&lt;&lt;</a>';
    $text .= '</td>';
    $text .= '<td align="center" colspan="5">';
    $text .= "<a href='?m_id=1&op=aktiv&dateh=$vars[currYear]-$vars[currMonth]$linkveg' class=kiscimlink>" . $vars['currYear'] . '. ' . $vars['months'][$vars['currMonth']] . '</a>';
    $text .= '</td>';

    $text .= '<td align="left">';
    $text .= '<a href="?m_id=1&op=aktiv&dateh=' . $vars['nextYear'] . '-' . $vars['nextMonth'] . $linkveg . '" title="' . _NEXT_MONTH . '" class="kiscimlink">&gt;&gt;&gt;</a>';
    $text .= '</td>';
    $text .= '</tr>';

    $text .= '<tr><td colspan=7><img src=img/space.gif width=5 height=8></td></tr>';

    $text .= '<tr>';
    for ($x = 0; $x < 7; $x++) {
        if ($x < 5)
            $arany = '14%';
        else
            $arany = '15%';
        $text .= '<td align="center" width="' . $arany . '" class="kicsi">' . $day_name_short[$x] . '</td>';
    }
    $text .= '</tr>';

    $text .= '<tr>';

    $rowCount = 0;

    $ureskockak = $vars['firstDayOfMonth'];
    if ($ureskockak == (-1))
        $ureskockak = 6;

    for ($x = 1; $x <= $ureskockak; $x++) {
        $rowCount++;
        $text .= '<td>&nbsp;</td>';
    }

    $dayCount = 1;
//Színek
    $szin_query = "SELECT datum,szin FROM lnaptar WHERE datum LIKE '" . $vars['currYear'] . "-" . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT) . "-%' ORDER BY datum;";

    $_szin = mysql_query($szin_query) or die(mysql_error());
    while ($szin = mysql_fetch_array($_szin)) {
        $n = substr($szin[0], -2);
        if ($n[0] == 0)
            $n = $n[1];
        $col = $szin[1];
        $kesz_szin[$n] = array($col);
    }

    while ($dayCount <= $vars['totalDays'][$vars['currMonth']]) {
        $class = $kesz_szin[$dayCount][0];
        if (empty($class))
            $class = 'zold';
        if ($rowCount % 7 == 0 && $rowCount >= 7) {
            $text .= '</tr><tr>';
        }
        if ($dayCount == date("j") && $vars['currYear'] == date("Y") && $vars['currMonth'] == date("n")) {  // today
            $text .= '<td align="center" class="mainaptar_' . $class . '" style="border-width: 2px;"><a href="?m_id=1&m_op=view&date=' . $vars['currYear'] . '-' . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dayCount, 2, '0', STR_PAD_LEFT) . "&szin=$class" . $linkveg . '" class="akislink">' . $dayCount . '</a></td>';
        } elseif ($dayCount == $datumT[2] && $vars['currYear'] == $datumT[0] && $vars['currMonth'] == $datumT[1]) {  // select
            $text .= '<td align="center" class="selnaptar_' . $class . '"><a href="?m_id=1&m_op=view&date=' . $vars['currYear'] . '-' . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dayCount, 2, '0', STR_PAD_LEFT) . "&szin=$class" . $linkveg . '" class="kislink">' . $dayCount . '</a></td>';
        } else {
            $text .= '<td align="center" class="naptar_' . $class . '"><a href="?m_id=1&m_op=view&date=' . $vars['currYear'] . '-' . str_pad($vars['currMonth'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($dayCount, 2, '0', STR_PAD_LEFT) . "&szin=$class" . $linkveg . '" class="kislink">' . $dayCount . '</a></td>';
        }
        $dayCount++;
        $rowCount++;
    }

    while ($rowCount % 7 != 0) {
        $text .= '<td>&nbsp;</td>';
        $rowCount++;
    }

    $text .= '</tr>';


    // az alsó "folyó hó" linket kihúztam
    /*
      $text .= '<tr>';
      $text .= '<td align="center" colspan="7">';
      $text .= '<br><small><a href="index.php?m_id=1&op=aktiv&sessid='.$sessid.'">'._CURR_MONTH.'</a>';
      $text .= '</td>';
      $text .= '</tr>';
     */
    $text .= '</table>';

    return $text;
}

function monthi() {
    $months = array('', _JAN, _FEBR, _MARC, _APR, _MAY, _JUN, _JUL, _AUG, _SEPT, _OCT, _NOV, _DEC);

    if (isset($_GET['dateh']) or isset($_GET['date'])) {
        if (!empty($_GET['dateh']))
            $valtozo = $_GET['dateh'];
        else
            $valtozo = $_GET['date'];

        $datum = explode('-', $valtozo);

        if (isset($datum[0])) {
            $currYear = $datum[0];
        }
        if (isset($datum[1])) {
            if ($datum[1][0] == '0')
                $datum[1] = $datum[1][1];
            $currMonth = $datum[1];
        }
        if (isset($datum[2])) {
            if ($datum[2][0] == '0')
                $datum[2] = $datum[2][1];
            $currDay = $datum[2];
        }
    }

    // set up some variables to identify the month, date and year to display
    if (!isset($currYear) || !isset($valtozo) || $valtozo == '') {
        $currYear = date("Y");
    }
    if (!isset($currMonth) || !isset($valtozo) || $valtozo == '') {
        $currMonth = date("n");
    }
    if (!isset($currDay) || !isset($valtozo) || $valtozo == '') {
        $currDay = date("j");
    }

    // number of days in each month
    $totalDays = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    // if leap year, modify $totaldays array appropriately
    if (date("L", mktime(0, 0, 0, $currMonth, 1, $currYear))) {
        $totalDays[2] = 29;
    }

    // set up variables to display previous and next months correctly
    // defaults for previous month
    $prevMonth = $currMonth - 1;
    $prevYear = $currYear;

    // if January, decrement year and set month to December
    if ($prevMonth < 1) {
        $prevMonth = 12;
        $prevYear--;
    }

    // defaults for next month
    $nextMonth = $currMonth + 1;
    $nextYear = $currYear;

    // if December, increment year and set month to January
    if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear++;
    }

    // get down to displaying the calendar
    // find out which day the first of the month falls on
    // set -1 on the end, while 'w' means English method 
// itt eredetileg a sor végén volt egy -1, de nemtom minek. rudanj

    $firstDayOfMonth = date("w", mktime(0, 0, 0, $currMonth, 1, $currYear)) - 1;

    $vars = compact('months', 'currYear', 'currMonth', 'currDay', 'totalDays', 'nextYear', 'nextMonth', 'prevYear', 'prevMonth', 'firstDayOfMonth');

    return $vars;
}

?>