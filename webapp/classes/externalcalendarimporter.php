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
                echo "ℹ No active external calendars to import.\n";
                return;
            }
            
            foreach ($calendars as $calendar) {
                try {
                    echo "▶ Importing: Church #{$calendar->church_id} - {$calendar->name}...\n";
                    
                    self::importFromUrl($calendar->url, $calendar->church_id);
                    
                    // Update last_import_at timestamp
                    $calendar->last_import_at = \Carbon\Carbon::now();
                    $calendar->save();
                    
                    echo "✓ Import successful: Church #{$calendar->church_id}\n";
                } catch (\Exception $e) {
                    echo "✗ Import failed for Church #{$calendar->church_id}: " . $e->getMessage() . "\n";
                    \Log::error("ExternalCalendarImporter: Church #{$calendar->church_id} - " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            echo "✗ Critical error in importAllExternalCalendars: " . $e->getMessage() . "\n";
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
        
        echo "  Created $eventsCreated masses from iCalendar\n";
        
        // 4. Refresh Elasticsearch index for this church
        $years = [2025, 2026, 2027, 2028];
        \ExternalApi\ElasticsearchApi::updateMasses($years, [$churchId]);
        
        echo "  Elasticsearch index updated\n";
    }

    /**
     * Download ICS content from URL using ExternalApi
     */
    private static function downloadIcsFromUrl($url) {
        try {
            $api = new \ExternalApi\ExternalApi();
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
            
            // Iterate through parsed events
            foreach ($events as $event) {
                try {
                    $calMass = self::createCalMassFromEvent($event, $churchId);
                    $calMass->save();
                    $eventsCreated++;
                } catch (\Exception $e) {
                    echo "  ⚠ Error creating CalMass: " . $e->getMessage() . "\n";
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
            ];
            
            // Parse SUMMARY
            if (preg_match('/^SUMMARY\s*:\s*(.*)$/im', $eventData, $m)) {
                $event->SUMMARY = trim($m[1]);
            }
            
            // Parse DTSTART
            if (preg_match('/^DTSTART(?:.*?)?\s*:\s*(.*)$/im', $eventData, $m)) {
                $event->DTSTART = trim($m[1]);
            }
            
            // Parse DTEND
            if (preg_match('/^DTEND(?:.*?)?\s*:\s*(.*)$/im', $eventData, $m)) {
                $event->DTEND = trim($m[1]);
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
        $rrule = self::extractRRule($event);
        
        $calMass = new \Eloquent\CalMass([
            'church_id' => $churchId,
            'title' => $summary,
            'start_date' => $startDate,
            'duration' => $duration,
            'rrule' => $rrule,
            'rite' => 'latin',  // Default rite
            'lang' => 'hu',     // Default language
            'period_id' => null,  // External calendars don't belong to periods
            'comment' => 'External calendar import',
        ]);
        
        return $calMass;
    }

    /**
     * Extract DTSTART from iCalendar event
     * Returns ISO 8601 datetime string
     */
    private static function extractStartDate($event) {
        if (!isset($event->DTSTART)) {
            throw new \Exception("DTSTART is missing from event");
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
                return json_encode(['hours' => $hours, 'minutes' => $minutes]);
            }
            
            // Default: 1 hour
            return json_encode(['hours' => 1, 'minutes' => 0]);
        } catch (\Exception $e) {
            // On any duration parsing error, default to 1 hour
            return json_encode(['hours' => 1, 'minutes' => 0]);
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
            $rrule = (string)$event->RRULE;
            // Keep the RRULE as-is (textual format from iCalendar)
            // The Angular UI will handle the rendering
            return json_encode(['rule' => $rrule]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse iCalendar datetime string (YYYYMMDDTHHMMSS or YYYYMMDD)
     * Returns ISO 8601 datetime string (YYYY-MM-DD HH:MM:SS)
     */
    private static function parseIcsDateTime($dateStr) {
        $dateStr = trim($dateStr);
        
        // Handle date-only format (YYYYMMDD)
        if (strlen($dateStr) == 8 && ctype_digit($dateStr)) {
            $year = substr($dateStr, 0, 4);
            $month = substr($dateStr, 4, 2);
            $day = substr($dateStr, 6, 2);
            return "$year-$month-$day 00:00:00";
        }
        
        // Handle datetime format (YYYYMMDDTHHMMSS or YYYYMMDDTHHMMSSZ)
        if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})Z?$/', $dateStr, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
            $hour = $matches[4];
            $minute = $matches[5];
            $second = $matches[6];
            return "$year-$month-$day $hour:$minute:$second";
        }
        
        // If already in ISO format, return as-is
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $dateStr)) {
            return $dateStr;
        }
        
        // Fallback: try to parse with DateTime
        try {
            $dt = new \DateTime($dateStr);
            return $dt->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            throw new \Exception("Unable to parse datetime: $dateStr");
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
