FROM php:8.3-cli

RUN apt update -y
RUN apt upgrade -y
RUN apt install git -y
COPY --from=composer/composer:latest-bin /composer /usr/local/bin/composer

WORKDIR /usr/src/myapp
CMD bash