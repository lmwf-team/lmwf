#!/bin/bash

set -euo pipefail

BASEDIR=$(dirname $0)
vendor/bin/phpunit -c "$BASEDIR/phpunit.xml"