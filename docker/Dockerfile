ARG PHP_VERSION=8.3

FROM webdevops/php-apache:${PHP_VERSION}

ARG COMPOSER_VERSION
ARG COMPOSER_AUTH_USERNAME
ARG COMPOSER_AUTH_PASSWORD
ARG MAGENTO_EDITION
ARG MAGENTO_VERSION

RUN composer self-update "$COMPOSER_VERSION"

RUN mkdir -p /app

RUN chown -R application:application /app

USER application

WORKDIR /app

RUN composer config --global http-basic.repo.magento.com "$COMPOSER_AUTH_USERNAME" "$COMPOSER_AUTH_PASSWORD"

RUN php -d memory_limit=-1 /usr/local/bin/composer create-project \
    --repository=https://repo.magento.com/ magento/project-"$MAGENTO_EDITION"-edition="$MAGENTO_VERSION" /app

USER root

COPY /bin/install_magento /usr/local/bin/install_magento

RUN chmod 755 /usr/local/bin/install_magento

RUN chown application:application /usr/local/bin/install_magento
