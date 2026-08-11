#!/bin/bash

set -euo pipefail

SDIR=$(dirname "${BASH_SOURCE[0]}")
"$SDIR/phpcs/phpcs.sh"
"$SDIR/phpunit/phpunit.sh"
"$SDIR/phpstan/phpstan.sh"