FROM php:8.5-cli

# PIE
COPY --from=ghcr.io/php/pie:bin /pie /usr/bin/pie
RUN apt-get update \
    && apt-get install -y --no-install-recommends --no-install-suggests  libtool libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

# XDebug
RUN pie install xdebug/xdebug
RUN echo "" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.mode = debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.start_with_request = yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

ENV PATH="$PATH:/app/vendor/bin"