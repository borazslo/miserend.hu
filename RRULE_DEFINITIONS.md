# RRULE Definitions Documentation

## Overview

This document provides a comprehensive guide to the **RRULE** (Recurrence Rule) definitions supported by both the Angular `/calendar` project and the PHP Laravel backend in the miserend.hu application.

RRULE (Recurrence Rule) is a standard format for defining recurring events, based on **RFC 5545 iCalendar specification**. This document details the properties each system supports and their implementation differences.

---

## Table of Contents

1. [RFC 5545 Standard Reference](#rfc-5545-standard-reference)
2. [Angular Calendar RRULE Support](#angular-calendar-rrule-support)
3. [PHP Backend RRULE Support](#php-backend-rrule-support)
4. [Comparison Table](#comparison-table)
5. [Implementation Examples](#implementation-examples)
6. [Known Limitations](#known-limitations)

---

## RFC 5545 Standard Reference

The RRULE specification is defined in [RFC 5545 - Internet Calendaring and Scheduling Core Object Specification](https://tools.ietf.org/html/rfc5545).

Key resources:
- **Full RFC 5545**: https://tools.ietf.org/html/rfc5545
- **RRULE Specification**: https://tools.ietf.org/html/rfc5545#section-3.8.5
- **RRULE Examples**: https://tools.ietf.org/html/rfc5545#section-3.8.5.3

---

## Angular Calendar RRULE Support

### Location
- **Model**: [`calendar/src/app/model/calendar/recurrence-rule.ts`](calendar/src/app/model/calendar/recurrence-rule.ts)
- **Enum**: [`calendar/src/app/enum/recurrence.ts`](calendar/src/app/enum/recurrence.ts)
- **Utilities**: [`calendar/src/app/util/mass-util.ts`](calendar/src/app/util/mass-util.ts)

### Dependencies
- **rrule**: v2.8.1 - JavaScript library for working with recurrence rules
- **@fullcalendar/rrule**: v6.1.19 - FullCalendar RRULE plugin

### RecurrenceRule Interface

```typescript
// calendar/src/app/model/calendar/recurrence-rule.ts
interface RecurrenceRule {
  dtstart: string;           // ISO 8601 datetime string (REQUIRED)
  until?: string | null;     // ISO 8601 datetime string (OPTIONAL)
  freq: 'daily' | 'weekly' | 'monthly' | 'yearly';  // Frequency (REQUIRED)
  count?: number | null;     // Number of occurrences (OPTIONAL)
  byweekno?: number[] | null;     // Week numbers [1-53] (OPTIONAL)
  bysetpos?: number | null;       // Position in set, e.g., 1st, -1 (last) (OPTIONAL)
  byweekday?: Day[] | null;       // Days of week ['MO','TU','WE','TH','FR','SA','SU'] (OPTIONAL)
  bymonth?: number | null;        // Month [1-12] (OPTIONAL)
  bymonthday?: number[] | null;   // Day of month [1-31] (OPTIONAL)
}
```

### Supported Frequencies

#### 1. **DAILY** (freq: 'daily')
Repeat daily on specified days of the week (if `byweekday` is set).

**Example:**
```json
{
  "dtstart": "2026-03-08T10:30:00",
  "freq": "daily",
  "count": 5
}
```

#### 2. **WEEKLY** (freq: 'weekly')
Repeat weekly on specified days. Supports even/odd week filtering via `byweekno`.

**Example - Every Monday and Wednesday:**
```json
{
  "dtstart": "2026-03-08T10:30:00",
  "freq": "weekly",
  "byweekday": ["MO", "WE"],
  "until": "2026-12-31T23:59:59"
}
```

**Example - Even weeks only:**
```json
{
  "dtstart": "2026-03-08T10:30:00",
  "freq": "weekly",
  "byweekno": [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52],
  "byweekday": ["MO"]
}
```

**Example - Odd weeks only:**
```json
{
  "dtstart": "2026-03-08T10:30:00",
  "freq": "weekly",
  "byweekno": [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35, 37, 39, 41, 43, 45, 47, 49, 51],
  "byweekday": ["MO"]
}
```

#### 3. **MONTHLY** (freq: 'monthly')
Repeat monthly on specified days of the month or on a specific occurrence of a weekday.

**Example - Same day of month:**
```json
{
  "dtstart": "2026-03-15T10:30:00",
  "freq": "monthly",
  "count": 12
}
```

**Example - First Monday of month:**
```json
{
  "dtstart": "2026-03-08T10:30:00",
  "freq": "monthly",
  "bysetpos": 1,
  "byweekday": ["MO"]
}
```

**Example - Last Friday of month:**
```json
{
  "dtstart": "2026-03-08T10:30:00",
  "freq": "monthly",
  "bysetpos": -1,
  "byweekday": ["FR"]
}
```

#### 4. **YEARLY** (freq: 'yearly')
Repeat yearly on a specific date or occurrence.

**Example - December 25th (Christmas):**
```json
{
  "dtstart": "2026-12-25T10:30:00",
  "freq": "yearly",
  "bymonth": 12,
  "bymonthday": [25]
}
```

### Angular Recurrence Enum

The [`Renum`](calendar/src/app/enum/recurrence.ts) enum defines high-level recurrence patterns:

```typescript
enum Renum {
  NONE = 'NONE',                         // No repetition (one-time event)
  EVERY_WEEK = 'EVERY_WEEK',             // Every week on specified days
  FIRST_WEEK = 'FIRST_WEEK',             // First occurrence of weekday in month
  SECOND_WEEK = 'SECOND_WEEK',           // Second occurrence of weekday in month
  THIRD_WEEK = 'THIRD_WEEK',             // Third occurrence of weekday in month
  FOURTH_WEEK = 'FOURTH_WEEK',           // Fourth occurrence of weekday in month
  FIFTH_WEEK = 'FIFTH_WEEK',             // Fifth occurrence of weekday in month
  LAST_DAY_OF_MONTH = 'LAST_DAY_OF_MONTH', // Last occurrence of weekday in month
  YEARLY = 'YEARLY',                     // Every year
  EVEN_WEEK = 'EVEN_WEEK',               // Even weeks only (2, 4, 6, ...)
  ODD_WEEK = 'ODD_WEEK',                 // Odd weeks only (1, 3, 5, ...)
}
```

### Angular Recurrence Properties

| Type | Description | Example Usage |
|------|-------------|---------------|
| NONE | One-time event, no recurrence | Single mass on specific date |
| EVERY_WEEK | Repeats every week on selected days | Weekly Sunday mass |
| FIRST_WEEK | First occurrence of selected weekday in month | First Monday mass |
| SECOND_WEEK | Second occurrence of selected weekday in month | Second Sunday mass |
| THIRD_WEEK | Third occurrence of selected weekday in month | Third Thursday mass |
| FOURTH_WEEK | Fourth occurrence of selected weekday in month | Fourth Friday mass |
| FIFTH_WEEK | Fifth occurrence of selected weekday in month | Fifth Saturday mass (if exists) |
| LAST_DAY_OF_MONTH | Last occurrence of selected weekday in month | Last Sunday mass |
| YEARLY | Once per year on specified date | Christmas, Easter (moveable via period) |
| EVEN_WEEK | Biweekly on even weeks | Alternate week mass |
| ODD_WEEK | Biweekly on odd weeks | Alternate week mass |

---

## PHP Backend RRULE Support

### Location
- **Implementation**: [`webapp/classes/simplerrule.php`](webapp/classes/simplerrule.php)
- **Usage**: [`webapp/classes/eloquent/calmass.php`](webapp/classes/eloquent/calmass.php)
- **iCalendar Export**: [`webapp/classes/html/church/ical.php`](webapp/classes/html/church/ical.php)

### SimpleRRule Class

The [`SimpleRRule`](webapp/classes/simplerrule.php) class processes RRULE arrays and generates occurrences.

#### Constructor Parameters

```php
public function __construct(array $rrule, callable $debugCallback = null)
```

**RRULE Array Structure:**

```php
$rrule = [
    'dtstart'      => '2026-03-08T10:30:00',     // Carbon datetime (REQUIRED)
    'until'        => '2026-12-31T23:59:59',     // Carbon datetime (OPTIONAL)
    'count'        => null,                       // Number of occurrences (OPTIONAL)
    'freq'         => 'WEEKLY',                   // Frequency (REQUIRED)
    'interval'     => 1,                          // Step between recurrences (OPTIONAL, default: 1)
    'bymonth'      => [],                         // Months [1-12] (OPTIONAL)
    'bymonthday'   => [],                         // Days of month [1-31] (OPTIONAL)
    'byweekday'    => ['MO', 'WE', 'FR'],        // Weekdays (OPTIONAL)
    'bysetpos'     => null,                       // Position in set, 1-5 or -1 (OPTIONAL)
    'byweekno'     => [],                         // Week numbers [1-53] (OPTIONAL)
    'exdate'       => [],                         // Excluded dates (OPTIONAL)
];
```

### Supported Frequencies

#### 1. **DAILY**
Generates daily occurrences, optionally filtered by `byweekday`.

```php
$rrule = [
    'dtstart' => Carbon::parse('2026-03-08 10:30'),
    'freq'    => 'DAILY',
    'until'   => Carbon::parse('2026-03-20 23:59'),
];
$rule = new SimpleRRule($rrule);
$occurrences = $rule->getOccurrences();
```

#### 2. **WEEKLY**
Generates weekly occurrences on specified weekdays. Supports `byweekno` for even/odd weeks.

```php
// Every Monday and Wednesday
$rrule = [
    'dtstart'   => Carbon::parse('2026-03-08 10:30'),
    'freq'      => 'WEEKLY',
    'byweekday' => ['MO', 'WE'],
    'until'     => Carbon::parse('2026-12-31 23:59'),
];

// Even weeks only
$rrule = [
    'dtstart'   => Carbon::parse('2026-03-08 10:30'),
    'freq'      => 'WEEKLY',
    'byweekday' => ['MO'],
    'byweekno'  => [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52],
    'until'     => Carbon::parse('2026-12-31 23:59'),
];
```

#### 3. **MONTHLY**
Generates monthly occurrences.

- **Without `byweekday`**: Same day of each month
- **With `byweekday` and `bysetpos`**: Specific occurrence of weekday in month

```php
// 15th of each month
$rrule = [
    'dtstart' => Carbon::parse('2026-03-15 10:30'),
    'freq'    => 'MONTHLY',
    'until'   => Carbon::parse('2026-12-31 23:59'),
];

// First Monday of each month
$rrule = [
    'dtstart'   => Carbon::parse('2026-03-08 10:30'),
    'freq'      => 'MONTHLY',
    'byweekday' => ['MO'],
    'bysetpos'  => 1,
    'until'     => Carbon::parse('2026-12-31 23:59'),
];

// Last Friday of each month
$rrule = [
    'dtstart'   => Carbon::parse('2026-03-06 10:30'),
    'freq'      => 'MONTHLY',
    'byweekday' => ['FR'],
    'bysetpos'  => -1,
    'until'     => Carbon::parse('2026-12-31 23:59'),
];
```

#### 4. **YEARLY**
Generates yearly occurrences on specified month/day combinations.

```php
// December 25th (Christmas)
$rrule = [
    'dtstart'    => Carbon::parse('2026-12-25 10:30'),
    'freq'       => 'YEARLY',
    'bymonth'    => [12],
    'bymonthday' => [25],
    'until'      => Carbon::parse('2050-12-31 23:59'),
];

// March 19th (St. Joseph)
$rrule = [
    'dtstart'    => Carbon::parse('2026-03-19 10:30'),
    'freq'       => 'YEARLY',
    'bymonth'    => [3],
    'bymonthday' => [19],
    'until'      => Carbon::parse('2050-12-31 23:59'),
];
```

### Key Methods

#### `getOccurrences(): array`
Generates all occurrences based on the RRULE definition.

```php
$rule = new SimpleRRule($rrule);
$occurrences = $rule->getOccurrences();  // Returns array of Carbon dates

foreach ($occurrences as $date) {
    echo $date->toIso8601String();  // 2026-03-08T10:30:00+01:00
}
```

#### `toText(): string`
Returns a human-readable text representation.

```php
$rule = new SimpleRRule($rrule);
echo $rule->toText();
// Output: "Freq: WEEKLY; Interval: 1; ByWeekday: 1,3,5; Until: 2026-12-31T23:59:59+01:00"
```

### Weekday Normalization

The `byweekday` field accepts ISO 8601 two-letter weekday codes:

```php
'MO' => 1   // Monday
'TU' => 2   // Tuesday
'WE' => 3   // Wednesday
'TH' => 4   // Thursday
'FR' => 5   // Friday
'SA' => 6   // Saturday
'SU' => 7   // Sunday
```

---

## Comparison Table

| Feature | Angular | PHP |
|---------|---------|-----|
| **Standard** | RFC 5545 (rrule.js v2.8.1) | Custom SimpleRRule class |
| **Date Format** | ISO 8601 string | Carbon datetime object |
| **Frequency Values** | lowercase ('daily', 'weekly', etc.) | UPPERCASE ('DAILY', 'WEEKLY', etc.) |
| **Supports `dtstart`** | ✅ Yes | ✅ Yes |
| **Supports `until`** | ✅ Yes | ✅ Yes |
| **Supports `count`** | ✅ Yes | ✅ Yes |
| **Supports `freq`** | ✅ Yes (4 types) | ✅ Yes (4 types) |
| **Supports `interval`** | ⚠️ Partial | ✅ Yes |
| **Supports `bymonth`** | ✅ Yes | ✅ Yes |
| **Supports `bymonthday`** | ✅ Yes | ✅ Yes |
| **Supports `byweekday`** | ✅ Yes | ✅ Yes |
| **Supports `bysetpos`** | ✅ Yes (1 to -1) | ✅ Yes (1 to -1) |
| **Supports `byweekno`** | ✅ Yes | ✅ Yes |
| **Supports `exdate`** | ✅ Yes | ✅ Yes |
| **Even/Odd Week Support** | ✅ Via Renum enum | ✅ Via byweekno array |
| **Easter Support** | ✅ Via Period system | ✅ Via Period system |
| **Christmas Support** | ✅ Via Period system | ✅ Via Period system |

---

## Implementation Examples

### Example 1: Weekly Mass (Every Monday)

**Angular (TypeScript):**
```typescript
const mass: Mass = {
  id: 1,
  churchId: 123,
  periodId: 'spring-2026',
  title: 'Regular Monday Mass',
  rite: Rite.ROMAN_CATHOLIC,
  startDate: '2026-03-02T10:30:00',
  rrule: {
    dtstart: '2026-03-02T10:30:00',
    freq: 'weekly',
    byweekday: ['MO'],
    until: '2026-12-31T23:59:59'
  },
  lang: LanguageCode.HU,
  comment: 'Regular Monday morning mass'
};
```

**PHP (Laravel):**
```php
$mass = CalMass::create([
    'church_id' => 123,
    'period_id' => 'spring-2026',
    'title' => 'Regular Monday Mass',
    'rite' => 'roman_catholic',
    'start_date' => '2026-03-02T10:30:00',
    'rrule' => [
        'dtstart' => '2026-03-02T10:30:00',
        'freq' => 'WEEKLY',
        'byweekday' => ['MO'],
        'until' => '2026-12-31T23:59:59'
    ],
    'lang' => 'hu',
    'comment' => 'Regular Monday morning mass'
]);
```

### Example 2: Biweekly (Even Weeks Only)

**Angular (TypeScript):**
```typescript
const mass: Mass = {
  rrule: {
    dtstart: '2026-03-02T10:30:00',
    freq: 'weekly',
    byweekday: ['TU', 'FR'],
    byweekno: [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52],
    until: '2026-12-31T23:59:59'
  }
};
```

**PHP (Laravel):**
```php
$rrule = [
    'dtstart' => '2026-03-02T10:30:00',
    'freq' => 'WEEKLY',
    'byweekday' => ['TU', 'FR'],
    'byweekno' => [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52],
    'until' => '2026-12-31T23:59:59'
];

$rule = new SimpleRRule($rrule);
$occurrences = $rule->getOccurrences();
```

### Example 3: Monthly (First Sunday)

**Angular (TypeScript):**
```typescript
const mass: Mass = {
  rrule: {
    dtstart: '2026-03-01T10:30:00',
    freq: 'monthly',
    byweekday: ['SU'],
    bysetpos: 1,
    until: '2026-12-31T23:59:59'
  }
};
```

**PHP (Laravel):**
```php
$rrule = [
    'dtstart' => '2026-03-01T10:30:00',
    'freq' => 'MONTHLY',
    'byweekday' => ['SU'],
    'bysetpos' => 1,
    'until' => '2026-12-31T23:59:59'
];

$rule = new SimpleRRule($rrule);
$occurrences = $rule->getOccurrences();
```

### Example 4: Monthly (Last Friday)

**Angular (TypeScript):**
```typescript
const mass: Mass = {
  rrule: {
    dtstart: '2026-03-27T10:30:00',
    freq: 'monthly',
    byweekday: ['FR'],
    bysetpos: -1,
    until: '2026-12-31T23:59:59'
  }
};
```

**PHP (Laravel):**
```php
$rrule = [
    'dtstart' => '2026-03-27T10:30:00',
    'freq' => 'MONTHLY',
    'byweekday' => ['FR'],
    'bysetpos' => -1,
    'until' => '2026-12-31T23:59:59'
];

$rule = new SimpleRRule($rrule);
$occurrences = $rule->getOccurrences();
```

### Example 5: Yearly (December 25th - Christmas)

**Angular (TypeScript):**
```typescript
const mass: Mass = {
  rrule: {
    dtstart: '2026-12-25T22:00:00',
    freq: 'yearly',
    bymonth: 12,
    bymonthday: [25],
    count: 1
  }
};
```

**PHP (Laravel):**
```php
$rrule = [
    'dtstart' => '2026-12-25T22:00:00',
    'freq' => 'YEARLY',
    'bymonth' => [12],
    'bymonthday' => [25],
    'count' => 1
];

$rule = new SimpleRRule($rrule);
$occurrences = $rule->getOccurrences();
```

### Example 6: Single Event (No Recurrence)

**Angular (TypeScript):**
```typescript
const mass: Mass = {
  rrule: {
    dtstart: '2026-03-08T10:30:00',
    freq: 'daily',
    count: 1
  }
};
```

**PHP (Laravel):**
```php
$rrule = [
    'dtstart' => '2026-03-08T10:30:00',
    'freq' => 'DAILY',
    'count' => 1
];

$rule = new SimpleRRule($rrule);
$occurrences = $rule->getOccurrences();
```

---

## Known Limitations

### Angular Limitations

1. **INTERVAL not fully documented**: The `interval` property in the rrule.js library is available but not exposed in the `RecurrenceRule` interface.

2. **BYMONTH precision**: `bymonth` is a single number, not an array - only one month can be specified (typically used with `freq: yearly`).

3. **Complex RRULE combinations**: Some advanced RFC 5545 features may not be fully supported or tested:
   - `BYHOUR`, `BYMINUTE`, `BYSECOND`
   - `BYYEARDAY`
   - Multiple `BYDAY` modifiers with same frequency

### PHP Limitations

1. **BYSETPOS range**: Only supports positive values (1-5) and -1 (last). Values like -2, -3 are not handled.

2. **Date arithmetic**: The PHP implementation uses Carbon library which is timezone-aware; be cautious with timezone handling across international date ranges.

3. **Performance**: Generating occurrences for long periods (e.g., 10+ years) may be computationally intensive.

4. **Limited NLP support**: Unlike rrule.js, SimpleRRule does not have natural language parsing (e.g., "every other Monday").

### Both Projects

1. **Easter/Christmas moveable dates**: These are handled via the **Period system**, not directly in RRULE. Special period types (`EASTER`, `CHRISTMAS`) map specific weekdays to these moveable feasts.

2. **Timezone handling**:
   - Angular uses ISO 8601 strings (timezone-aware if `Z` suffix present)
   - PHP uses Carbon datetime objects with timezone context
   - Conversion between systems requires care

3. **ExDate format**:
   - Angular: ISO 8601 string array
   - PHP: Carbon datetime array or date strings

---

## Integration Notes

### Data Flow

1. **UI (Angular) → Backend (PHP)**:
   - User creates mass with recurrence pattern via Angular dialog
   - Angular sends RRULE as JSON with lowercase `freq`
   - PHP receives and stores in `calmass.rrule` JSON column
   - SimpleRRule processes for occurrence generation

2. **Backend (PHP) → Frontend (Angular)**:
   - PHP returns mass data with RRULE (already stored as JSON)
   - Angular receives and displays in calendar via FullCalendar + rrule plugin
   - If needed, converts to human-readable text via `Renum` enum

3. **Export (iCalendar)**:
   - PHP converts RRULE arrays to iCalendar format via `ical.php`
   - Format: `RRULE:FREQ=WEEKLY;BYDAY=MO,WE,FR;UNTIL=20261231T235959`
   - Includes `EXDATE` lines for excluded dates

### Debugging

**Angular Console:**
```typescript
import { RRule } from 'rrule';

const rrule = mass.rrule;
const rule = new RRule(rrule);
const occurrences = rule.all().slice(0, 10);  // First 10 occurrences
console.log(occurrences);
```

**PHP Debug:**
```php
$rrule = [...];
$rule = new SimpleRRule($rrule, function($msg, $ctx) {
    \Log::debug($msg, $ctx);
});
$occurrences = $rule->getOccurrences();
```

---

## References

- [RFC 5545 Full Specification](https://tools.ietf.org/html/rfc5545)
- [rrule.js Documentation](https://github.com/jakubroztocil/rrule)
- [rrule.js npm Package](https://www.npmjs.com/package/rrule)
- [FullCalendar RRULE Plugin](https://fullcalendar.io/docs/rrule-plugin)
- [Carbon DateTime Documentation](https://carbon.nesbot.com/)

---

## Document Metadata

- **Last Updated**: 2026-03-08
- **Scope**: miserend.hu application RRULE support
- **Projects Covered**:
  - Angular Calendar: `/calendar`
  - PHP Backend: `/webapp`
- **Status**: Complete documentation of current implementation

