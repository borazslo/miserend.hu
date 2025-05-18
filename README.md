# 🙏 miserend.hu

A miserend.hu teljes forrása elavult mintaadatokkal.

# ⚙️ Telepítés

## 📦 Előfeltételek

- [Docker](https://docs.docker.com/engine/install/)
- [make](https://www.gnu.org/software/make/)

_Megjegyzés: ha lehetőségünk a `make` telepítésére, a `Makefile`-ban megnézhetjük, melyik task mit futtat le._

## 🚀 Indítás

```sh
make start
```

### 🛸 Indítás a háttérben:

```sh
make start DAEMON=true
```

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

## 🪟 Futtatás Windows alatt

- Windows környezetben a miserend konténer kiakad, hogy a `exec ./docker/entrypoint_miserend.sh: no such file or directory`.  
  Megoldás: az `entrypoint_miserend.sh` átalakítása, hogy **Unix sorvégeket (LF)** használjon **Windows sorvégrek (CRLF)** helyett.

# 🗃️ Dump készítés

Ha dump-ot szeretnénk készíteni az adatbázisról fejlesztési célra, a kényes adatok eltávolításáról gondoskodni kell. Erre való a `dumper`.

## ⚙️ Tisztítás konfiguráció

A konfigurációs fájl helye: `dumper/config.yaml`

```yaml
purge: [Tisztítási konfiguráció]
  columns: [Oszlop szintű tisztítás]
    [Tábla neve]:
      - [Oszlop neve]
  tables: [Tábla szintű tisztítás]
    - [Tábla neve]
```

### ⚙️ Adatbázis kapcsolódás konfiguráció `.env` fájlban:

```
DUMPER_USER=[Felhasználó aki jogosult adatbázist is létrehozni]
DUMPER_PASSWORD=[Felhasználó jelszava]
DUMPER_HOST=[MySQL szerver címe]
DUMPER_SOURCE_DB=[Adatbázis amelyet ki szeretnénk dump-olni]
DUMPER_TEMP_DB=[Ideiglenes adatbázis neve, amelyben a tisztítást végezzük (a program hozza létre és semmisíti meg)]
```

## 🏃‍♂️ Futtatás

```sh
make dumper
```

## 💾 Dump

Elkészült dump: `docker/mysql/01-dump.sql`

Az elkészült dump-ot a fejlesztői környezet automatikusan betölti, amikor a MySQL konténer létrejön.

# 🐳 Konténerek

A [docker-compose.yml](docker-compose.yml) a következő konténereket indítja el:

## 🛢️ mysql

Az adatbázisszerver. Betölti a mintaadatokat és megőrzi az adatokat újraindítás esetén is.  
Törléshez a hozzá tartozó _volume_-ot kell eltávolítani (pl. Docker Desktopban).

## 🧰 pma (phpMyAdmin)

Webes adatbázis-kezelő a `http://localhost:8081` címen.  
Éles környezetben **le kell állítani**!

## 📬 mailcatcher

Fejlesztéshez használatos, az emaileket elkapja és a `http://localhost:1080` címen megtekinthetők.  
Éles környezetben **le kell tiltani**, és biztosítani az emailküldést.

## 🌐 miserend

A webalkalmazás fő komponense. A `/webapp` mappa kerül betöltésre.

## 🔍 elasticsearch

A keresőmotor. A következő függvény rendszeres futtatása szükséges:  
`Externalapi\ElasticsearchApi::updateChurches()`  
Első használatkor is futtatni kell!

## 📊 kibana

Elasticsearch admin felülete fejlesztéshez.  
Beizzítása kis varázslást igényelhet.

# 🛠️ További parancsok

## 🧭 Konténerekbe belépés

```sh
docker exec -it [mysql|pma|mailcatcher|miserend] bash
```

## ✅ Unit tesztek futtatása (hamarosan)

```sh
make test
```

Megjegyzés: Jelenleg nincs `phpunit` telepítve.

## 📦 Composer használata (interaktív módban):

```sh
docker exec miserend composer install|require|update
```

# 🌳 Branching stratégia

- `master` ➜ staging környezet (`staging.miserend.hu`)
- `production` ➜ éles honlap

# 💬 Egyéb megjegyzések

- A `mailcatcher` csak `env['production']` esetén nem aktív.
