#!/bin/bash

set -euo pipefail

docker compose run --rm lmwf_dsn vendor/bin/phpunit -c dev/phpunit/phpunit.xml