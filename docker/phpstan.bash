#!/bin/bash

set -euo pipefail

docker compose run --rm lmwf_dsn vendor/bin/phpstan analyse -c dev/phpstan/phpstan.neon