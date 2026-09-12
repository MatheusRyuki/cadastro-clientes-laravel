# Docker / Sail

Laravel 13, PHP 8.5 e MySQL 8.4. PHP, Composer e Node ficam no container; no host só precisa de Docker.

Serviços: `laravel.test` + MySQL. Sem Redis nem Mailpit. Cache, fila e sessão usam o MySQL; e-mail vai para o log.

## Primeira vez

O `./vendor/bin/sail` só existe depois do `composer install`. Sem `vendor/` no clone, veja o README.

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

App: http://localhost:8080/customers

## Uso diário

```bash
./vendor/bin/sail up -d
./vendor/bin/sail down
./vendor/bin/sail logs -f
./vendor/bin/sail artisan ...
./vendor/bin/sail composer ...
./vendor/bin/sail npm run dev      # Vite em http://localhost:5173
./vendor/bin/sail npm run build
./vendor/bin/sail shell
./vendor/bin/sail test
```

## Portas

Definidas no `.env` (`APP_PORT`, `VITE_PORT`, `FORWARD_DB_PORT`):

- **8080** — app
- **5173** — Vite
- **3306** — MySQL (acesso do host)

## Banco

Dentro do Docker o host é `mysql`. Do host: `127.0.0.1:3306`.

- database: `laravel`
- user: `sail`
- password: `password`

Os testes usam o banco `testing` (criado pelo script do Sail no MySQL).

## Recriar do zero

```bash
./vendor/bin/sail down -v
./vendor/bin/sail up -d --build
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan storage:link
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm run build
```
