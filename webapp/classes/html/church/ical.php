<?php

namespace Html\Church;

use Eloquent\CalMass;
use Eloquent\Church as ChurchModel;

class Ical extends \Html\Html {

    public function __construct($path) {
        // Expect path like [id]
        if (empty($path[0]) || !is_numeric($path[0])) {
            throw new \Exception('Hiányzó templom azonosító az iCal generáláshoz.');
        }
        $tid = (int)$path[0];

        // Fetch church and masses
        $church = ChurchModel::find($tid);
        $masses = CalMass::where('church_id', $tid)->get()->toArray();

        $ical = $this->generateIcal($masses, $church, $tid);

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

    private function generateIcal(array $masses, $church, int $churchId): string {
        $lines = [];
        $lines[] = 'BEGIN:VCALENDAR';
        $lines[] = 'VERSION:2.0';
        $lines[] = 'PRODID:-//miserend.hu//Calendar//HU';
        $lines[] = 'CALSCALE:GREGORIAN';
        $lines[] = 'METHOD:PUBLISH';

        $dtstamp = gmdate('Ymd\\THis\\Z');
        $counter = 1;

        foreach ($masses as $m) {
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
