#!/bin/bash

set -euo pipefail

docker compose exec lmwf_dsn vendor/bin/phpstan analyse -c dev/phpstan/phpstan.neon --memory-limit 256M