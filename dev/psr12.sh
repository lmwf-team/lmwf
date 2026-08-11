#!/bin/bash
set -euo pipefail
LMWF_DIR_PATH="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"

"$LMWF_DIR_PATH/vendor/bin/phpcs" -qn --runtime-set ignore_warnings_on_exit 1 src
"$LMWF_DIR_PATH/vendor/bin/phpcs" -qn --exclude=PSR1.Classes.ClassDeclaration --runtime-set ignore_warnings_on_exit 1 tests