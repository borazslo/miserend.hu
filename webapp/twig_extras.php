<?php
/*
 * twig_extras.php
 *
 * Ebben a fájlban találhatók a Twig-hez készített egyedi filterek és kiegészítők.
 *
 * A Twig sablonmotort a load.php-ban és a classes/html/html.php-ban inicializáljuk.
 * Ez a file tartalmazza az első saját Twig filterünket.
 *
 * Használat:
 *   - A filtert regisztrálni kell a Twig environmentben (pl. load.php-ban):
 *       $twig->addFilter(new \Twig\TwigFilter('hungarian_date', 'twig_hungarian_date_format'));
 *   - A sablonban így használható: {{ datum|hungarian_date }}
 *
 * Dokumentáció:
 *   - Ez a filter magyar dátumformátumot ad vissza.
 *   - A bemenet lehet string vagy timestamp.
 *   - További filterek is ide kerülhetnek a jövőben.
 */


function twig_hungarian_date_format($date, $format = null) {
    global $_honapok;

    // Ha string, konvertáld timestamp-re
    if (is_string($date)) {
        $date = strtotime($date);
    }
    
    $napok = ['vasárnap', 'hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat'];

    // Alapértelmezett formátum: H:i
    $timeFormat = $format !== null ? $format : 'H:i';
    $showTime = ($timeFormat !== '');

    // Ha ma van
    if (date('Y-m-d', $date) === date('Y-m-d')) {
        return 'ma' . ($showTime ? ' ' . date($timeFormat, $date) : '');
    }
    // Ha tegnap volt
    if (date('Y-m-d', $date) === date('Y-m-d', strtotime('-1 day'))) {
        $weekday = $napok[(int)date('w', $date)];
        return 'tegnap, ' . $weekday . ($showTime ? ' ' . date($timeFormat, $date) : '');
    }
    // Ha holnap lesz
    if (date('Y-m-d', $date) === date('Y-m-d', strtotime('+1 day'))) {
        $weekday = $napok[(int)date('w', $date)];
        return 'holnap, ' . $weekday . ($showTime ? ' ' . date($timeFormat, $date) : '');
    }
    // Ha az aktuális héten van (előző vasárnaptól következő vasárnapig)
    $today = strtotime(date('Y-m-d'));
    $dow = date('w', $today); // 0=vasárnap
    $week_start = strtotime('-' . $dow . ' days', $today); // előző vasárnap 00:00
    $week_end = strtotime('+7 days', $week_start); // következő hét vasárnap 00:00

    if ($date >= $week_start && $date <= $week_end) {
        $weekday = $napok[(int)date('w', $date)];
        $month = $_honapok[(int)date('n', $date)][0].".";
        $day = date('j.', $date);
        return $weekday . ' (' . $month . ' ' . $day . ')' . ($showTime ? ' ' . date($timeFormat, $date) : '');
    }

    // Egyéb esetben csak a hónap, nap és idő
    $month = $_honapok[(int)date('n', $date)][0].".";
    $day = date('j.', $date);
    $weekday = $napok[(int)date('w', $date)];
    $time = date($timeFormat, $date);

    // Pl.: Júl. 16., szerda 14:30
    return $month . ' ' . $day . ', ' . $weekday . ($showTime ? ' ' . $time : '');
}

function twig_translate($text) {
    
    $text = Translator::translate($text);
    
    return $text;
}

