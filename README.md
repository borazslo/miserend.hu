miserend.hu
========

A miserend.hu teljes forrása elavult mintaadatokkal.

## Telepítés
- [Docker](https://docs.docker.com/engine/install/) telepítése a számítógépre.
- A projekt root könyvtárában futtatni kell ezt: `docker-compose up`
  - Ha háttérben szeretnéd futtatni, akkor az utasítás végére mehet a `-d` argumentum (daemon) megadása: `docker compose up -d`
  - Windows környezetben a miserend konténer kiakad, hogy a `exec ./docker/entrypoint_miserend.sh: no such file or directory`. Megoldás az entrypoind_miserend.sh átalakítása, hogy unix sorvégeket (LF) használjon windows sorvégrek helyett (CRLF)
- Egyes beállításokat, pl. portokat, az `.env.example` fájl tartalmának átmásolásával az `.env` fájlban lehet módosítani. 
  - Ha a docker up hibát generál, mondván hogy egy port már foglalt, akkor ez lehet a megoldás. Egyébiránt opcionális.
  - `MISEREND_WEBAPP_ENVIRONMENT`= development | staging | production
- És máris elérhető miserend lokális példánya a `http://localhost:8000` (vagy amit az `.env`-ben meghatároztunk)
  - Van egy regisztrált felhasználó is: `admin` névvel és a meglepő `admin` jelszóval.

## Konténerek
A [docker-compose.yml](docker-compose.yml) a következő konténereket építi fel és indítja el:

**mysql**: Az adatbáziszerver. Ebbe tölti be a minta adatokat. A mysql adatbázisokat megőrzi későbbi leállítás / törlés esetén (`docker-compose remove mysql`) is! Az adatok törlése csak a konténerhez tartozó megfelelő *volume* törlésével lehet például a Docker Desktop alkalmazásban.

**pma**: Egy phpMyAdmin is elérhetővé válik a mysql adminisztrálás támogatására a `http://localhost:8081` címen. (A port eltérhet a `.env` beállítása alapján.) Éles környezetben ezt le kell állítani!

**mailcatcher**: Fejlesztői környezetekben az emaileket ténylegesen nem küldjük el, hanem elkapjuk őket és megtekinthetőek itt: `http://localhost:1080`. Éles környzetben ezt le kell állítani, és figyelni kell arra, hogy helyes beállítással kimenjenek ténylegesen a levelek.

**miserend**: Maga a honlap mindene. A forráskódból a /webapp rész kerül csak összekötésre / feltöltésre.

## További segítség
- Belépés az egyes konténerekbe: `docker exec -it [mysql|pma|mailcatcher|miserend] bash`
- A `mailcatcher` csak az env['production'] esetén nem lép közbe.
- Fejlesztéshez jól jöhet a `composer` használata, bár telepíti magát:  `docker exec miserend composer install|require|update`. Interactive (`-it`) módban természetesen elég a `composer...`
- [később] Unit testing: `docker exec miserend ./vendor/bin/phpunit tests`

## Néhány vegyes gondolat
  - a master branch kerül ki a staging környezetbe (staging.miserend.hu), de még nem automatikusan
  - a production branch kerül ki az éles honlapra
	
