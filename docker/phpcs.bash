#!/bin/bash

# Used for local development, not for CI. For CI, check /dev/phpcs/phpcs.sh.

set -euo pipefail

docker compose exec lmwf_dsn phpcs -n src
docker compose exec lmwf_dsn phpcs -n --exclude=PSR1.Classes.ClassDeclaration tests