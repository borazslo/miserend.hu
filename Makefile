.PHONY: dumper start

start:
	docker compose --profile main up

dumper:
	docker compose --profile dumper up