miserend.hu
========

A miserend.hu teljes forrása a /kepek és /fajlok kivételével. [![Build Status](https://travis-ci.org/borazslo/miserend.hu.png)](https://travis-ci.org/borazslo/miserend.hu)

## Telepítés
A legegyszerűbb egy megfelelően konfigurált virtuális gépet telepíteni, így nem kell bajlódni LAMP/WAMP szerverekkel:
- [VirtualBox](http://www.virtualbox.org/), [Vagrant](https://www.vagrantup.com/) és [GitHub Desktop](https://desktop.github.com/) telepítése.
- A GitHub Desktopban ennek a forrásnak a [klónozása](https://help.github.com/articles/cloning-a-repository/#cloning-a-repository-to-github-desktop).
- Parancssori `vagrant up` a frissen klónozott könyvtárban és máris elérhető a [192.168.33.10](http://192.168.33.10)

## További segítség
- A virtuális gép a http://192.168.33.10/ címen érhető el. 
- SSH, mySQL, Mailcatcher, stb. eléréséhez valamint a virtuális gép irányításához lásd: [box.scotch.io](https://box.scotch.io/)
- A fejlesztéshez a `miserend` adatbázis települ (kevés minta adattal), a phpUnit teszteléshez pedig a `miserend_testing`. (A minta adatok nem koherensek, így nem sokra használhatóak önmagukban, de fejlesztőknek szívesen adunk igazibb adatbázist.)
- Ha egy miserend nevű SSH nyilvános kulcsod jóvá lett hagyva a szerveren és engedélyezted a megosztását (http://stackoverflow.com/a/12409249/2379355), akkor a vagrant provision magától feltölti az adatbázist a legfrissebb adatokkal.
- A [MailCatcher](http://mailcatcher.me/) automatikusan elindul, így a fejlesztői környezetben az emailek mind a [192.168.33.10:1080](http://192.168.33.10:1080) oldalra futnak be.
- [NetBeans](https://netbeans.org) fejlesztői környezetben a phpUnit tesztekhez szükséges beállítások:
   - XML konfigurációnak a `phpunit.xml`-t kell megadni.
   - Egyéni scriptnek pedig a `phpunitOnVagrant.sh` fájlt. 

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
