ARG PHP_VERSION=8.3

FROM webdevops/php-apache:${PHP_VERSION}

ARG COMPOSER_VERSION
ARG MAGENTO_EDITION
ARG MAGENTO_VERSION

RUN composer self-update "$COMPOSER_VERSION"

RUN mkdir -p /app

RUN chown -R application:application /app

USER application

WORKDIR /app

RUN --mount=type=secret,id=composer_auth_username,env=COMPOSER_AUTH_USERNAME \
    --mount=type=secret,id=composer_auth_password,env=COMPOSER_AUTH_PASSWORD \
    composer config --global http-basic.repo.magento.com "$COMPOSER_AUTH_USERNAME" "$COMPOSER_AUTH_PASSWORD"

RUN php -d memory_limit=-1 /usr/local/bin/composer create-project \
    --repository=https://repo.magento.com/ magento/project-$MAGENTO_EDITION-edition=$MAGENTO_VERSION /app

RUN composer config --global --unset http-basic.repo.magento.com

USER root

COPY /bin/magento_install /usr/local/bin/magento_install

RUN chmod 755 /usr/local/bin/magento_install

RUN chown application:application /usr/local/bin/magento_install
