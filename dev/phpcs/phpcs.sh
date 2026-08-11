#!/bin/bash

set -euo pipefail
SDIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../.." && pwd)"

"$SDIR/vendor/bin/phpcs" -qn --runtime-set ignore_warnings_on_exit 1 src
"$SDIR/vendor/bin/phpcs" -qn --exclude=PSR1.Classes.ClassDeclaration --runtime-set ignore_warnings_on_exit 1 tests