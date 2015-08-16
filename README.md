miserend.hu
========
egyelőre hirporta.hu-val egyben

A miserend.hu teljes forrása honlap a /kepek és /fajlok kivételével.

Az adatbázis struktúrát a mysql_sample.sql tartalmazza, némi adattal együtt.

Előfeltétel
- LAMP szerver
-- Több helyen még kell a http://php.net/manual/en/language.basic-syntax.phptags.php
- Composer: https://getcomposer.org/doc/00-intro.md#globally
- sudo apt-get install php5-sqlite

Telepítés
- Klónozás 
- config.inc-ben be kell állítani a mysql nevet/jelszót.
- mysql betöltése
- adatbázisban a fooldal táblába egy új sort beírni
- php composer.phar



http://stackoverflow.com/questions/22772854/attempting-to-install-oauth-on-ubuntu-but-getting-errors
- apt-get update
- apt-get install libpcre3 libpcre3-dev
- pecl install oauth

A /terkep mappába bekerült a terkep.miserend.hu teljes anyaga, ami hamarosan meg is szűnik. Ezt még szebben be kell olvasztani a főkódba.
A /terkep facebook belépése nem üzemel, mert más url van regisztrálva a facebook appban.
