#!/bin/sh

set -euo pipefail

phpcs -qn --runtime-set ignore_warnings_on_exit 1 src
phpcs -qn --exclude=PSR1.Classes.ClassDeclaration --runtime-set ignore_warnings_on_exit 1 tests