# 🙏 miserend.hu

A miserend.hu teljes forrása elavult mintaadatokkal.

# Komponensek

Az alkalmazás öt komponensből áll.

## Adatbázis

Az adatbázis egyszerű MySQL / MariaDB. Az adatbázis séma inicializálásáoz szükséges fájlok a `docker/mysql/initdb.d` könytárban találhatóak. Ennek a könyvtárnak a `data` alkönyvtárában mintaadatok is találhatóak.

## Elastisearch/Kibana

Az alkalmazás keresőmotorja, alapesetben a standard konténer alapú telepítés kiszolgálja az igényeket, speciális esetben van szükség konfigurálására.

## Naptár frontend (Angular)

A `calendar` könyvtárban található Angular alkalmazás. Közvetlenül nem használható, a `docker/miserend/calendar_deploy.py` szkript segítségécel integrálható az web alkalmazás forrásába.

## Miserend web alkalmazás (PHP)

A fő komponens a portál forráskódja. A PHP függőségeket `composer` segítségével lehet telepíteni, a JavaScript/CSS függőségeket pedig `nodejs/npm`-el. 

# ⚙️ Telepítés

Az alkalmazást vagy fejlesztési vagy kipróbálási céllal lehet telepíteni saját környezetben.

## Kipróbálás

### 📦 Előfeltételek
 
- [Docker](https://docs.docker.com/engine/install/)
- [make](https://www.gnu.org/software/make/)

_Megjegyzés: ha lehetőségünk a `make` telepítésére, a `Makefile`-ban megnézhetjük, melyik task mit futtat le._

### 🚀 Indítás

```sh
make start
```

### 🛸 Indítás a háttérben:

```sh
make start DAEMON=true
```

> TODO: korrekt előkonfiguráció, docker compose helyi build nélkül

## Fejlesztés

### 📦 Előfeltételek

- git
- bash (Windows alatt a git része, vagy WSL használata ajánlott)
- Docker/podman
- MySQL kliens
- nodejs/npm és Python (naptár fejlesztésre)
- SMTP szerver (mailcatcher ajánlott)

Ügyeljünk, hogy a fejlesztéskor konzisztensen *UNIX sorvégeket* használjunk!

### Telepítés

#### Adatbázis

Az adatbázis konténer első futtatáskor a `docker/mysql/initdb.d` könyvtár alapján inicializálja az adatbázist. Ha az adatbázis sémán változtatsz, ebbe a könyvtárba vezesd be a módosításokat!

### Elastisearch/Kibana

A compose fájlban található beállítások első körben teljesen megfelelnek.

### Miserend alkalmazás

A webapp könyvtárat kell az upstream miserend konténerbe mappelni. Amennyiben a naptár alkalmazáson dolgozunk, az npm build után a `docker/miserend/calendar_deploy.py` szkript futtatásával lehet az alkalmazásba integrálni.

# Fejlesztői megjegyzések

## 🌍 Környezeti változók

Egyes beállításokat, pl. portokat, az `.env.example` fájl tartalmának átmásolásával az `.env` fájlban lehet módosítani.

- Ha a `docker up` hibát generál, mondván hogy egy port már foglalt, akkor ez lehet a megoldás. Egyébként opcionális.
- `MISEREND_WEBAPP_ENVIRONMENT` = `development` | `staging` | `production`

## 🔗 Elérések

| Megnevezés | Cím                   | Felhasználónév | Jelszó | Megjegyzés                      |
| ---------- | --------------------- | -------------- | ------ | ------------------------------- |
| Miserend   | http://localhost:8001 | admin          | admin  | `.env` fájlban állítható        |
| phpMyAdmin | http://localhost:8081 | user vagy root | pw     | Host: mysql, Database: miserend |
| Kibana     | http://localhost:5601 |                |        | Elasticsearch frontend          |

## 🗃️ Dump készítés

Ha dump-ot szeretnénk készíteni az adatbázisról fejlesztési célra, a kényes adatok eltávolításáról gondoskodni kell, erre a `docker/mysql/dump.sh` szkript szolgál. A fájl elején lévő változóktat környezeti változóként lehet felülbírálni.

## 🐳 Konténerek

A [docker-compose.yml](docker-compose.yml) a következő konténereket indítja el:

### 🛢️ mysql

Az adatbázisszerver. Betölti a mintaadatokat és megőrzi az adatokat újraindítás esetén is.  
Törléshez a hozzá tartozó _volume_-ot kell eltávolítani (pl. Docker Desktopban).

### 🔍 elasticsearch

A keresőmotor. A következő függvény rendszeres futtatása szükséges:  
`Externalapi\ElasticsearchApi::updateChurches()`  
Első használatkor is futtatni kell!

### 📊 kibana

Elasticsearch admin felülete fejlesztéshez.  
Beizzítása kis varázslást igényelhet.

### 🌐 miserend

A webalkalmazás fő komponense. A `/webapp` mappa kerül betöltésre.

## 🛠️ További parancsok

### 🧭 Konténerekbe belépés

```sh
docker exec -it [mysql|pma|mailcatcher|miserend] bash
```

### ✅ Unit tesztek futtatása (hamarosan)

```sh
make test
```

Megjegyzés: Jelenleg nincs `phpunit` telepítve.

### 📦 Composer használata (interaktív módban):

```sh
docker exec miserend composer install|require|update
```

## 🌳 Branching stratégia

- `master` ➜ staging környezet (`staging.miserend.hu`)
- `production` ➜ éles honlap

## 💬 Egyéb megjegyzések

- A `mailcatcher` csak `env['production']` esetén nem aktív.

## 📆 Naptárnézet

- Egy különálló projekt, ami be lett integrálva a meglévő rendszerbe
- Első alkalommal le kell generálni az időszakokat:
- Admin joggal, az `/periodyeareditor` felületen

### Naptár szerkesztése

A `/calendar` könyvtárban az alábbi parancsokat futtassuk:

Ha még nem volt, akkor:
```sh
npm install
```
```sh
ng build --configuration=localProd
python ../scripts/calendar_deploy.py
npm run start:integrated
```
- Ezzel egyrészt elérjük, hogy fejlesztői legyen a naptár
- Másrészt elérjük, hogy ha valamit módosítunk, az szinte egyből érvényre jusson
- Ilyenkor egy python script a `/calendar` mappában buildeli az Angularos projektet, majd a megfelelő helyre átmásolja a legenerált fájlokat


## Táblák beszúrása
Ha még nincsenek a miserend adatbázisban a `cal_` prefixű táblák, akkor először másoljuk fel a dockerre az sql fájlokat:
```
docker cp ./scripts/calendar_sql_init mysql:/calendar_sql_init
```

Majd a mysql docker konténerbe belépve, az alábbi kódot futtassuk:
```
mysql --default-character-set=utf8 -u root -p miserend < /calendar_sql_init/calendar_init.sql
```
Ha minta adatokat is szeretnénk (periódushoz) akkor az alábbiakat is futtassuk, ebben a sorrendben:
```
mysql --default-character-set=utf8 -u root -p miserend < /calendar_sql_init/sample_periods.sql
mysql --default-character-set=utf8 -u root -p miserend < /calendar_sql_init/sample_period_years.sql
```
Ezután be kell lépni a felületre, és az `/periodyeareditor` felületen legenerálni az aktuális időszakra.
A minta adatok idővel elévülhetnek, fontos az aktualizálásuk!

## Éles / staging / UAT build
Fejlesztés végén azonban egy megfelelő környezetbe való build kell, például:
```
ng build --configuration=production
python ../scripts/calendar_deploy.py
```