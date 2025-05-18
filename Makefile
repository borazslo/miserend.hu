.PHONY: dumper start install test

start:
ifeq ($(DAEMON),false)
	docker compose --profile main up -d
else
	docker compose --profile main up
endif

install:
	docker compose --profile main exec miserend sh ./scripts/install.sh

dumper:
	docker compose --profile dumper up

test:
	docker exec miserend ./vendor/bin/phpunit tests