.PHONY: all command console start stop

ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
$(eval $(ARGS):;@:)

all:
	@echo Select some task of the defined in the Makefile.

command:
	@docker compose run --rm -u application web $(ARGS)

console:
	@docker compose exec -u application web bash

start:
	@echo "(Magento) Starting"
	@docker compose up -d
	@echo "(Magento) Started"

stop:
	@echo "(Magento) Stopping"
	@docker compose down
	@echo "(Magento) Stopped"
