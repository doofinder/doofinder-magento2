.PHONY: all command console start stop backup-db restore-db upgrade-doofinder uninstall-doofinder reinstall-doofinder cache-flush setup load-sampledata setup-with-data compliance

# Include environment variables from .env file
include .env
export

# Retrieve additional arguments passed to make (e.g., for the 'command' target)
ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
$(eval $(ARGS):;@:)

# Shortcut for executing commands in the web container as the 'application' user
docker_exec_web = docker compose exec -u application web

# Default target: list available tasks
all:
	@echo "Select a task defined in the Makefile:"
	@echo "  command, console, start, stop, backup-db, restore-db, upgrade-doofinder,"
	@echo "  uninstall-doofinder, reinstall-doofinder, cache-flush, setup, load-sampledata,"
	@echo "  setup-with-data, compliance"

# Backup the MySQL database from the 'db' container and compress the output
backup-db:
	docker compose exec db /usr/bin/mysqldump -u root -pmagentobase magentobase | gzip > backup_$(shell date +%Y%m%d%H%M%S)$(prefix).sql.gz

# Restore the MySQL database using a provided backup file (pass file=<backupfile> as argument)
restore-db:
	gunzip < $(file) | docker compose exec -T db /usr/bin/mysql -u root -pmagentobase magentobase

# Enable the Doofinder module, upgrade Magento, and clean the cache
upgrade-doofinder:
	$(docker_exec_web) php bin/magento module:enable Doofinder_Feed --clear-static-content
	$(docker_exec_web) php bin/magento setup:upgrade
	$(docker_exec_web) php bin/magento cache:clean

# Disable the Doofinder module, upgrade Magento, and clean the cache
uninstall-doofinder:
	$(docker_exec_web) php bin/magento module:disable Doofinder_Feed --clear-static-content
	$(docker_exec_web) php bin/magento setup:upgrade
	$(docker_exec_web) php bin/magento cache:clean

# Reinstall Doofinder: disable then re-enable the module
reinstall-doofinder: uninstall-doofinder upgrade-doofinder

# Flush the Magento cache
cache-flush:
	$(docker_exec_web) php bin/magento cache:flush

# Build Docker images, install Magento, and start containers
setup:
	docker compose build
	docker compose run --rm web su application -c magento_install
	docker compose up -d

# Deploy sample data and upgrade Magento
load-sampledata:
	$(docker_exec_web) php bin/magento sampledata:deploy
	$(docker_exec_web) php bin/magento setup:upgrade

# Setup Magento and deploy sample data
setup-with-data: setup load-sampledata

# Check code compliance for the Doofinder Feed module using PHP Code Sniffer
compliance:
	$(docker_exec_web) vendor/bin/phpcs -vs --standard=Magento2 app/code/Doofinder/Feed/

# Execute an arbitrary command in the web container (pass additional arguments)
command:
	@docker compose run --rm web $(ARGS)

# Open an interactive shell in the web container as the 'application' user
console:
	@docker compose exec -u application web bash

# Start the Magento Docker containers
start:
	@echo "(Magento) Starting"
	@docker compose up -d
	@echo "(Magento) Started"

# Stop the Magento Docker containers
stop:
	@echo "(Magento) Stopping"
	@docker compose down
	@echo "(Magento) Stopped"
