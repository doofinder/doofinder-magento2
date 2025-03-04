.PHONY: all backup-db cache-flush check-env clean consistency console doofinder-configure doofinder-reinstall doofinder-uninstall doofinder-upgrade init init-with-data restore-db setup start stop

# Include environment variables from .env file
include .env
export

ifeq ($(DOOFINDER_LOCAL),true)
	include .env.local
	export
endif

# Shortcut for executing commands in the web container as the 'application' user
ifeq ($(DOOFINDER_LOCAL),true)
	docker_compose = docker compose --env-file .env --env-file .env.local
else
	docker_compose = docker compose
endif

docker_exec_web = $(docker_compose) exec -u application web

# Default target: list available tasks
all:
	@echo "Before \`make init\` be sure to set up your environment with a proper \`.env\` file."
	@echo "Select a task defined in the Makefile:"
	@echo "  command, console, start, stop, backup-db, restore-db, upgrade-doofinder,"
	@echo "  uninstall-doofinder, reinstall-doofinder, cache-flush, setup, load-sampledata,"
	@echo "  setup-with-data, compliance"

check-env:
ifeq ($(DOOFINDER_LOCAL),true)
	@echo "\e[3;32;44mDOOFINDER_LOCAL mode=ON\n"
endif
ifeq ($(COMPOSER_AUTH_USERNAME),"")
	$(error COMPOSER_AUTH_USERNAME is undefined.)
endif
ifeq ($(COMPOSER_AUTH_PASSWORD),"")
	$(error COMPOSER_AUTH_PASSWORD is undefined.)
endif
ifeq ($(MAGENTO_BASE_URL),"")
	$(error MAGENTO_BASE_URL is undefined. Please be sure all environment variables from `.env.example` are defined and correct.)
endif

# Backup the MySQL database from the 'db' container and compress the output
backup-db:
	$(docker_compose) exec db /usr/bin/mysqldump -u root -pmagentobase magentobase | gzip > backup_$(shell date +%Y%m%d%H%M%S)$(prefix).sql.gz

# Restore the MySQL database using a provided backup file (pass file=<backupfile> as argument)
restore-db:
	@test -z "$(file)" && echo "Error: 'file' variable not provided. Use file=<backupfile>" && exit 1;
	gunzip < $(file) | $(docker_compose) exec -T db /usr/bin/mysql -u root -pmagentobase magentobase

# Configures extension static files
doofinder-configure: check-env
	@envsubst < templates/etc/config.xml > Doofinder/Feed/etc/config.xml

# Enable the Doofinder module, upgrade Magento, and clean the cache
doofinder-upgrade: doofinder-configure
	$(docker_exec_web) php bin/magento module:enable Doofinder_Feed --clear-static-content
	$(docker_exec_web) php bin/magento setup:upgrade
	$(docker_exec_web) php bin/magento cache:clean

# Disable the Doofinder module, upgrade Magento, and clean the cache
doofinder-uninstall: doofinder-configure
	$(docker_exec_web) php bin/magento module:disable Doofinder_Feed --clear-static-content
	$(docker_exec_web) php bin/magento setup:upgrade
	$(docker_exec_web) php bin/magento cache:clean

# Reinstall Doofinder: disable then re-enable the module
doofinder-reinstall: doofinder-uninstall doofinder-upgrade

# Flush the Magento cache
cache-flush:
	$(docker_exec_web) php bin/magento cache:flush

# Build Docker images, install Magento, and start containers
init: doofinder-configure setup start
	$(docker_compose) pull --ignore-buildable
	$(docker_compose) build
	$(docker_compose) run --rm setup
	$(docker_exec_web) magento_install

init-with-data: init
	$(docker_exec_web) php bin/magento sampledata:deploy
	$(docker_exec_web) php bin/magento setup:upgrade

# Check code consitency for the Doofinder Feed module using PHP Code Sniffer
consistency:
	$(docker_exec_web) vendor/bin/phpcs -vs --standard=Magento2 app/code/Doofinder/Feed/

# Open an interactive shell in the web container as the 'application' user
console:
	$(docker_exec_web) bash

# Start the Magento Docker containers
start: doofinder-configure
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
	$(docker_compose) down -v
	sudo rm -rf ./app