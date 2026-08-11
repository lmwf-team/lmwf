#!/bin/bash

set -euo pipefail

BASEDIR=$(dirname $0)
phpunit -c "$BASEDIR/phpunit.xml" $@