FROM php:8.4-alpine

RUN curl -sLo /opt/phpcs https://phars.phpcodesniffer.com/phpcs.phar
RUN curl -sLo /opt/phpcbf https://phars.phpcodesniffer.com/phpcbf.phar
RUN chmod +x /opt/phpcs /opt/phpcbf
ENV PATH="$PATH:/opt"
COPY ../../check-php-style.sh /opt
RUN chmod +x /opt/check-php-style.sh