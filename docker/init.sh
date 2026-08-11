#!/bin/sh

apt update -y
apt upgrade -y
apt install git unzip zip -y
curl -o /usr/local/bin/phpunit -L https://phar.phpunit.de/phpunit-12.phar
chmod +x /usr/local/bin/phpunit