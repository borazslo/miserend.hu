miserend.hu
========

A miserend.hu teljes forrása a /kepek és /fajlok kivételével. [![Build Status](https://travis-ci.org/borazslo/miserend.hu.png)](https://travis-ci.org/borazslo/miserend.hu)

Az adatbázis struktúrát a mysql_sample.sql tartalmazza, némi minta adattal együtt.
(A minta adatok nem koherensek, így nem sokra használhatóak önmagukban. De fejlesztőknek szívesen adunk igazibb adatbázist.)

##Előfeltétel
- (L)AMP szerver
 - Több helyen még kell a http://php.net/manual/en/language.basic-syntax.phptags.php
- [Git telepítése](http://git-scm.com/book/en/Getting-Started-Installing-Git) és [beállítása](http://git-scm.com/book/en/Getting-Started-First-Time-Git-Setup).
- `sudo apt-get install php5-sqlite`
- `sudo apt-get install nodejs`
- `npm install`
- `npm install -g bower`

##Telepítés
- `git clone https://github.com/borazslo/miserend.hu.git`
- MySQL elérhetőség megadása a `config.inc`-ben vagy környezeti változóként (SetEnv/Export).
- `php composer.phar selfupdate`
- `php composer.phar install`
- `php install.php` (Betölti a minta adatbázist. Létrehozza a hiányzó könyvtárakat.)

## Néhány vegyes gondolat
- continuous deployment van, azaz:
    - push után a travis-mc.org/borazslo/miserend.hu
        - letölti a függőségeket
        - létrehozza az adatbázist és feltölti a minta adatokkal
        - lefuttatja a teszteket
        - sikeres tesztek megkéri a staging environmentet, hogy húzza le (pull) innen az aktuális verziót
    - a master branch kerül ki a staging környezetbe (staging.miserend.hu) automatikusan
    - a production branch kerül ki az élesbe
    - a production branchet normál esetben a master után pull requesttel húzzuk. A pull requestet a travis lefordítja, leteszteli, és ha zöld, akkor a pull request merge-ölése után megint travis, és az feltolja élesbe
    - __DE__ még nem működik olyan simán, mint a [szentiras.hu](https://github.com/borazslo/szentiras.hu/wiki/Fejleszt%C5%91i-tudnival%C3%B3k#n%C3%A9h%C3%A1ny-vegyes-gondolat)! (Nincs wekiszolgáló leállítás, stb.)
      
##Mappákról
- Létrehozandó: /kepek; /kepek/templomok
- Létrehozandó: /fajlok/igenaptar; /fajlok/sqlite; /fajlok/staticmaps; /fajlok/tmp
- Törölhető a /terkep mappa. Az egykori terkep.miserend.hu teljes, de nem működő anyaga.
