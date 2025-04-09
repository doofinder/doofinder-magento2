.PHONY: all cache-flush clean consistency db-backup db-restore dev-console doofinder-configure doofinder-reinstall doofinder-uninstall doofinder-upgrade init init-with-data start stop


# Include environment variables from .env file
ifeq ("$(wildcard .env)","")
	$(error Please be sure a `.env` file is present in the root directory. You can make a copy of `.env.example`)
endif

include .env
export

ifeq ($(origin COMPOSER_AUTH_USERNAME), undefined)
  $(error COMPOSER_AUTH_USERNAME is undefined.)
endif

ifeq ($(origin COMPOSER_AUTH_PASSWORD), undefined)
  $(error COMPOSER_AUTH_PASSWORD is undefined.)
endif

docker_compose ?= docker compose
ifneq ("$(wildcard .env.local)","")
	include .env.local
	export
	docker_compose = docker compose --env-file .env --env-file .env.local
endif

docker_exec_web = $(docker_compose) exec -u application web

# Default target: list available tasks
all:
	@echo "Before \`make init\` be sure to set up your environment with a proper \`.env\` file."
	@echo "Select a task defined in the Makefile:"
	@echo "  all, cache-flush, clean, consistency, db-backup, db-restore, dev-console,"
	@echo "  doofinder-configure, doofinder-reinstall, doofinder-uninstall,"
	@echo "  doofinder-upgrade, init, init-with-data, start, stop"

# Backup the MySQL database from the 'db' container and compress the output
db-backup:
	$(docker_compose) exec db /usr/bin/mysqldump -u root -p$(MYSQL_PASSWORD)  $(MYSQL_DATABASE) | gzip > backup_$(shell date +%Y%m%d%H%M%S)$(prefix).sql.gz

# Restore the MySQL database using a provided backup file (pass file=<backupfile> as argument)
db-restore:
	@[ -e "$(file)" ] || (echo "Error: 'file' variable not provided. Use file=<backupfile>" && exit 1)
	gunzip < $(file) | $(docker_compose) exec -T db /usr/bin/mysql -u root -p$(MYSQL_PASSWORD)  $(MYSQL_DATABASE)

# Configures extension static files
doofinder-configure:
	@envsubst < templates/etc/config.xml > Doofinder/Feed/etc/config.xml

# Enable the Doofinder module, upgrade Magento, and clean the cache
doofinder-upgrade: doofinder-configure
	$(docker_exec_web) php bin/magento module:enable Doofinder_Feed
	$(docker_exec_web) php bin/magento setup:upgrade

# Disable the Doofinder module, upgrade Magento, and clean the cache
doofinder-uninstall: doofinder-configure
	$(docker_exec_web) php bin/magento module:disable Doofinder_Feed --clear-static-content
	$(docker_compose) exec db /usr/bin/mysql -u root -p$(MYSQL_PASSWORD) $(MYSQL_DATABASE) -e "DELETE FROM setup_module WHERE module = 'Doofinder_Feed';"
	$(docker_compose) exec db /usr/bin/mysql -u root -p$(MYSQL_PASSWORD) $(MYSQL_DATABASE) -e "DELETE FROM core_config_data WHERE path LIKE 'doofinder_config_config%';"
	$(docker_compose) exec db /usr/bin/mysql -u root -p$(MYSQL_PASSWORD) $(MYSQL_DATABASE) -e "DELETE FROM integration WHERE name = 'Doofinder Integration';"
	$(docker_exec_web) php bin/magento setup:upgrade

# Reinstall Doofinder: disable then re-enable the module
doofinder-reinstall: doofinder-uninstall doofinder-upgrade

# Flush the Magento cache
cache-flush:
	$(docker_exec_web) php bin/magento cache:flush

# Build Docker images, install Magento, and start containers
init: doofinder-configure
	$(docker_compose) pull --ignore-buildable
	$(docker_compose) build
	$(docker_compose) run --rm setup
	$(docker_compose) up -d
	$(docker_exec_web) magento_install

init-with-data: init
	$(docker_exec_web) php -d memory_limit=-1 bin/magento sampledata:deploy
	$(docker_exec_web) php bin/magento setup:upgrade

# Check code consitency for the Doofinder Feed module using PHP Code Sniffer
consistency:
	$(docker_exec_web) vendor/bin/phpcs -vs --standard=Magento2 app/code/Doofinder/Feed/

# Open an interactive shell in the web container as the 'application' user
dev-console:
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
	@echo "\033[33m⚠️ WARNING ⚠️\033[0m"
	@echo "This will permanently delete"
	@echo "  - All Docker volumes for this project"
	@echo "  - The entire ./app directory, including all Magento files"
	@echo -n "Type 'DELETE' to confirm removing all volumes and ./app directory: " && read ans && [ "$${ans}" = "DELETE" ]
	$(docker_compose) down -v
	sudo rm -rf ./app

docs:
	docker run --rm --volume "$(shell pwd)/doc:/data" --user $(shell id -u):$(shell id -g) \
		pandoc/extra --pdf-engine=xelatex manual.md -o Manual.pdf --template ./template/eisvogel \
		--listings --filter pandoc-latex-environment --toc
	mv ./doc/Manual.pdf ./Doofinder/Feed/Manual.pdf
