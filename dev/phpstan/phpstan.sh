#!/bin/bash

set -euo pipefail
SDIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../.." && pwd)"

"$SDIR/vendor/bin/phpstan" analyse -c "$SDIR/phpstan.neon" $@