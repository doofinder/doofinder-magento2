.PHONY: all command console start stop backup-db restore-db upgrade-doofinder uninstall-doofinder reinstall-doofinder cache-flush setup load-sampledata setup-with-data compliance

# Include environment variables from .env file
include .env
export

ifeq ($(DOOFINDER_LOCAL),true)
	include .env.local
	export
endif

# Retrieve additional arguments passed to make (e.g., for the 'command' target)
ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
$(eval $(ARGS):;@:)

# Shortcut for executing commands in the web container as the 'application' user
ifeq ($(DOOFINDER_LOCAL),true)
	docker_compose = docker compose --env-file .env --env-file .env.local
else
	docker_compose = docker compose
endif

docker_exec_web = $(docker_compose) exec -u application web
docker_run_web = $(docker_compose) run --rm web su application -c

# Default target: list available tasks
all:
	@echo "Select a task defined in the Makefile:"
	@echo "  command, console, start, stop, backup-db, restore-db, upgrade-doofinder,"
	@echo "  uninstall-doofinder, reinstall-doofinder, cache-flush, setup, load-sampledata,"
	@echo "  setup-with-data, compliance"

# Configures extension static files
configure:
	@envsubst < templates/etc/config.xml > Doofinder/Feed/etc/config.xml

# Backup the MySQL database from the 'db' container and compress the output
backup-db:
	$(docker_compose) exec db /usr/bin/mysqldump -u root -pmagentobase magentobase | gzip > backup_$(shell date +%Y%m%d%H%M%S)$(prefix).sql.gz

# Restore the MySQL database using a provided backup file (pass file=<backupfile> as argument)
restore-db:
	@test -z "$(file)" && echo "Error: 'file' variable not provided. Use file=<backupfile>" && exit 1;
	gunzip < $(file) | $(docker_compose) exec -T db /usr/bin/mysql -u root -pmagentobase magentobase

# Enable the Doofinder module, upgrade Magento, and clean the cache
upgrade-doofinder: configure
	$(docker_exec_web) php bin/magento module:enable Doofinder_Feed --clear-static-content
	$(docker_exec_web) php bin/magento setup:upgrade
	$(docker_exec_web) php bin/magento cache:clean

# Disable the Doofinder module, upgrade Magento, and clean the cache
uninstall-doofinder: configure
	$(docker_exec_web) php bin/magento module:disable Doofinder_Feed --clear-static-content
	$(docker_exec_web) php bin/magento setup:upgrade
	$(docker_exec_web) php bin/magento cache:clean

# Reinstall Doofinder: disable then re-enable the module
reinstall-doofinder: uninstall-doofinder upgrade-doofinder

# Flush the Magento cache
cache-flush:
	$(docker_exec_web) php bin/magento cache:flush

# Build Docker images, install Magento, and start containers
init: setup start magento-install

init-with-data: init magento-load-sampledata

# Same as init but do not start
setup: configure pull-build magento-download

# Pull and build project images
pull-build:
	$(docker_compose) pull --ignore-buildable
	$(docker_compose) build

# Downloads and update a magento project
magento-download:
	$(docker_compose) run --rm setup

# Install a magento site
magento-install: configure start
	$(docker_exec_web) magento_install

# Deploy sample data and upgrade Magento
magento-load-sampledata: configure
	$(docker_exec_web) php bin/magento sampledata:deploy
	$(docker_exec_web) php bin/magento setup:upgrade

# Check code compliance for the Doofinder Feed module using PHP Code Sniffer
compliance:
	$(docker_exec_web) vendor/bin/phpcs -vs --standard=Magento2 app/code/Doofinder/Feed/

# Execute an arbitrary command in the web container (pass additional arguments)
command:
	$(docker_compose) run --rm web $(ARGS)

# Open an interactive shell in the web container as the 'application' user
console:
	$(docker_compose) exec -u application web bash

# Start the Magento Docker containers
start: configure
	@echo "(Magento) Starting"
	@$(docker_compose) up -d
	@echo "(Magento) Started"

# Stop the Magento Docker containers
stop:
	@echo "(Magento) Stopping"
	@$(docker_compose) down
	@echo "(Magento) Stopped"

clean:
	@echo -n "Are you sure, this will delete volumes and ./app directory? [y/N] " && read ans && [ $${ans:-N} = y ]
	docker compose down -v
	sudo rm -rf ./app