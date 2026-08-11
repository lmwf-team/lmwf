#!/bin/bash

set -euo pipefail

BASEDIR=$(dirname $0)
${BASEDIR}/../../vendor/bin/phpstan analyse -c "$BASEDIR/phpstan.neon"