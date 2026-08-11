#!/bin/sh

set -eu

BASEDIR=$(dirname $0)
phpunit -c "$BASEDIR/phpunit.xml"