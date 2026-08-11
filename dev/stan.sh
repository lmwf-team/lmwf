#!/bin/bash

set -euo pipefail
LMWF_DIR_PATH="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"

"$LMWF_DIR_PATH/vendor/bin/phpstan" analyse -c "$LMWF_DIR_PATH/dev/phpstan/phpstan.neon" $@