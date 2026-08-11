#!/bin/bash

set -euo pipefail

SDIR=$(dirname "${BASH_SOURCE[0]}")
phpstan analyse -c "$SDIR/phpstan.neon" $@