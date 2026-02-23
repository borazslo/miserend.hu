Ez a konténer egy előkészített tar.gz állományból inicializálja az Elasticsearch adatbázist. Ez egy fájlrendszer snapshot, ezt használva sokkal gyorsabban kapunk egy előre feltöltött Elasticsearch adtabázist, mintha az adatokat pl. JSON formátumból importálnánk.

A konténert az Elasticsearch indítása előtt szerepel a `compose.yaml` fájlban. Ha az Elasticsearch volume már bármilyen adatot tartalmaz, akkor a snapshot nem kerül kitömörítésre. 

A tar.gz állományt a GitHub workflow tölti le @krisek nextcloudjából, de máshová is elhelyezhető–a repóba azért nem került be mert 240MB bináris adatot nem elegáns a miserend.hu forráskódjával együtt tárolni.

Az inicializáló konténert GitHUb workflow-val lehet elkészíteni, ezt kézzel kell elindítani; alapértelmezetten `dev` tag-et kap az image, de más is beállítható; a `compose.yaml` docker compose fájlt kell megfelelően módosítani.

Ezzel a módszerrel az Elasticsearch snapshot-okat standard konténerként tudjuk elérhetővé tenni.