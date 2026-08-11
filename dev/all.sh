#!/bin/bash
set -euo pipefail
SDIR=$(dirname "${BASH_SOURCE[0]}")

"$SDIR/psr12.sh"
"$SDIR/test.sh"
"$SDIR/stan.sh"