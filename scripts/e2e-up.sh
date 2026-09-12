#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

set -a
# shellcheck disable=SC1091
source .env
set +a

COMPOSE=(docker compose -f docker-compose.yml -f docker-compose.e2e.yml)
E2E_PORT="${E2E_APP_PORT:-8081}"
HEALTH_URL="http://127.0.0.1:${E2E_PORT}/__e2e/health"
FORBIDDEN=("laravel" "testing")

if ! docker info >/dev/null 2>&1; then
    echo "Docker não está acessível. Abra o Docker Desktop e ative a integração WSL." >&2
    exit 1
fi

if [[ ! -f .env ]]; then
    echo "Arquivo .env não encontrado. Copie .env.example e gere a APP_KEY." >&2
    exit 1
fi

APP_KEY="$(grep -E '^APP_KEY=' .env | cut -d= -f2-)"
if [[ -z "$APP_KEY" ]]; then
    echo "APP_KEY vazia no .env." >&2
    exit 1
fi

if [[ ! -f .env.e2e ]]; then
    cp .env.e2e.example .env.e2e
    TOKEN="$(openssl rand -hex 16)"
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env.e2e
    sed -i "s|^E2E_TOKEN=.*|E2E_TOKEN=${TOKEN}|" .env.e2e
    echo "Criado .env.e2e"
fi

grep -q '^APP_KEY=base64:' .env.e2e || sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env.e2e
if ! grep -qE '^E2E_TOKEN=.+' .env.e2e; then
    TOKEN="$(openssl rand -hex 16)"
    sed -i "s|^E2E_TOKEN=.*|E2E_TOKEN=${TOKEN}|" .env.e2e
fi

export E2E_TOKEN
E2E_TOKEN="$(grep -E '^E2E_TOKEN=' .env.e2e | cut -d= -f2-)"

mkdir -p storage/app/e2e-public
ln -sfn ../storage/app/e2e-public public/e2e-storage

"${COMPOSE[@]}" up -d mysql
echo "Aguardando MySQL..."
for _ in $(seq 1 30); do
    if "${COMPOSE[@]}" exec -T mysql mysqladmin ping -p"${DB_PASSWORD:-password}" --silent >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

"${COMPOSE[@]}" exec -T mysql mysql -uroot -p"${DB_PASSWORD:-password}" -e "CREATE DATABASE IF NOT EXISTS e2e CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON e2e.* TO 'sail'@'%'; FLUSH PRIVILEGES;"

"${COMPOSE[@]}" up -d laravel.e2e

echo "Migrando somente o banco e2e..."
sleep 3
"${COMPOSE[@]}" exec -T laravel.e2e php artisan e2e:migrate-fresh --force
"${COMPOSE[@]}" exec -T laravel.e2e php artisan config:clear

echo "Aguardando HTTP E2E em ${HEALTH_URL}..."
HEALTH=""
for _ in $(seq 1 40); do
    HEALTH="$(curl -fsS "$HEALTH_URL" || true)"
    if [[ -n "$HEALTH" ]]; then
        break
    fi
    sleep 2
done

if [[ -z "$HEALTH" ]]; then
    echo "Falha: a instância HTTP E2E não respondeu em ${HEALTH_URL}" >&2
    exit 1
fi

echo "Health HTTP: ${HEALTH}"

python3 - "$HEALTH" <<'PY'
import json, sys
payload = json.loads(sys.argv[1])
db = payload.get("database")
conn = payload.get("connection")
env = payload.get("env")
forbidden = {"laravel", "testing"}
if env != "e2e" or db != "e2e" or conn != "e2e":
    raise SystemExit(f"Isolamento HTTP recusado: env={env} database={db} connection={conn}")
if db in forbidden or conn in forbidden:
    raise SystemExit("Isolamento HTTP recusado: banco protegido")
print("Isolamento HTTP confirmado: banco e2e")
PY

echo "E2E pronto em ${HEALTH_URL}"
