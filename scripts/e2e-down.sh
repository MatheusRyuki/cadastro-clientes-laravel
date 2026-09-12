#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

docker compose -f docker-compose.yml -f docker-compose.e2e.yml stop laravel.e2e
docker compose -f docker-compose.yml -f docker-compose.e2e.yml rm -f laravel.e2e

echo "Instância HTTP E2E encerrada. mysql e laravel.test foram preservados."
