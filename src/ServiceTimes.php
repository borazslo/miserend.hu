<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * A miserendek és az OSM/open_hours közötti kapcsolatot biztosítja.
 */
class ServiceTimes
{
    /*
        To test with:
        2141 - Mar 26-Oct 29: Mo-Th,Fr[1] 19:00, Su 08:30; Oct 30-Mar 25: Mo-Th,Fr[1] 17:00, Su 08:30
        31 - Su[1] 07:30, Su[2-4] 08:00 "igeliturgia"
        3919
        4212 - Apr 01-Sep 30: Fr[1],Sa 18:00; Oct 01-Mar 31: Fr[1],Sa 17:00
         1120 vs 1037
    */

    public function loadMasses(int $tid, $args = [])
    {
        $string = '';

        // Load all Times of Church
        $results = DB::table('misek')
            ->where('torles', '0000-00-00 00:00:00')
            ->where('tid', $tid)
            ->orderBy('weight', 'DESC')
            ->orderBy('idoszamitas')
            ->orderBy('nap')
            ->orderBy('ido')
            ->get();

        global $milyen;
        $days = $_days = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
        $months = ['Jan', 'Feb', 'Mar', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $period = '';
        $nap = '';
        $schedule = [];
        foreach ($results as $row) {
            foreach (['idoszamitas', 'weight', 'tol', 'ig', 'tmp_datumtol', 'tmp_relation', 'tmp_datumig'] as $field) {
                $schedule[$row->idoszamitas][$field] = $row->$field;
            }

            $day = [];
            foreach (['ido', 'nyelv', 'milyen', 'megjegyzes'] as $field) {
                if ($row->$field != '') {
                    $day[$field] = $row->$field;
                }
            }

            $d = $days[$row->nap > 0 ? $row->nap - 1 : 6];
            if ($row->tol == $row->ig) {
                $d = 'Mo-Su';
            }
            if (isset($row->nap2)) {
                if ('pt' == $row->nap2) {
                    $d = 'week 1-53/2 '.$d;
                } elseif ('ps' == $row->nap2) {
                    $d = 'week 2-53/2 '.$d;
                } elseif (0 != $row->nap2) {
                    $d .= '['.$row->nap2.']';
                }
            }
            $schedule[$row->idoszamitas]['days'][$d][] = $day;
        }

        // Optimze days
        foreach ($schedule as &$periods) {
            $hashs = [];
            foreach ($periods['days'] as $day => $times) {
                if (preg_match('/^('.implode('|', $_days).')/', $day)) {
                    $hashs[md5(json_encode($times))][] = $day;
                }
            }

            foreach ($hashs as $c => $days) {
                if (\count($days) > 1) {
                    $saveDay = $periods['days'][$days[0]];
                    $dayPattern = '';
                    // printr($days);
                    foreach ($days as $k => $day) {
                        if ($k + 1 == \count($days)) {
                            $isLast = true;
                        } else {
                            $isLast = false;
                        }

                        $lastDayKey = isset($days[$k - 1]) ? array_search($days[$k - 1], $_days) : false;
                        $currentDayKey = array_search($days[$k], $_days);
                        $nextDayKey = isset($days[$k + 1]) ? array_search($days[$k + 1], $_days) : false;

                        isset($days[$k - 1]) ? preg_match(
                            '/('.implode('|', $_days).')\[([0-9]{1})\]/',
                            $days[$k - 1],
                            $lastConstrained
                        ) : $lastConstrained = false;
                        preg_match('/('.implode('|', $_days).')\[([0-9]{1})\]/', $days[$k], $currentConstrained);
                        isset($days[$k + 1]) ? preg_match(
                            '/('.implode('|', $_days).')\[([0-9]{1})\]/',
                            $days[$k + 1],
                            $nextConstrained
                        ) : $nextConstrained = false;

                        // First always!
                        if (0 == $k) {
                            if (!$currentConstrained) {
                                $dayPattern .= $day;
                            } else {
                                if (!$nextConstrained) {
                                    $dayPattern .= $day;
                                } else {
                                    $dayPattern .= $currentConstrained[1].'['.$currentConstrained[2];
                                    if ($isLast) {
                                        $dayPattern .= 's]';
                                    }
                                }
                            }
                        // In the middle   AND end
                        } else {
                            if ($k < \count($days)) {
                                if ($lastConstrained) {
                                    if ($currentConstrained && $lastConstrained[1] == $currentConstrained[1]) {
                                        if ($lastConstrained[2] + 1 == $currentConstrained[2]
                                            && isset($nextConstrained[2])
                                            && $currentConstrained[2] + 1 == $nextConstrained[2]) {
                                        } else {
                                            if ($lastConstrained[2] + 1 == $currentConstrained[2]) {
                                                $dayPattern .= '-'.$currentConstrained[2];
                                            } else {
                                                $dayPattern .= ','.$currentConstrained[2];
                                            }
                                        }

                                        if ($isLast || !$nextConstrained) {
                                            $dayPattern .= ']';
                                        }
                                    } else {
                                        if (']' != substr($dayPattern, -1)) {
                                            $dayPattern .= ']';
                                        }   // Kár hogy ez kell. Nem tudo mikor és miért kell. Lásd: 1120 vs 1037
                                        $dayPattern .= ',';

                                        if ($currentConstrained) {
                                            $dayPattern .= $currentConstrained[1].'['.$currentConstrained[2];
                                            if ($isLast) {
                                                $dayPattern .= ']';
                                            }
                                        } else {
                                            $dayPattern .= $day;
                                        }
                                    }
                                } else {
                                    if ($currentConstrained) {
                                        $dayPattern .= ','.$currentConstrained[1].'['.$currentConstrained[2];
                                        if ($isLast) {
                                            $dayPattern .= ']';
                                        }
                                    } else {
                                        if ($lastDayKey + 1 == $currentDayKey && $currentDayKey + 1 == $nextDayKey) {
                                        } else {
                                            if ($lastDayKey + 1 == $currentDayKey && $currentDayKey + 1 != $nextDayKey) {
                                                $dayPattern .= '-'.$day;
                                            } else {
                                                $dayPattern .= ','.$day;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        unset($periods['days'][$day]);
                    }

                    // echo $dayPattern;

                    $periods['days'][$dayPattern] = $saveDay;
                }
            }
        }

        // printr($periods);

        // Generate OSM string
        global $milyen, $nyelv;
        $string = '';
        foreach ($schedule as $key => $periodss) {
            if (1 == \count($schedule) && '01-01' == $periodss['tmp_datumtol'] && '12-31' == $periodss['tmp_datumig']) {
            } else {
                if ($periodss['tmp_datumtol'] == $periodss['tmp_datumig']) {
                    $string .= date('M d', strtotime('2023-'.$periodss['tmp_datumtol'])).': ';
                } else {
                    $string .= date('M d', strtotime('2023-'.$periodss['tmp_datumtol'])).'-'.date(
                        'M d',
                        strtotime('2023-'.$periodss['tmp_datumig'])
                    ).': ';
                }
            }

            foreach ($periodss['days'] as $key => $times) {
                // if(count($periodss['days']) == 1 AND $key == 'Mo-Su') { } else  // Ez logikus lenne, de a feldolgozó nem szereti
                $string .= $key.' ';
                foreach ($times as $time) {
                    $string .= date('H:i', strtotime($time['ido']));

                    // add comment(s)

                    $comments = [];

                    if (isset($time['nyelv'])) {
                        if (isset($nyelv[$time['nyelv']])) {
                            $comments[] = $nyelv[$time['nyelv']]['description'];
                        } else {
                            $comments[] = $time['nyelv'];
                        }
                    }
                    if (isset($time['milyen'])) {
                        if (isset($milyen[$time['milyen']])) {
                            $comments[] = $milyen[$time['milyen']]['name'];
                        } else {
                            $comments[] = $time['milyen'];
                        }
                    }
                    if (isset($time['megjegyzes'])) {
                        $comments[] = $time['megjegyzes'];
                    }

                    if (\count($comments) > 0) {
                        $string .= ' "'.implode(', ', $comments).'"';
                    }

                    $string .= ',';
                }
                $string = preg_replace('/(\,(| ))$/', '', $string);
                $string .= ', ';
            }
            $string = preg_replace('/(\,(| ))$/', '', $string);
            $string .= '; ';
        }
        $string = preg_replace('/(\;(| ))$/', '', $string);

        if (isset($args['skipvalidation']) && (true == $args['skipvalidation'] || 1 == $args['skipvalidation']) || \in_array(
            'skipvalidation',
            $args
        )) {
        } else {
            $this->validate($string);
        }

        $this->string = $string;
    }

    public function validate(string $string)
    {
        $exp = urlencode(str_replace("\n", '', $string));

        $openingh = new Legacy\Services\ExternalApi\OpeninghApi();

        try {
            $openingh->validate($string);
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();

            return false;
        }

        $this->linkForDetails = $openingh->linkForDetails;
    }
}
