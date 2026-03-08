# 🎯 Google iCalendar Integráció - Implementációs Terv

**Verzió:** 1.0  
**Dátum:** 2026-03-08  
**Státusz:** Tervezés befejezve

---

## 📋 Áttekintés

Ezt a dokumentumot az alábbi követelmények alapján készítettük:
- Külső Google naptárak (iCalendar) adatainak behúzása a saját rendszerbe
- Az importált események keresésben való megjelenítése
- Read-only nézet az Angular naptárban (szerkesztés letiltása)
- Napi automata frissítés cron job-on keresztül

---

## 🏗️ Architektúra Áttekintés

```
Google Calendar (iCalendar)
         |
         v
ExternalCalendarImporter (PHP)
         |
    +----+----+
    |         |
    v         v
CalMass    Church
    |         |
    +----+----+
         v
  Elasticsearch
         |
         v
Angular UI (Read-only)
```

---

## 📊 Adatbázis Séma

### Új tábla: `external_calendars`

**Fájl:** `docker/mysql/initdb.d/03-external-calendars.sql`

```sql
CREATE TABLE external_calendars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    church_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    url VARCHAR(2048) NOT NULL,
    active BOOLEAN DEFAULT 1,
    last_import_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (church_id) REFERENCES templomok(id) ON DELETE CASCADE,
    UNIQUE KEY unique_church_external (church_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;
```

**Oszlopok:**
- `id`: Egyedi azonosító
- `church_id`: Templom azonosítója (FK → templomok.id)
- `name`: Naptár neve (pl. "Google Calendar", "Ünnepek")
- `url`: Az iCalendar ICS URL
- `active`: Aktív/inaktív flag (cron csak az aktívakat dolgozza fel)
- `last_import_at`: Utolsó sikeres import dátuma
- `created_at`, `updated_at`: Metaadatok

**Constraints:**
- `UNIQUE KEY (church_id, name)`: Egy templom nem vezethet azonos nevű naptárakat
- `FOREIGN KEY`: Templom törléskor a naptár is törlődik

---

### Mintaadatok

**Fájl:** `docker/mysql/data/external-calendars-sample.sql`

```sql
INSERT INTO external_calendars (church_id, name, url, active, created_at) VALUES
(1254, 'Google Calendar', 'https://calendar.google.com/calendar/ical/c_qssbhpdrcj135o533mvm8d2ch4%40group.calendar.google.com/public/basic.ics', 1, NOW());
```

**Adat:**
- **Church ID:** 1254
- **Naptár név:** "Google Calendar"
- **URL:** Public Google Calendar iCalendar endpoint

---

## 🔧 Backend Komponensek

### 1. ExternalCalendar Eloquent Model

**Fájl:** `webapp/classes/eloquent/externalcalendar.php`

```php
<?php
namespace Eloquent;

class ExternalCalendar extends \Illuminate\Database\Eloquent\Model {
    protected $table = 'external_calendars';
    protected $fillable = ['church_id', 'name', 'url', 'active', 'last_import_at'];
    protected $dates = ['last_import_at', 'created_at', 'updated_at'];

    public function church() {
        return $this->belongsTo(Church::class, 'church_id');
    }
}
```

**Kapcsolatok:**
- `church()`: BelongsTo relationship Church modellel

---

### 2. ExternalCalendarImporter Osztály

**Fájl:** `webapp/classes/externalcalendarimporter.php`

**Felelősségek:**
- iCalendar URL-ből tartalom letöltése
- VEVENT elemek feldolgozása
- CalMass objektumok létrehozása
- CalMass-ok mentése az adatbázisba
- Elasticsearch index frissítése (`updateMasses()`)

**Függőségek:**
- `sabre/vobject` (Composer library iCalendar parse-oláshoz)
- `ExternalApi\ExternalApi` (HTTP lekérésekhez)
- `Eloquent\CalMass` (Mise modell)
- `ExternalApi\ElasticsearchApi` (Keresési index frissítéshez)

**Főbb metódusok:**

#### `public static function importAllExternalCalendars()`
Cron job belépési pont. Feldolgozza az összes aktív external calendar-t.

```php
// Pseudocode
foreach (ExternalCalendar::where('active', 1)->get() as $extCal) {
    try {
        self::importFromUrl($extCal->url, $extCal->church_id);
        $extCal->last_import_at = now();
        $extCal->save();
    } catch (Exception $e) {
        // Log error
    }
}
```

#### `private static function importFromUrl($url, $churchId)`
Letöltés és feldolgozás egy naptárhoz.

```php
// Flow
1. ICS tartalom letöltése URL-ből (HTTP GET)
2. Jelenlegi temple összes CalMass törlése
3. iCalendar feldolgozása (parseICalendar)
4. CalMass objektumok létrehozása
5. Elasticsearch frissítés
```

#### `private static function parseICalendar($icsContent)`
iCalendar feldolgozása `sabre/vobject` lib-vel.

```php
// Pseudocode
$vCal = Reader::read($icsContent);
foreach ($vCal->VEVENT as $event) {
    $calMass = new CalMass([
        'church_id' => $churchId,
        'title' => (string)$event->SUMMARY,
        'start_date' => (string)$event->DTSTART->getDateTime(),
        'duration' => self::extractDuration($event),
        'rrule' => self::extractRRule($event),
        'rite' => 'latin',
        'lang' => 'hu',
        'period_id' => null,  // External cal-ek nem periódushoz kötöttek
        'comment' => 'External calendar import'
    ]);
    $calMass->save();
}
```

#### `private static function extractRRule($event)`
RRULE szó szerinti megtartása JSON formátumban.

```php
if (!isset($event->RRULE)) {
    return null;
}

$rrule = (string)$event->RRULE;
// Pl. "FREQ=WEEKLY;BYDAY=SU,MO;UNTIL=20261231T235959Z"
return json_encode(['rule' => $rrule]);
```

#### `private static function extractDuration($event)`
DURATION vagy DTEND alapján.

```php
if (isset($event->DURATION)) {
    return json_encode(['duration' => (string)$event->DURATION]);
}
if (isset($event->DTEND)) {
    $start = $event->DTSTART->getDateTime();
    $end = $event->DTEND->getDateTime();
    $diff = $end->diff($start);
    return json_encode([
        'hours' => $diff->h,
        'minutes' => $diff->i
    ]);
}
return json_encode(['hours' => 1, 'minutes' => 0]);  // Default: 1 óra
```

---

### 3. Church Model Módosítások

**Fájl:** `webapp/classes/eloquent/church.php`

**Relationship hozzáadása:**
```php
public function externalCalendars() {
    return $this->hasMany(ExternalCalendar::class, 'church_id');
}
```

**Computed property hozzáadása:**
```php
public function getHasExternalCalendarAttribute() {
    return $this->externalCalendars()->where('active', 1)->exists();
}
```

**WriteAccess property módosítása:**

Jelenlegi logika (előző kód alapján):
```php
private function calcWriteAccess() {
    // ... meglévő logika ...
    
    // NEW: Ha external calendar van, akkor read-only
    if ($this->hasExternalCalendar) {
        return false;
    }
    
    return $canWrite;
}
```

---

### 4. Composer Dependency

**Fájl:** `webapp/composer.json`

Hozzáadás a `require` szekciójához:
```json
"sabre/vobject": "^4.5"
```

**Futtatás:**
```bash
cd webapp
composer require sabre/vobject
```

---

## ⏰ Cron Job Integráció

### Regisztrálás

**Fájl módosítás:** `webapp/classes/eloquent/cron.php`

A `initialize()` metódusban az alábbi sor hozzáadása:

```php
['\ExternalCalendarImporter', 'importAllExternalCalendars', '1 day']
```

**Teljes bejegyzés:**
```php
$jobsToSave = [
    ['\Eloquent\Cron', 'initialize', '1 week'],
    ['\Message', 'clean', '1 hour'],
    // ... más jobsok ...
    ['\ExternalCalendarImporter', 'importAllExternalCalendars', '1 day'],  // NEW
];
```

### Cron Logika

**Flow:**
```
1. Összes external_calendars lekérése WHERE active = 1
2. Mindegyik naptárhoz:
   a) Templom összes CalMass törlése (WHERE church_id = ? AND period_id IS NULL)
   b) iCalendar import futtatása (parseICalendar + CalMass create)
   c) Elasticsearch index frissítése: ElasticsearchApi::updateMasses([2025, 2026, 2027], [$churchId])
   d) last_import_at timestamp frissítése
3. Log: Sikeres/sikertelen importok naplózása
```

**Pszeudokód:**
```php
public static function importAllExternalCalendars() {
    set_time_limit(600);
    
    $calendars = ExternalCalendar::where('active', 1)->get();
    
    foreach ($calendars as $calendar) {
        try {
            // 1. Templomhoz tartozó CalMass törlése
            CalMass::where('church_id', $calendar->church_id)
                ->whereNull('period_id')
                ->delete();
            
            // 2. Import
            self::importFromUrl($calendar->url, $calendar->church_id);
            
            // 3. Elasticsearch frissítés
            ElasticsearchApi::updateMasses(
                [2025, 2026, 2027, 2028],
                [$calendar->church_id]
            );
            
            // 4. Timestamp frissítés
            $calendar->last_import_at = now();
            $calendar->save();
            
            echo "✓ Import sikeres: Church #{$calendar->church_id} ({$calendar->name})\n";
        } catch (Exception $e) {
            echo "✗ Import hiba: {$e->getMessage()}\n";
        }
    }
}
```

---

## 🌐 API Integráció

### Church Detail Endpoint

**Módosítandó fájl:** `webapp/classes/html/calendar/church.php`

A Church API response-ba hozzáadása:
```php
$church->append('hasExternalCalendar');
```

**API response minta:**
```json
{
    "id": 1254,
    "name": "Nagyvárad Székesegyháza",
    "nev": "Nagyvárad Székesegyháza",
    "hasExternalCalendar": true,
    "writeAccess": false,
    "rite": "latin",
    ...
}
```

### CalMass List Endpoint

**Módosítandó fájl:** `webapp/classes/html/calendar/masses.php`

A `getByChurchId()` metódus output-ja már tartalmazza a CalMass objektumokat, így az RRULE és egyéb mezők már benne vannak.

---

## 🎨 Frontend Integráció (Angular)

### church-calendar komponens módosítások

**Fájl:** `calendar/src/app/components/church-calendar/church-calendar.component.ts`

**Meglévő property:**
```typescript
@Input() editable: boolean = false;
```

**Módosítás 1: API response feldolgozása**
```typescript
ngOnInit() {
    // ... meglévő kód ...
    
    // hasExternalCalendar flag alapján editable beállítása
    if (this.currentChurch?.hasExternalCalendar) {
        this.editable = false;
    }
}
```

**Módosítás 2: Event Handler Disabling**

Az `editable` property már kontrollál:
- Dátumkattintásokat (új esemény hozzáadás)
- Edit/delete gombokat az Event Viewer Dialog-ban

Ezek a logikák már a meglévő kódban vannak, csak az `editable` flag-et kell `false`-ra állítani.

---

## ✅ Tesztelés

### Tesztelési Checklist

- [ ] MySQL tábla létrehozve (`external_calendars`)
- [ ] Mintaadatok beillesztve (church_id 1254)
- [ ] Composer lib telepítve (`sabre/vobject`)
- [ ] ExternalCalendarImporter osztály működik
- [ ] Cron job regisztrálva (initialize-nél)
- [ ] Church model `hasExternalCalendar` property működik
- [ ] `writeAccess` property `false`-ra vált external calendar-nál
- [ ] API response tartalmazza `hasExternalCalendar` flag-et
- [ ] Angular UI read-only (edit/delete gombok rejtve)
- [ ] CalMass-ok Elasticsearch-be indexelve
- [ ] Keresésben megjelenik az importált esemény
- [ ] Napi cron job sikeresen lefutott

### Manuális Tesztelés

**1. Adatbázis validáció:**
```sql
SELECT * FROM external_calendars WHERE church_id = 1254;
SELECT COUNT(*) FROM cal_masses WHERE church_id = 1254 AND period_id IS NULL;
```

**2. Import futtatása:**
```php
$importer = new \ExternalCalendarImporter();
$importer->importAllExternalCalendars();
```

**3. Elasticsearch validáció:**
```bash
curl -X GET "localhost:9200/mass/_search?q=church_id:1254"
```

**4. Angular UI tesztelés:**
- Nyiss meg egy tempomot external calendar-ral
- Ellenőrizd, hogy az edit/delete gombok nincsenek megjelenítve
- Ellenőrizd, hogy az importált események meg vannak jelenítve az Angular naptárban

---

## 📝 Implementációs Sorrend

1. **Adatbázis séma**
   - `03-external-calendars.sql` létrehozása
   - `external-calendars-sample.sql` létrehozása

2. **Composer dependency**
   - `composer require sabre/vobject`

3. **Eloquent Model**
   - `webapp/classes/eloquent/externalcalendar.php`

4. **ExternalCalendarImporter**
   - `webapp/classes/externalcalendarimporter.php`

5. **Church Model bővítés**
   - `hasExternalCalendar` property
   - `writeAccess` módosítás
   - `externalCalendars()` relationship

6. **Cron Job regisztrálás**
   - `webapp/classes/eloquent/cron.php` módosítása

7. **API Integration**
   - `webapp/classes/html/calendar/church.php` módosítása

8. **Angular Frontend (ha szükséges)**
   - `calendar/src/app/components/church-calendar/church-calendar.component.ts` tesztelése

9. **End-to-end tesztelés**
   - Cron job futtatása
   - Keresés validáció
   - UI validáció

---

## 🔗 Referenciák

- **iCalendar RFC 5545:** https://tools.ietf.org/html/rfc5545
- **sabre/vobject dokumentáció:** https://sabre.io/vobject/
- **Google Calendar iCalendar formátum:** https://support.google.com/calendar/answer/37648
- **Eloquent dokumentáció:** https://laravel.com/docs/eloquent

---

## 🐛 Hibakezelés

### Lehetséges hibák és megoldások

**1. iCalendar parse hiba**
```php
try {
    $vCal = Reader::read($icsContent);
} catch (ParseException $e) {
    \Log::error("iCalendar parse failed: " . $e->getMessage());
    throw new \Exception("Invalid iCalendar format: " . $url);
}
```

**2. HTTP letöltés sikertelen**
```php
$api = new \ExternalApi\ExternalApi();
$api->query = $url;
$api->run();
if (!$api->rawData) {
    throw new \Exception("Failed to download iCalendar from: " . $url);
}
```

**3. CalMass duplikáció**
Megoldás: Templomhoz tartozó jelenlegi CalMass-okat törlés előtt.

---

## 📌 Megjegyzések

- Az external calendar-ból importált misék a keresésben azonnal meg fognak jelenni az `updateMasses()` után
- RRULE-t szó szerint megtartjuk, az Angular UI megfelelően kezelheti
- Az external calendar napi frissítés a cron rendszeren keresztül működik
- Temple holder ezt a naptárat nem szerkesztheti, csak olvashatja

---

**Készítette:** Architect Mode  
**Státusz:** Ready for Code Mode Implementation ✅
