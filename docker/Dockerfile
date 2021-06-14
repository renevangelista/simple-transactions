FROM php:7.3-fpm-alpine3.13

COPY . /var/www/app

WORKDIR /var/www/app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
RUN rm -rf composer-setup.php
RUN composer install

RUN apk add bash
RUN apk add supervisor

COPY /docker/worker.conf /etc/supervisor.d/worker.ini

RUN chown -R :www-data /var/www/app && \
    chmod -R 750 /var/www/app  && \
    chmod g+s /var/www/app && \
    chmod -R 777 /var/www/app/public && \
    chmod -R 777 /var/www/app/storage && \
    chmod -R 777 /var/www/app/bootstrap/cache

#Insere aspas nas variáveis de ambiente
ENV GPG_KEYS=\'${GPG_KEYS}\'
ENV PHP_CFLAGS=\'${PHP_CFLAGS}\'
ENV PHP_CPPFLAGS=\'${PHP_CPPFLAGS}\'
ENV PHP_EXTRA_CONFIGURE_ARGS=\'${PHP_EXTRA_CONFIGURE_ARGS}\'
ENV PHP_LDFLAGS=\'${PHP_LDFLAGS}\'
ENV PHPIZE_DEPS=\'${PHPIZE_DEPS}\'

RUN chmod +x /var/www/app/docker/entrypoint.sh

ENTRYPOINT ["docker/entrypoint.sh"]
