#!/bin/bash
set -euo pipefail
SDIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

docker compose cp "$SDIR/../dev/phpdoc/phpdoc.dist.xml" phpdoc_dsn:/data
docker compose up phpdoc_dsn
mkdir -p "$SDIR/../docs"
rm -rf "$SDIR/../docs/phpdoc"
mv "$SDIR/../src/docs/phpdoc" "$SDIR/../docs/phpdoc"
rm -f "$SDIR/../src/phpdoc.dist.xml"