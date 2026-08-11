#!/bin/bash

set -euo pipefail

SDIR=$(dirname ${BASH_SOURCE[0]})/..
docker compose cp $SDIR/composer.json composer_dsn:/app
docker compose cp $SDIR/composer.lock composer_dsn:/app
docker compose exec -it composer_dsn composer install --ignore-platform-reqs