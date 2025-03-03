ARG PHP_VERSION=8.3

FROM webdevops/php-apache:${PHP_VERSION}

ARG COMPOSER_VERSION

RUN composer self-update "$COMPOSER_VERSION"

RUN mkdir -p /app

RUN chown -R application:application /app

WORKDIR /app

COPY /bin/magento_install /usr/local/bin/magento_install

RUN chmod 755 /usr/local/bin/magento_install

COPY /bin/magento_download /usr/local/bin/magento_download

RUN chmod 755 /usr/local/bin/magento_download

