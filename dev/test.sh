#!/bin/bash

set -euo pipefail
LMWF_DIR_PATH="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"

"$LMWF_DIR_PATH/vendor/bin/phpunit" -c "$LMWF_DIR_PATH/phpunit.xml" $@