# Clientes

CRUD de clientes em Laravel 13, para estudo local. Interface em português (pt-BR), sem autenticação.

Não use em produção.

## O que o app faz

- Listagem com busca, ordenação e paginação
- Criar, editar, ver e excluir cliente
- Drawer lateral para criar e editar sem sair da lista
- Split view no desktop: o preview abre ao lado da lista
- Command palette com `Ctrl+K` / `⌘K`
- Soft delete com lixeira (restaurar ou excluir de vez)
- Upload de foto, validação de formulário e toasts

## Requisitos

- [Docker](https://docs.docker.com/get-docker/) (PHP, Composer e Node rodam no Sail)

## Como subir

Na primeira vez (clone sem `vendor/`):

```bash
cp .env.example .env
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd)":/opt -w /opt laravelsail/php85-composer:latest composer install --ignore-platform-reqs
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

Se o Composer já estiver no host, `composer install` no lugar do `docker run` também serve para gerar o `./vendor/bin/sail`.

App: http://localhost:8080/customers

Detalhes de portas, banco e comandos do dia a dia: [DOCKER.md](DOCKER.md).

## Testes

```bash
./vendor/bin/sail test
```

## Stack

Laravel 13 · PHP 8.5 · MySQL 8.4 · Tailwind CSS · Alpine.js · Laravel Sail

## Licença

MIT. Veja [LICENSE](LICENSE).
