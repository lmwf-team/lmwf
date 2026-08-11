#!/bin/bash

set -euo pipefail
LMWF_DIR_PATH="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"

"$LMWF_DIR_PATH/vendor/bin/phpcbf" "$LMWF_DIR_PATH/src" "$LMWF_DIR_PATH/tests"