FROM php:8.5-cli

COPY --from=ghcr.io/php/pie:bin /pie /usr/bin/pie
RUN apt-get update \
    && apt-get install -y --no-install-recommends --no-install-suggests  libtool libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*
RUN pie install xdebug/xdebug

ENV PATH="$PATH:/app/vendor/bin"