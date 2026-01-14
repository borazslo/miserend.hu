A több különböző Dockerfile használata magyarázatra szorul.

A [miserend/Dockerfile](miserend/Dockerfile) egyben tartalmazza az összes fordítási lépést, ebbe beleértve a node modulok telepítését, a calendar fordítását és a PHP modulok fordítását is. Ez gyakori CI/CD környezetbel buildek-hez (ahol nem feltétlenül van cache, vagy korlátozottak a kapacitások) nem praktikus.

CI/CD környezetben (lásd [.github/workflows](.github/workflows)) egy php base image-dszel [php-base](php-base/Dockerfile) dolgozunk, ez [.github/workflows/php-base.yaml](.github/workflows/php-base.yaml) alapján hetente egyszer, ill. a php függőségek változásakor triggerelődik.

Ha bármelyek commit-et tag-eljük a tárolóban, az [.github/workflows/app-release.yaml](.github/workflows/app-release.yaml) munkafolyamat helyben előkészíti a npm függőségeket (ezek cache-elve is vannak), majd futtatja a [miserend/Dockerfile.github](miserend/Dockerfile.github) Dockerfile-t, amelynek alapja a php-base image-ünk, és így jóval egyszerűbb, mint a helyi build-re szolgáló változat.