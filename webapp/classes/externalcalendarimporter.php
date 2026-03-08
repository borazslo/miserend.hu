<?php

/**
 * Detect and convert content to UTF-8
 * Handles charset from headers and validates UTF-8
 *
 * @param string $content Raw content from download
 * @param string $contentType HTTP Content-Type header (optional)
 * @return string UTF-8 encoded content
 * @throws \Exception
 */
function ensureUtf8($content, $contentType = '') {
    // 1. Extract charset from Content-Type header if provided
    $charset = 'UTF-8';
    if (!empty($contentType) && preg_match('/charset\s*=\s*([^\s;]+)/i', $contentType, $matches)) {
        $charset = strtoupper(trim($matches[1], '"\''));
    }
    
    // 2. Remove UTF-8 BOM if present
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $content = substr($content, 3);
    }
    
    // 3. Convert to UTF-8 if not already
    if ($charset !== 'UTF-8' && $charset !== 'UTF8') {
        $content = iconv($charset, 'UTF-8//IGNORE', $content);
        if ($content === false) {
            throw new \Exception("Failed to convert charset from $charset to UTF-8");
        }
    }
    
    // 4. Validate and sanitize UTF-8
    if (!mb_check_encoding($content, 'UTF-8')) {
        // Remove invalid UTF-8 sequences
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
    }
    
    return $content;
}

/**
 * Sanitize string for database storage
 * Ensures valid UTF-8 encoding without null bytes or control characters
 *
 * @param string $text
 * @return string
 */
function sanitizeUtf8($text) {
    // Remove null bytes
    $text = str_replace("\0", '', $text);
    
    // Validate/fix UTF-8
    if (!mb_check_encoding($text, 'UTF-8')) {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    }
    
    return trim($text);
}

/**
 * External Calendar Importer
 * Imports masses from Google Calendar iCalendar (ICS) format
 */
class ExternalCalendarImporter {

    /**
     * Main cron entry point: Import all active external calendars
     * This is called daily from the cron system
     */
    public static function importAllExternalCalendars() {
        set_time_limit(600);
        
        try {
            $calendars = \Eloquent\ExternalCalendar::where('active', 1)->get();
            
            if ($calendars->isEmpty()) {
                echo "ℹ No active external calendars to import.<br>\n";
                return;
            }
            
            foreach ($calendars as $calendar) {
                try {
                    echo "▶ Importing: Church #{$calendar->church_id} - {$calendar->name}...<br>\n";
                    
                    self::importFromUrl($calendar->url, $calendar->church_id);
                    
                    // Update last_import_at timestamp
                    $calendar->last_import_at = \Carbon\Carbon::now();
                    $calendar->save();
                    
                    echo "✓ Import successful: Church #{$calendar->church_id}<br>\n";
                } catch (\Exception $e) {
                    echo "✗ Import failed for Church #{$calendar->church_id}: " . $e->getMessage() . "<br>\n";
                    \Log::error("ExternalCalendarImporter: Church #{$calendar->church_id} - " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            echo "✗ Critical error in importAllExternalCalendars: " . $e->getMessage() . "<br>\n";
            \Log::error("ExternalCalendarImporter: " . $e->getMessage());
        }
    }

    /**
     * Import a single calendar from URL
     * 
     * Flow:
     * 1. Download ICS content from URL
     * 2. Delete existing external calendar masses (period_id IS NULL)
     * 3. Parse iCalendar and create new CalMass objects
     * 4. Refresh Elasticsearch index
     */
    private static function importFromUrl($url, $churchId) {
        // 1. Download ICS content
        $icsContent = self::downloadIcsFromUrl($url);
        
        if (empty($icsContent)) {
            throw new \Exception("Failed to download iCalendar from URL: " . substr($url, 0, 50) . "...");
        }
        
        // 2. Delete existing external calendar masses
        // (masses imported from external calendars have period_id = NULL)
        \Eloquent\CalMass::where('church_id', $churchId)
            ->whereNull('period_id')
            ->delete();
        
        // 3. Parse and create new CalMass objects
        $eventsCreated = self::parseAndCreateCalMasses($icsContent, $churchId);

        echo "  Created $eventsCreated masses from iCalendar<br>\n";

        // 4. Refresh Elasticsearch index for this church
        $years = [2025, 2026, 2027, 2028];
        \ExternalApi\ElasticsearchApi::updateMasses($years, [$churchId]);

        echo "  Elasticsearch index updated<br>\n";
    }

    /**
     * Download ICS content from URL using ExternalApi
     */
    private static function downloadIcsFromUrl($url) {
        try {
            $api = new \ExternalApi\ExternalApi();
            $api->cache = '1 day';
            $api->query = $url;
            $api->format = 'json';  // Temporarily set to avoid strict format checking
            $api->strictFormat = false;
            
            // Download raw ICS content
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'miserend.hu/ExternalCalendarImporter');
            
            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200 || empty($content)) {
                throw new \Exception("HTTP $httpCode: Failed to download from $url");
            }
            
            return $content;
        } catch (\Exception $e) {
            throw new \Exception("Download failed: " . $e->getMessage());
        }
    }

    /**
     * Parse iCalendar content and create CalMass objects
     * Simple custom parser for iCalendar format
     */
    private static function parseAndCreateCalMasses($icsContent, $churchId) {
        $eventsCreated = 0;
        
        try {
            // Parse iCalendar using custom parser
            $events = self::parseIcsEvents($icsContent);
            
            if (empty($events)) {
                return 0;  // No events in calendar
            }
            
            try {
                $deleted = \Eloquent\CalMass::where('church_id', $churchId)
                    ->where('comment', 'External calendar import')
                    ->delete();
                echo "  Deleted {$deleted} existing external masses for church #{$churchId}<br>\n";
            } catch (\Exception $e) {
                echo "  ⚠ Failed to delete existing masses for church #{$churchId}: " . $e->getMessage() . "<br>\n";
                \Log::error("ExternalCalendarImporter: failed deleting previous masses for church #{$churchId} - " . $e->getMessage());
            }

            // Iterate through parsed events
            foreach ($events as $event) {
                try {
                    $calMass = self::createCalMassFromEvent($event, $churchId);
                    if($calMass) {
                        $calMass->save();
                        $eventsCreated++;
                    }
                } catch (\Exception $e) {
                    echo "  ⚠ Error creating CalMass: " . $e->getMessage() . "<br>\n";
                    // Continue with next event instead of failing entire import
                }
            }
        } catch (\Exception $e) {
            throw new \Exception("iCalendar parsing failed: " . $e->getMessage());
        }
        
        return $eventsCreated;
    }

    /**
     * Custom iCalendar parser - extracts VEVENT blocks and their properties
     * Returns array of event objects with SUMMARY, DTSTART, DTEND, DURATION, RRULE properties
     */
    private static function parseIcsEvents($icsContent) {
        $events = [];
        
        // Split by VEVENT blocks
        $pattern = '/BEGIN:VEVENT\s*(.*?)\s*END:VEVENT/is';
        if (!preg_match_all($pattern, $icsContent, $matches)) {
            return [];
        }
        
        foreach ($matches[1] as $eventData) {
            $event = (object) [
                'SUMMARY' => null,
                'DTSTART' => null,
                'DTEND' => null,
                'DURATION' => null,
                'RRULE' => null,
                'EXDATES' => [],  // To store EXDATE values if needed in the future
            ];
            
            // Parse EXDATE (capture the full parameter+value part, e.g. ;TZID=Europe/Budapest:20241222T183000)
            if (preg_match_all('/^EXDATE\;\b(.*)$/im', $eventData, $exdateMatches)) {
                foreach ($exdateMatches[1] as $exdate) {
                    // Store the whole right-hand side (including parameters and the ':' + datetime)
                    $event->EXDATE[] = trim($exdate);
                }
            }

            // Parse SUMMARY
            if (preg_match('/^SUMMARY\:\b(.*)$/im', $eventData, $m)) {
                $event->SUMMARY = trim(preg_replace('/\\\\,/', ', -', $m[1]));
            }
            
            // Parse DTSTART
            if (preg_match('/^DTSTART(\;|\:)\b(.*)$/im', $eventData, $m)) {
                $event->DTSTART = trim($m[2]);
            }
            
            // Parse DTEND
            if (preg_match('/^DTEND(\;|\:)\b(.*)$/im', $eventData, $m)) {
                $event->DTEND = trim($m[2]);
            }
            
            // Parse DURATION
            if (preg_match('/^DURATION\s*:\s*(.*)$/im', $eventData, $m)) {
                $event->DURATION = trim($m[1]);
            }
            
            // Parse RRULE
            if (preg_match('/^RRULE\s*:\s*(.*)$/im', $eventData, $m)) {
                $event->RRULE = trim($m[1]);
            }
            
            $events[] = $event;
        }
        
        return $events;
    }

    /**
     * Convert iCalendar VEVENT to CalMass object
     */
    private static function createCalMassFromEvent($event, $churchId) {
        
        $summary = isset($event->SUMMARY) ? (string)$event->SUMMARY : 'External Calendar Event';
        $startDate = self::extractStartDate($event);        
        $duration = self::extractDuration($event);
        $rrule = null;

        if($event->RRULE) {
            $rrule = self::extractRRule($event);
            $rruleString = $rrule ? json_encode($rrule) : 'none';
            if($rrule and !isset($rrule['dtstart'])) {
                echo "  ⚠ Warning: RRULE is missing DTSTART reference, skipping RRULE: " . json_encode($rrule) . "<br>\n";    
                $rrule = null;
            }
        }
    
        if(!empty($event->EXDATE)) {            
            $exdates = self::extractExDates($event);
        }

        $calMass = \Eloquent\CalMass::make([
            'church_id' => $churchId,
            'title' => $summary,
            'start_date' => $startDate,
            'rrule' => $rrule,
            'exdate' => !empty($exdates) ? $exdates : null,
            'duration' => $duration,
            'rite' => 'ROMAN_CATHOLIC',  // Default rite
            'lang' => 'hu',     // Default language
            'period_id' => null,  // External calendars don't belong to periods
            'comment' => 'External calendar import',
        ]);

        return $calMass;
    }

    /**
     * Extract EXDATE from iCalendar event
     * Returns array of ISO 8601 datetime strings
     */
    private static function extractExDates($event) {
        if (!isset($event->EXDATE)) {
            return [];
        }

        $exdates = [];
        foreach ($event->EXDATE as $exdate) {            
            try {
                $exdate = trim($exdate);

                // If format contains TZID=Zone:YYYYMMDDTHHMM[SS]
                if (preg_match('/TZID=([^:;]+):(.+)$/i', $exdate, $m)) {
                    $tzid = $m[1];
                    $dtStr = $m[2];

                    // Date-only (YYYYMMDD)
                    if (preg_match('/^\d{8}$/', $dtStr)) {
                        $exdates[] = substr($dtStr, 0, 4) . '-' . substr($dtStr, 4, 2) . '-' . substr($dtStr, 6, 2);
                        continue;
                    }

                    // If seconds missing (YYYYMMDDTHHMM), append seconds
                    if (preg_match('/^\d{8}T\d{4}$/', $dtStr)) {
                        $dtStr .= '00';
                    }

                    // Try to create DateTime in the given TZ and convert to UTC
                    $dt1 = \DateTime::createFromFormat('Ymd\THis', $dtStr, new \DateTimeZone($tzid));
                    $dt = \DateTime::createFromFormat('Ymd\THis', $dtStr);
                    if ($dt === false) {
                        // Fallback: try generic parser and take date part
                        $iso = self::parseIcsDateTime($dtStr);
                        $exdates[] = substr($iso, 0, 10);
                        continue;
                    }

                    $dt->setTimezone(new \DateTimeZone('UTC'));
                    $exdates[] = $dt->format('Y-m-d\TH:i:s');
                    $exdates[] = $dt1->format('Y-m-d\TH:i:s');
                    $exdates[] = $dt1->format('Y-m-d');
                    continue;
                }

                // No TZID present: parse normally and return date part (YYYY-MM-DD)
                $iso = self::parseIcsDateTime($exdate);
                $exdates[] = substr($iso, 0, 10);
            } catch (\Exception $e) {
                // Best-effort fallback: try generic parse and extract date, otherwise skip
                try {
                    $iso = self::parseIcsDateTime($exdate);
                    $exdates[] = substr($iso, 0, 10);
                } catch (\Exception $ignored) {
                    throw new \Exception("Failed to parse EXDATE: $exdate - " . $e->getMessage());
                }
            }
            $exdates[] = self::parseIcsDateTime($exdate);
        }
        return $exdates;
    }

    /**
     * Extract DTSTART from iCalendar event
     * Returns ISO 8601 datetime string
     */
    private static function extractStartDate($event) {
        if (!isset($event->DTSTART)) {
            throw new \Exception("DTSTART is missing from event: ".json_encode($event));
        }
        
        $dtstart = $event->DTSTART;
        // Parse iCalendar datetime format (YYYYMMDDTHHMMSS or YYYYMMDD)
        return self::parseIcsDateTime($dtstart);
    }

    /**
     * Extract DURATION or calculate from DTEND
     * Returns JSON: {"hours": int, "minutes": int}
     */
    private static function extractDuration($event) {
        try {
            // Try DURATION field first
            if (isset($event->DURATION)) {
                return self::parseDurationString($event->DURATION);
            }
            
            // Try DTSTART and DTEND
            if (isset($event->DTSTART) && isset($event->DTEND)) {
                $start = self::parseIcsDateTime($event->DTSTART);
                $end = self::parseIcsDateTime($event->DTEND);
                
                $startDt = new \DateTime($start);
                $endDt = new \DateTime($end);
                $diff = $endDt->diff($startDt);
                
                $hours = $diff->h + ($diff->days * 24);
                $minutes = $diff->i;
                return ['hours' => $hours, 'minutes' => $minutes];
            }
            
            // Default: 1 hour
            return ['hours' => 1, 'minutes' => 0];
        } catch (\Exception $e) {
            // On any duration parsing error, default to 1 hour
            return ['hours' => 1, 'minutes' => 0];
        }
    }

    /**
     * Extract RRULE from iCalendar event
     * Returns JSON: {"rule": "FREQ=WEEKLY;BYDAY=SU,MO;..."}
     */
    private static function extractRRule($event) {
        if (!isset($event->RRULE)) {
            return null;
        }
        
        try {
            $ruleArray = [];
            $rrule = (string)$event->RRULE;
            $rules = explode(';', $rrule);
            foreach ($rules as $rule) {
                [$key, $value] = explode('=', $rule);
                if ($value) {
                    if($key == 'FREQ') $value = strtolower($value);
                    
                    if($key == 'BYDAY' AND preg_match('/^(SU|MO|TU|WE|TH|FR|SA)(,(SU|MO|TU|WE|TH|FR|SA))*$/', $value)) {
                        // Convert BYDAY=SU,MO to ["SU", "MO"]                        
                        $ruleArray["byweekday"] = explode(',', $value);
                    }

                    else if($key === 'BYDAY' AND preg_match('/^(1|2|3|4|5|-1)(SU|MO|TU|WE|TH|FR|SA)*$/', $value, $match)) {
                        $ruleArray["bysetpos"] = (int)$match[1];
                        $ruleArray["byweekday"] = $match[2];                        
                    }
                    else {
                        
                        if($key == "BYDAY" OR $key == "BYMONTHDAY") {
                        throw new \Exception("Unsupported RRULE parameter: $key - skipping RRULE: ". $rrule);
                        }

                        $ruleArray[strtolower($key)] = $value;                
                    }
                }
            }
            if(!empty($ruleArray)) {
                $ruleArray['dtstart'] = self::parseIcsDateTime($event->DTSTART);  // Include DTSTART for reference
            }

            // Convert dtstart and until FROM 20220913T183000 or 20220913T183000 TO 2026-12-23T23:59:00            
            if(isset($ruleArray['until'])) {
                $ruleArray['until'] = self::parseIcsDateTime($ruleArray['until']);
            }
            return $ruleArray;
        } catch (\Exception $e) {
            echo "  ⚠ Failed to parse RRULE: " . $e->getMessage() . "<br>\n";
            return null;
        }
    }

    /**
     * Parse iCalendar datetime string with support for TZID parameter
     * Handles formats like:
     * - TZID=Europe/Budapest:20221201T060000
     * - 20221201T060000
     * - 20221201T060000Z
     * - YYYY-MM-DDTHH:MM:SS
     *
     * Returns ISO 8601 datetime string in UTC (Y-m-d\TH:i:s format)
     */
    private static function parseIcsDateTime($dateStr) {
        $dateStr = trim($dateStr);        

        $tzid = null;
        $dtString = $dateStr;
        
        // Extract TZID parameter if present (e.g., TZID=Europe/Budapest:20221201T060000)
        if (preg_match('/^TZID=([^:;]+):(.+)$/', $dateStr, $matches)) {
            $tzid = $matches[1];
            $dtString = $matches[2];
        }
        
        // Handle date-only format (YYYYMMDD)
        if (strlen($dtString) == 8 && ctype_digit($dtString)) {
            $year = substr($dtString, 0, 4);
            $month = substr($dtString, 4, 2);
            $day = substr($dtString, 6, 2);
            
            // Create Carbon instance in the specified timezone or default to UTC
            try {
                if ($tzid) {
                    $carbon = \Carbon\Carbon::createFromFormat('Y-m-d', "$year-$month-$day", $tzid);
                    return $carbon->setTimezone('Europe/Budapest')->format('Y-m-d\TH:i:s');
                } else {
                    return "$year-$month-$day" . "T00:00:00";
                }
            } catch (\Exception $e) {
                return "$year-$month-$day" . "T00:00:00";
            }
        }
        
        // Handle datetime format (YYYYMMDDTHHMMSS or YYYYMMDDTHHMMSSZ)
        if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})Z?$/', $dtString, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
            $hour = $matches[4];
            $minute = $matches[5];
            $second = $matches[6];
            
            try {
                // Create Carbon instance with the datetime string
                $dateTimeStr = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);
                
                if ($tzid) {
                    // Create in the specified timezone and convert to UTC
                    $carbon = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateTimeStr, $tzid);
                    return $carbon->setTimezone('Europe/Budapest')->format('Y-m-d\TH:i:s');
                } else {
                    // No timezone specified, treat as UTC
                    $carbon = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dateTimeStr, 'Europe/Budapest');
                    return $carbon->format('Y-m-d\TH:i:s');
                }
            } catch (\Exception $e) {
                // Fallback: return formatted without timezone conversion
                return "$year-$month-$day" . "T$hour:$minute:$second";
            }
        }
        
        // If already in ISO 8601 format (YYYY-MM-DDTHH:MM:SS, optionally Z or ±HH:MM), parse and return in UTC
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+\-]\d{2}:\d{2})?$/', $dtString)) {
            try {
                $carbon = \Carbon\Carbon::parse($dtString);
                return $carbon->setTimezone('Europe/Budapest')->format('Y-m-d\TH:i:s');
            } catch (\Exception $e) {
                return $dtString;
            }
        }
        
        // Fallback: try to parse with Carbon
        try {
            if ($tzid) {
                $carbon = \Carbon\Carbon::parse($dtString, $tzid);
                return $carbon->setTimezone('Europe/Budapest')->format('Y-m-d\TH:i:s');
            } else {
                $carbon = \Carbon\Carbon::parse($dtString);
                return $carbon->setTimezone('Europe/Budapest')->format('Y-m-d\TH:i:s');
            }
        } catch (\Exception $e) {
            throw new \Exception("Unable to parse datetime: $dateStr - " . $e->getMessage());
        }
    }

    /**
     * Parse iCalendar duration string (e.g., "PT1H30M" means 1 hour 30 minutes)
     * Returns JSON: {"hours": int, "minutes": int}
     */
    private static function parseDurationString($durationStr) {
        $durationStr = trim($durationStr);
        
        // Initialize hours and minutes
        $hours = 0;
        $minutes = 0;
        
        // Parse ISO 8601 duration format: P[n]D[T[n]H[n]M[n]S]
        // Examples: PT1H, PT30M, PT1H30M, P1DT2H
        
        // Extract time part (after T)
        if (preg_match('/T(.+)/', $durationStr, $timeMatch)) {
            $timePart = $timeMatch[1];
            
            // Extract hours
            if (preg_match('/(\d+)H/', $timePart, $hMatch)) {
                $hours = (int)$hMatch[1];
            }
            
            // Extract minutes
            if (preg_match('/(\d+)M/', $timePart, $mMatch)) {
                $minutes = (int)$mMatch[1];
            }
        }
        
        // Extract days part (before T) and add to hours
        if (preg_match('/P(\d+)D/', $durationStr, $dMatch)) {
            $days = (int)$dMatch[1];
            $hours += $days * 24;
        }
        
        return json_encode(['hours' => $hours, 'minutes' => $minutes]);
    }
}
