#!/bin/bash

set -euo pipefail

docker compose exec lmwf_dsn vendor/bin/phpunit -c dev/phpunit/phpunit.xml "$@"