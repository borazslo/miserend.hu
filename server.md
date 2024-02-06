# Gondolatok a szerverrel kapcsolatban

Szükséges a caddy elindítása: `sudo caddy start --config /etc/caddy/Caddyfile`

Aztán a docker: `docker compose miserend up -d`

Ha teljes mysqldump másolásra van szükség: 

```
 docker cp [local sql file] mysql:/file.sql
 docker exec -it -u root mysql bash
 mysql -u user -p miserend < file.sql
```

