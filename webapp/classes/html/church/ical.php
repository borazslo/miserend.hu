<?php

namespace Html\Church;

use Eloquent\CalMass;
use Eloquent\Church ;

class Ical extends \Html\Html {

    public function __construct($path) {
        // Expect path like [id]
        if (empty($path[0]) || !is_numeric($path[0])) {
            throw new \Exception('Hiányzó templom azonosító az iCal generáláshoz.');
        }
        $tid = (int)$path[0];

        // Fetch church and masses
        $church = Church::find($tid);

        $_SERVER['REQUEST_METHOD'] = 'GET'; // Sajnos a Periods az még nem erre van kitalálva, ezért kell itt ez
        $periodsClass = new \html\calendar\Periods('generate','array');
        $generatedPeriods = $periodsClass->result;
        $masses = CalMass::where('church_id', $tid)->get()->toArray();

                
        $ical = $this->generateIcal($masses, $generatedPeriods, $church, $tid);

        // Output headers for .ics
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename="miserend_church_' . $tid . '.ics"');

        echo $ical;
        exit;
    }

    private function formatIcsDate(string $iso): string {
        // Accept YYYY-MM-DD or ISO datetime
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $iso)) {
            return str_replace('-', '', $iso);
        }
        try {
            $dt = new \DateTime($iso);
            return $dt->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\\THis\\Z');
        } catch (\Exception $e) {
            return preg_replace('/[^0-9T]/', '', $iso) . 'Z';
        }
    }

    private function escapeString($s) {
        $s = str_replace(["\\", "\n", "\r", ";", ","], ["\\\\", "\\n", "\\r", "\\;", "\\,"], $s);
        return $s;
    }

    private function rruleToString($rrule) {
        if (!$rrule || !is_array($rrule)) return '';
        $parts = [];
        foreach ($rrule as $k => $v) {
            if ($k === 'dtstart') continue;
            if ($k === 'until') {
                $parts[] = 'UNTIL=' . $this->formatIcsDate($v);
                continue;
            }
            if (is_array($v)) {
                $parts[] = strtoupper($k) . '=' . implode(',', array_map(function($x){ return strtoupper((string)$x); }, $v));
                continue;
            }
            if (strtolower($k) === 'freq') {
                $parts[] = 'FREQ=' . strtoupper((string)$v);
            } else {
                $parts[] = strtoupper($k) . '=' . (string)$v;
            }
        }
        return implode(';', $parts);
    }

    private function createCalendarEvent($mass, $periods, $deletedDates) {
        $events = [];

        printr($periods);
        exit;

        // normalize keys: support both snake_case and camelCase
        $get = function($k, $default=null) use ($mass) {
            if (isset($mass[$k])) return $mass[$k];
            $alt = str_replace('_', '', $k);
            if (isset($mass[$alt])) return $mass[$alt];
            $alt2 = preg_replace_callback('/([A-Z])/', function($m){ return '_' . strtolower($m[1]); }, $k);
            if (isset($mass[$alt2])) return $mass[$alt2];
            return $default;
        };

        $rrule = $get('rrule', null);
        if (is_string($rrule)) {
            $decoded = json_decode($rrule, true);
            if (json_last_error() === JSON_ERROR_NONE) $rrule = $decoded;
        }

        $exdate = $get('exdate', null);
        if (is_string($exdate)) {
            $decoded = json_decode($exdate, true);
            if (json_last_error() === JSON_ERROR_NONE) $exdate = $decoded;
        }

        $experiod = $get('experiod', null);
        if (is_string($experiod)) {
            $decoded = json_decode($experiod, true);
            if (json_last_error() === JSON_ERROR_NONE) $experiod = $decoded;
        }

        $duration = $get('duration', null);
        if (is_string($duration)) {
            $decoded = json_decode($duration, true);
            if (json_last_error() === JSON_ERROR_NONE) $duration = $decoded;
        }

        // recurring case: rrule + period_id
        $periodId = $get('period_id', $get('periodId', null));
        if (!empty($rrule) && !empty($periodId)) {
            // load generated periods for this period id
            $genPeriods = \Eloquent\CalGeneratedPeriod::where('period_id', $periodId)->get()->toArray();
            foreach ($genPeriods as $gp) {
                // clone rrule
                $r = is_array($rrule) ? $rrule : (array)$rrule;
                // attach exrule from experiod if present
                $exrule = [];
                if (!empty($experiod) && is_array($experiod)) {
                    foreach ($experiod as $exPid) {
                        $fps = \Eloquent\CalGeneratedPeriod::where('period_id', $exPid)->get()->toArray();
                        foreach ($fps as $fp) {
                            // dtstart: fp.start_date + time part of original rrule.dtstart
                            $timeSuffix = '';
                            if (!empty($r['dtstart']) && strlen($r['dtstart']) > 10) {
                                $timeSuffix = substr($r['dtstart'], 10);
                            }
                            $exrule[] = [
                                'dtstart' => ($fp['start_date'] ?? '') . $timeSuffix,
                                'until' => ($fp['end_date'] ?? ''),
                                'freq' => 'daily'
                            ];
                        }
                    }
                }

                // set rrule dtstart to period start + time suffix
                $timeSuffix = '';
                if (!empty($r['dtstart']) && strlen($r['dtstart']) > 10) {
                    $timeSuffix = substr($r['dtstart'], 10);
                }
                $r['dtstart'] = ($gp['start_date'] ?? '') . $timeSuffix;
                if (!empty($r['until'])) {
                    $r['until'] = ($gp['end_date'] ?? $r['until']);
                }

                $evt = [];
                $evt['id'] = $get('id', null);
                $evt['title'] = $get('title', '');
                $evt['rrule'] = $r;
                if (!empty($duration)) $evt['duration'] = $duration;
                if (!empty($exdate)) $evt['exdate'] = $exdate;
                if (!empty($exrule)) $evt['exrule'] = $exrule;
                $evt['startDate'] = $r['dtstart'];
                $evt['color'] = $gp['color'] ?? null;
                $evt['comment'] = $get('comment', '');

                $events[] = $evt;
            }
        } else {
            // single event fallback
            $start = $get('startDate', $get('start_date', null));
            if (empty($start)) return $events;
            $evt = [];
            $evt['id'] = $get('id', null);
            $evt['title'] = $get('title', '');
            $evt['startDate'] = $start;
            $evt['rrule'] = [
                'dtstart' => $start,
                'freq' => 'daily',
                'count' => 1
            ];
            $evt['exdate'] = is_array($exdate) ? $exdate : [];
            $evt['exrule'] = [];
            if (!empty($duration)) $evt['duration'] = $duration;
            $evt['comment'] = $get('comment', '');
            $events[] = $evt;
        }

        return $events;
    }

    private function generateIcal(array $masses, $periods, $church, int $churchId): string {
        $lines = [];
        $lines[] = 'BEGIN:VCALENDAR';
        $lines[] = 'VERSION:2.0';
        $lines[] = 'PRODID:-//miserend.hu//Calendar//HU';
        $lines[] = 'CALSCALE:GREGORIAN';
        $lines[] = 'METHOD:PUBLISH';

        $dtstamp = gmdate('Ymd\\THis\\Z');
        $counter = 1;

        $tmp = [];
        foreach ($masses as $m) { 
            $tmp = array_merge($tmp, $this->createCalendarEvent($m, $periods, false));
        }

        foreach ($tmp as $m) {



            if (empty($m['startDate'])) continue;
            $evStart = $m['startDate'];

            $lines[] = 'BEGIN:VEVENT';
            $uid = 'miserend-' . ($m['id'] ?? $counter++ ) . '@miserend.hu';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTAMP:' . $dtstamp;

            // DTSTART
            $lines[] = 'DTSTART:' . $this->formatIcsDate($evStart);

            // DTEND from duration if provided
            if (!empty($m['duration'])) {
                try {
                    $dur = $m['duration'];
                    $d = new \DateTime($evStart);
                    if (!empty($dur['days'])) $d->modify('+' . (int)$dur['days'] . ' days');
                    if (!empty($dur['hours'])) $d->modify('+' . (int)$dur['hours'] . ' hours');
                    if (!empty($dur['minutes'])) $d->modify('+' . (int)$dur['minutes'] . ' minutes');
                    $lines[] = 'DTEND:' . $d->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\\THis\\Z');
                } catch (\Exception $e) {
                    // ignore
                }
            }

            // RRULE
            if (!empty($m['rrule'])) {
                $rr = $this->rruleToString($m['rrule']);
                if ($rr) $lines[] = 'RRULE:' . $rr;
            }

            // EXDATE
            if (!empty($m['exdate']) && is_array($m['exdate'])) {
                $fixed = array_map(function($d){ return $this->formatIcsDate($d); }, $m['exdate']);
                $lines[] = 'EXDATE:' . implode(',', $fixed);
            }

            $lines[] = 'SUMMARY:' . $this->escapeString($m['title'] ?? '');
            if (!empty($m['comment'])) $lines[] = 'DESCRIPTION:' . $this->escapeString($m['comment']);
            $lines[] = 'X-MASS-ID:' . ($m['id'] ?? '');
            if (!empty($m['color'])) $lines[] = 'COLOR:' . $m['color'];

            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        return implode("\r\n", $lines);
    }
}
