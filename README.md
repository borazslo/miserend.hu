# 🙏 miserend.hu

A miserend.hu weboldal teljes forráskódja. Próbáld ki!

Csak [git](gttps://git-scm.com) és [Docker](https://docs.docker.com/engine/install/) legyen nálad és mehet is:

```sh
git clone https://github.com/borazslo/miserend.hu/
cd miserend.hu
docker-compose  -f docker/compose.yml -f docker/compose.test.yml up
```

Máris elérhető a http://localhost:8000 címen a miserend alkalmazás. Az `admin` felhasználóval be is lehet lépni az alapérelmezett jelszóval: `miserend`.


# ⚙️ Fejlesztői környezet telepítése

Kapcsolódj be a fejlesztésbe! Ehhez szükséged lesz egy fejlesztői környezetre amit ripsz-ropsz felállíthatsz.

## 📦 Előfeltételek

- __[git](gttps://git-scm.com)__ mindenképp legyen nálad, vannak akik __Github Desktopot__ is használnak mellé.   
- __Docker__ vagy __Podman__ is nélkülözhetetlen.
- __nodejs/npm__ és __python__ a naptár rész fejlesztéséhez kell
- __MySQL vagy MariaDB kliens__ ajánlott az az adatbázisban turkáláshoz.

Elsősorban __linux__ alapú fejlesztésre van minden optimalizálva, de nem lehetetlen windows és osx használata sem. Erről a végén írunk még. 




## 🚀 Indítás

### tl;dr
```sh
git clone https://github.com/borazslo/miserend.hu/
cd miserend.hu/webapp
npm ci
cd ..
chmod 777 webapp/fajlok/tmp
docker pull ghcr.io/borazslo/miserend.hu:{{ version }}
docker tag ghcr.io/borazslo/miserend.hu:{{ version }} localhost/miserend.hu:latest
docker-compose  -f docker/compose.yml -f docker/compose.dev.yml up
```

Ahol a `{{ version }}` helyére (kapcsoszárojeleket is elhagyva), azt a verziót konténer image verziót kell beírni, amelyikkel dolgozni szeretnél.

### Részletesebben

#### Letöltjük az egész repository-t a saját gépünkre.
```
git clone https://github.com/borazslo/miserend.hu/
```
2. Telepítenünk kell a Javascript/CSS függőségeket
```
cd miserend.hu/webapp
npm ci
```
#### Hozzáférést kell adni a /tmp könyvtárhoz

Jó adag ideiglenes fájlt tárolunk a gyorsabb működés érdekében. Ezek a webapp/fajlok/tmp könyvtárban gyűlnek. Alapértelmezetten viszont a weblapot kiszolgáló www-data nem tudja írni ezt a könyvtárat és sorra kapjuk a hibaüzeneteket, hogy "We could not save the cacheFile to..."
```
cd ..
chmod 777 webapp/fajlok/tmp
```
#### Miserend docker image letöltése

Természetesen magunk is felépíthetjük a helyi "miserend" docker conatinert, de sokkal gyorsabb és stabilabb, ha egy már kiadott release-t töltünk le és használunk. 

A github oldalunkon található [tag-elt release-k](https://github.com/borazslo/miserend.hu/tags)  közül válogathatunk.

Például:
```
docker pull ghcr.io/borazslo/miserend.hu:v2026.1.14
```
##### A letöltött image átnevezése
A developer környezet a l```ocalhost/miserend.hu:latest``` image-t keresi, így az előbb letöltött változatnak adjunk egy megfelelő aliast. 

Előbbi példát folytatva:
```
docker tag ghcr.io/borazslo/miserend.hu:v2026.1.14 localhost/miserend.hu:latest
```
##### Kezdődjön a móka
A docker compose valamennyi konténert szépen felépíti, bekonfigurálja, feltölti adatokkal, és elindítja:
```
docker-compose  -f docker/compose.yml -f docker/compose.dev.yml up
```

Máris elérhető a http://localhost:8000 címen a miserend alkalmazás. Az `admin` felhasználóval be is lehet lépni az alapérelmezett jelszóval: `miserend`.



# Komponensek és konténerek

Az alkalmazás öt komponensből áll.

## 🛢️ MySQL konténer azaz az adatbázis

Az adatbázis egyszerű MySQL / MariaDB. Megőrzi az adatokat újraindítás esetén is.  

Az adatbázis konténer első futtatáskor a `docker/mysql/initdb.d` könyvtár alapján inicializálja az adatbázist. Valamint a  `data` alkönytár alapján fel is töltjük minta adatokkal az adatbázist.

Ha az adatbázis sémán változtatsz, ebbe a könyvtárba vezesd be a módosításokat! Ha pedig pár committal korábban változtattak az adatbázison, akkor szükséget lehet az adatbázis újrainicializálására amire legjobb megoldás a conatiner törlése, majd -- és ez fontos -- a hozzá tartozó _volume_ törlése is. Így már újraindításnál szépen újra épül az adatbázis, bár minden korábbi saját adatbázis adatot elveszik.

Ha grafikus adatbázis elérésre lenne szükség, az [adminer](https://www.adminer.org/en/) ajánlott, egyszerűen /webapp  valamelyik könyvtárába kell tenni és már megy is. Természetesen ezt a fájl nem kell a git tárolóba elmenteni.

## Elastisearch és Kibana

Az alkalmazás keresőmotorját az Elasticsearch adja. A fent leírt standard konténer alapú telepítés során szépen elindul ez is. De kézzel kell feltölteni adatokkal legalább az első indítás után!

A templom keresőhöz a `Externalapi\ElasticsearchApi::updateChurches()` függvényt kell rendszeresen futtatni, a szentmisék kereséséhez a `Externalapi\ElasticsearchApi::updateMasses()` függvényt. Ezeket legegyszerűbb az alapértelmezett adatbázisból felálló időzített cron feladatok kézi futtatásával elindíteni:
[/index.php?q=cron&cron_id=XX](http:/localhost:8000/index.php?q=cron&cron_id=XX) és [/index.php?q=cron&cron_id=XX](http:/localhost:8000/index.php?q=cron&cron_id=XX)
Első használatkor is futtatni kell!

Vigyázat! Az 5000 misézőhelyhez évente több mint 500 ezer (!) konkrét liturgikus esemény tartozik, így az updateMass() eltarthat fél óráig is! 

### 📊 kibana

Elasticsearch admin felülete fejlesztéshez.  
Beizzítása kis varázslást igényelhet.

## Mailcatcher

Fejlesztői környezetben indul egy Mailcatcher is, ami a levelezés szimulálásában és tesztelésében segít. A mailcatcher működése esetén bármit csinálhatunk a honlapon, a keletkező emaileket sosem küldi el, hanem a mailcatcher kapja el. Így nem kell ideiglenes smtp beállításokkal nyüglődni.

Alapértelmezetten a http://localhost:11080/ oldalon lehet nyomon követni a kiküldött emaileket. 


## Miserend web alkalmazás (PHP)

A webalkalmazás fő komponense a `miserend` konténerben indul. A repó `webapp` könyvtárát a dev composer rá-mappeli a konténerre. Így ha bármit változtatsz, rögtön tesztelhető is. 

 A PHP függőségeket `composer` segítségével lehet telepíteni, a JavaScript/CSS függőségeket pedig `nodejs/npm`-el. 

Ha új PHP van NodeJS függőséget építesz be, akkor a dev composer fájlból a két volume-ot ki kell venni és a függőségeket helyben telepíteni. 

A http://localhost:8000 címen érhető el. Az admin / miserend az első felhasználó neve / jelszava.


## Naptár frontend (Angular)

A naptárnézet és naptár szerkesztő felület egy különállóm Angular alap projekt, mely a `miserend` konténerban van szintén.

A `calendar` könyvtárban található Angular alkalmazás forrása.

### 📆 Naptárnézet
- Első alkalommal le kell generálni az időszakokat:
- Admin joggal, az `/periodyeareditor` felületen
- A minta adatok idővel elévülhetnek, fontos az aktualizálásuk!

### Naptárnézet fejlesztése
Amennyiben a naptár alkalmazáson dolgozunk, az npm build után a `docker/miserend/calendar_deploy.py` szkript futtatásával lehet az alkalmazásba integrálni.


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


# Fejlesztői megjegyzések

## 🌍 Környezeti változók

Egyes beállításokat, pl. portokat, az `.env.example` fájl tartalmának átmásolásával az `.env` fájlban lehet módosítani.

- Ha a `docker up` hibát generál, mondván hogy egy port már foglalt, akkor ez lehet a megoldás. Egyébként opcionális.
- `MISEREND_WEBAPP_ENVIRONMENT` = `development` | `staging` | `production`

## Helyi build

Az alkamazásból helyben is lehet container image-t készíteni, ehhez a következő parancsot kell lefuttatni:

```sh
docker build -t miserend:latest -f docker/miserend/Dockerfile
```

Ha ki szeretnéd próbálni, hogyan működne a valóságban, akkor a [dev composer](docker/compose.dev.yml) fájlban írd ät a `miserend` service `image` attribútumát `localhost/miserend:latest`-re. 

## 🗃️ Dump készítés

Ha dump-ot szeretnénk készíteni az adatbázisról fejlesztési célra, a kényes adatok eltávolításáról gondoskodni kell, erre a `docker/mysql/dump.sh` szkript szolgál. A fájl elején lévő változóktat környezeti változóként lehet felülbírálni.


## Windows 

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



## Fájl jogosultságok

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