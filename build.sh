#!/bin/bash
set -e

if [ ! -d "/app/pub" ]; then
  mkdir /app/pub
fi

echo "<h1>We are setting up your Magento installation. Please, try again later</h1><p>You can see the installation progress using: docker logs setup -f" > /app/pub/index.html

composer self-update "$COMPOSER_VERSION"

hash composer 2>/dev/null && IS_COMPOSER_ON_HOST=true

echo "Waiting for connection to Elasticsearch..."
timeout 100 bash -c "
    until curl --silent --output /dev/null http://elasticsearch:9200/_cat/health?h=st; do
        printf '.'
        sleep 2
    done"
[ $? != 0 ] && echo "Failed to connect to Elasticsearch" && exit

composer config --global http-basic.repo.magento.com "$COMPOSER_AUTH_USERNAME" "$COMPOSER_AUTH_PASSWORD"

mkdir /src

php -d memory_limit=-1 /usr/local/bin/composer create-project --repository=https://repo.magento.com/ magento/project-"$MAGENTO_EDITION"-edition="$MAGENTO_VERSION" /src

chmod u+x /src/bin/magento

chown -R www-data:www-data /src
chmod -R 777 /src

if [[ "$MAGENTO_VERSION" == *"2.3."* ]]; then
  php /src/bin/magento setup:install \
	--admin-firstname="$MAGENTO_ADMIN_FIRST_NAME" \
	--admin-lastname="$MAGENTO_ADMIN_LAST_NAME" \
	--admin-email="$MAGENTO_ADMIN_EMAIL" \
	--admin-user="$MAGENTO_ADMIN_USER" \
	--admin-password="$MAGENTO_ADMIN_PASSWORD" \
	--base-url=https://"$MAGENTO_BASE_URL" \
	--base-url-secure=https://"$MAGENTO_BASE_URL" \
	--backend-frontname="$MAGENTO_ADMIN_FRONTNAME" \
	--db-host=db \
	--db-name="$MYSQL_DATABASE" \
	--db-user="$MYSQL_USER" \
	--db-password="$MYSQL_PASSWORD" \
	--use-rewrites=1 \
	--language="$MAGENTO_LOCALE" \
	--currency="$MAGENTO_CURRENCY" \
	--timezone="$MAGENTO_TIMEZONE" \
	--use-secure-admin=1
fi

if [[ "$MAGENTO_VERSION" == *"2.4."* ]]; then
  php /src/bin/magento setup:install \
	--admin-firstname="$MAGENTO_ADMIN_FIRST_NAME" \
	--admin-lastname="$MAGENTO_ADMIN_LAST_NAME" \
	--admin-email="$MAGENTO_ADMIN_EMAIL" \
	--admin-user="$MAGENTO_ADMIN_USER" \
	--admin-password="$MAGENTO_ADMIN_PASSWORD" \
	--base-url=https://"$MAGENTO_BASE_URL" \
	--base-url-secure=https://"$MAGENTO_BASE_URL" \
	--backend-frontname="$MAGENTO_ADMIN_FRONTNAME" \
	--db-host=db \
	--db-name="$MYSQL_DATABASE" \
	--db-user="$MYSQL_USER" \
	--db-password="$MYSQL_PASSWORD" \
	--use-rewrites=1 \
	--language="$MAGENTO_LOCALE" \
	--currency="$MAGENTO_CURRENCY" \
	--timezone="$MAGENTO_TIMEZONE" \
	--use-secure-admin=1 \
	--search-engine=elasticsearch7 \
	--elasticsearch-host=elasticsearch
fi

mkdir /src/app/code
mkdir /src/app/code/Doofinder
mkdir /src/app/code/Doofinder/Feed
cp -r /package/* /src/app/code/Doofinder/Feed
cp -r /src/* /app
chown -R www-data:www-data /app
chmod -R 777 /app

rm /app/pub/index.html

php /app/bin/magento setup:upgrade
php /app/bin/magento setup:di:compile
php /app/bin/magento indexer:reindex
php /app/bin/magento setup:static-content:deploy es_ES en_US -f

if [[ "$MAGENTO_VERSION" == *"2.4."* ]]; then
  php /app/bin/magento module:disable Magento_TwoFactorAuth --clear-static-content
fi

php /app/bin/magento cache:flush

chown -R www-data:www-data /app
chmod -R 777 /app

if [[ "$MAGENTO_VERSION" == *"2.3."* ]]; then
  sed -i 's/xdebug_disable();/ /g' /app/vendor/magento/magento2-functional-testing-framework/src/Magento/FunctionalTestingFramework/_bootstrap.php
fi

echo "Docker development environment setup complete."
echo "You may now access your Magento instance"
