#!/bin/bash

set -euo pipefail

SDIR=$(dirname ${BASH_SOURCE[0]})/..
docker compose cp $SDIR/composer.json composer_dsn:/app
docker compose cp $SDIR/composer.lock composer_dsn:/app
docker compose exec composer_dsn composer $@ --ignore-platform-reqs
docker compose cp composer_dsn:/app/composer.json $SDIR
docker compose cp composer_dsn:/app/composer.lock $SDIR