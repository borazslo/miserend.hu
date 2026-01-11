# 🙏 miserend.hu

A miserend.hu weboldal teljes forráskódja.

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
 
- [git](gttps://git-scm.com)
- [Docker](https://docs.docker.com/engine/install/)

### 🚀 Indítás

```sh
git clone https://github.com/borazslo/miserend.hu/
cd miserend.hu
docker-compose  -f docker/compose.yml -f docker/compose.test.yml up
```

Az alkalmazásba a http://localhost:8000 címen az `admin` felhasználóval lehet belépni, az alapérelmezett jelszó `miserend`.

## Fejlesztés

### 📦 Előfeltételek

- git
- Docker/Podman
- MySQL vagy MariaDB kliens
- nodejs/npm és python (naptár fejlesztésre)

#### Windows 

Lehetséges Windows Subsystem for Linux nélkül is felépíteni egy miserend fejlesztői környezetet, de mivel az alkalmazás komponensei alapvetően natív linuxos eszközök, a windowsos futtatás mindig extra odafigyelést igényel.   

Mindenesetre, a szükséges eszközök winget-tel is telepíthetőek.

```
winget install --id=Git.Git -e
winget install --id=Python.Python.3.14 -e
winget install --id=Docker.DockerCLI -e
winget install --id=Docker.DockerCompose -e
winget install --id=OpenJS.NodeJS.LTS -e
```

De szinte biztos, hogy a végén valami extra masszírozás kell.


### 🚀 Indítás

```sh
git clone https://github.com/borazslo/miserend.hu/
cd miserend.hu
docker-compose  -f docker/compose.yml -f docker/compose.dev.yml up
```

A dev composer file tartalmaz egy mailcatcher-t, így nem kell külön SMTP szerverrel bajlódni.


#### Adatbázis

Az adatbázis konténer első futtatáskor a `docker/mysql/initdb.d` könyvtár alapján inicializálja az adatbázist. Ha az adatbázis sémán változtatsz, ebbe a könyvtárba vezesd be a módosításokat!

### Miserend alkalmazás

Itt is igaz, hogy admin / miserend az első felhasználó neve / jelszava.

A repó `webapp` könyvtárát a dev composer rá-mappeli a konténerre. Így ha bármit változtatsz, rögtön tesztelhető is. Amennyiben a naptár alkalmazáson dolgozunk, az npm build után a `docker/miserend/calendar_deploy.py` szkript futtatásával lehet az alkalmazásba integrálni.

Ha grafikus adatbázis elérésre lenne szükség, az [adminer](https://www.adminer.org/en/) ajánlott, egyszerűen az alkalmazás valamelyik könyvtárába kell tenni és már megy is. Természetesen ezt a fájl nem kell a git tárolóba elmenteni.

TODO: .gitignore frissítése

Ha új PHP van NodeJS függőséget építesz be, akkor a dev composer fájlból a két volume-ot ki kell venni és a függőségeket helyben telepíteni. 

#### Helyi build

Az alkamazásból helyben is lehet container image-t készíteni, ehhez a következő parancsot kell lefuttatni:

```sh
docker build -t miserend:latest -f docker/miserend/Dockerfile
```

Ha ki szeretnéd próbálni, hogyan működne a valóságban, akkor a [dev composer](docker/compose.dev.yml) fájlban írd ät a `miserend` service `image` attribútumát `localhost/miserend:latest`-re. 

# Fejlesztői megjegyzések

## 🌍 Környezeti változók

Egyes beállításokat, pl. portokat, az `.env.example` fájl tartalmának átmásolásával az `.env` fájlban lehet módosítani.

- Ha a `docker up` hibát generál, mondván hogy egy port már foglalt, akkor ez lehet a megoldás. Egyébként opcionális.
- `MISEREND_WEBAPP_ENVIRONMENT` = `development` | `staging` | `production`

## 🔗 Elérések

| Megnevezés | Cím                   | Felhasználónév | Jelszó    | Megjegyzés                      |
| ---------- | --------------------- | -------------- | --------- | ------------------------------- |
| Miserend   | http://localhost:8001 | admin          | miserend  | `.env` fájlban állítható        |
| Kibana     | http://localhost:5601 |                |           | Elasticsearch frontend          |

## 🗃️ Dump készítés

Ha dump-ot szeretnénk készíteni az adatbázisról fejlesztési célra, a kényes adatok eltávolításáról gondoskodni kell, erre a `docker/mysql/dump.sh` szkript szolgál. A fájl elején lévő változóktat környezeti változóként lehet felülbírálni.

## 🐳 Konténerek

A [docker/compose.yml](docker/compose.yml) a következő konténereket indítja el:

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

Ezen felül a dev composer fájl tartalmaz egy mailcatcher konténert is.


## 🛠️ További parancsok

### 🧭 Konténerekbe belépés

```sh
docker exec -it [mysql|mailcatcher|miserend] bash
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

## 📆 Naptárnézet

- Egy különálló projekt, ami be lett integrálva a meglévő rendszerbe
- Első alkalommal le kell generálni az időszakokat:
- Admin joggal, az `/periodyeareditor` felületen

A minta adatok idővel elévülhetnek, fontos az aktualizálásuk!

### Naptárnézet fejlesztése

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

### Fájl jogosultságok

Ha a fejlesztői környezetben a repót a miserend konténerbe mappeled előfordulhat, hogy a konténerben futó PHP nem tud (ideiglenes/cache) fájlokat írni, ilyenkor plusz írási jogot kell adnod az adott könyvtárra, pl:

```sh
chmod 777 webapp/fajlok/tmp
```

## Éles / staging / UAT build
Fejlesztés végén azonban egy megfelelő környezetbe való build kell, például:
```
ng build --configuration=production
python ../scripts/calendar_deploy.py
```