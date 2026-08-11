FROM php:8.4

COPY --from=composer/composer:latest-bin /composer /usr/local/bin/composer
COPY init.sh /usr/local

WORKDIR /usr/src/myapp
CMD bash