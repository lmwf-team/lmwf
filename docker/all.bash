#!/bin/bash

set -euo pipefail

function all_fn {
    local sdir=$(dirname "${BASH_SOURCE[0]}")
    "$sdir/phpcs.bash"
    "$sdir/phpstan.bash"
    "$sdir/phpunit.bash"
}

all_fn