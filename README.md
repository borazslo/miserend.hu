miserend.hu
========

A miserend.hu teljes forrása a /kepek és /fajlok kivételével. [![Build Status](https://travis-ci.org/borazslo/miserend.hu.png)](https://travis-ci.org/borazslo/miserend.hu)

## Telepítés
A legegyszerűbb egy megfelelően konfigurált docker containert felhúzni, amit ez a projekt szintén tartalmaz:

**Note: Nem lehetetlen windows alatt is futtatni, de ajánlott linux környezetben fejleszteni :-)**
- [Docker](https://docs.docker.com/engine/install/) telepítése.
- `.env.example` fájl tartalmának átmásolása `.env` fájlba (ezt létre kell hozni). Ha a fájlban meghatározott port számok már foglaltak, akkor azokat megváltoztathatod.
- A projekt root könyvtárában futtatni kell ezt: `docker compose up`
  - Ha háttérben szeretnéd futtatni, akkor az utasítás végére mehet a `-d` argumentum (daemon) megadása: `docker compose up -d`

## További segítség
- Ha minden jól ment, a miserend lokális példánya a `http://localhost:8000` (a port száma az, amit a `.env`-ben határoztál meg) érhető el.
  - A phpymadmin: `http://localhost:8081` (a port szintén eltérhet)
- Belépés a web app konténerbe: `docker exec -it miserend bash`
- Belépés a mysql konténerbe: `docker exec -it mysql bash`
- A `mailcatcher` még nincs beüzemelve a dockerbe, de tervbe van véve.
- `composer` használata:  `docker exec miserend ./composer.phar install|require|update`. Interactive (`-it`) módban természetesen elég a `./composer.phar...`
- Unit testing: `docker exec miserend ./vendor/bin/phpunit tests`

## Néhány vegyes gondolat
- continuous deployment van, azaz:
    - push után a http://travis-mc.org/borazslo/miserend.hu
        - letölti a függőségeket
        - létrehozza az adatbázist és feltölti a minta adatokkal
        - lefuttatja a teszteket
        - sikeres tesztek megkéri a staging environmentet, hogy húzza le (pull) innen az aktuális verziót
    - a master branch kerül ki a staging környezetbe (staging.miserend.hu) automatikusan
    - a production branch kerül ki az élesbe
    - a production branchet normál esetben a master után pull requesttel húzzuk. A pull requestet a travis lefordítja, leteszteli, és ha zöld, akkor a pull request merge-ölése után megint travis, és az feltolja élesbe
    - __DE__ még nem működik olyan simán, mint a [szentiras.hu](https://github.com/borazslo/szentiras.hu/wiki/Fejleszt%C5%91i-tudnival%C3%B3k#n%C3%A9h%C3%A1ny-vegyes-gondolat)! (Nincs wekiszolgáló leállítás, stb.)

## Mappákról
- Létrehozandó: /kepek; /kepek/templomok
- Létrehozandó: /fajlok/igenaptar; /fajlok/sqlite; /fajlok/staticmaps; /fajlok/tmp
