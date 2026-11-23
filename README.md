# E-commerce Backend API

Laravel 13 / PHP 8.3+ / MySQL 8 / Sanctum / Redis / Docker Compose.

This repository is a production-oriented Laravel application scaffold for a Vue 3 storefront.
It includes a versioned REST API, database schema, authentication, product/category APIs,
cart and checkout foundations, inventory locking, order history, Docker/Nginx configuration,
seed data, tests, and deployment instructions.

> Important: payment gateway integrations require credentials, gateway-specific signature
> verification, and sandbox testing. The included payment interface is a safe integration
> boundary, not a claim that a real provider is already connected. Review `SECURITY.md`
> and complete the deployment checklist before production use.

## Requirements

- Docker Engine + Docker Compose plugin (recommended), or PHP 8.3+, Composer, MySQL 8
- Git
- Optional: Node.js only if you later add frontend assets

## Quick start (Docker)

```bash
cp .env.example .env
# Set a strong APP_KEY after the first build; see below.
docker compose build
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

API base URL: `http://localhost:8080/api/v1`

Default seeded admin:
- Email: `admin@example.com`
- Password: `ChangeMe123!`

Change this password immediately and do not use seeded credentials in production.
The seeder reads `SEED_ADMIN_EMAIL` and `SEED_ADMIN_PASSWORD` from the environment.

## Local development without Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure MySQL and Redis in .env
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

## Useful commands

```bash
php artisan route:list --path=api/v1
php artisan test
php artisan optimize:clear
php artisan queue:work
```

## API overview

| Method | Endpoint | Access |
|---|---|---|
| POST | `/api/v1/auth/register` | Public |
| POST | `/api/v1/auth/login` | Public |
| POST | `/api/v1/auth/logout` | Sanctum |
| GET | `/api/v1/auth/me` | Sanctum |
| GET | `/api/v1/products` | Public |
| GET | `/api/v1/products/{product}` | Public |
| GET | `/api/v1/categories` | Public |
| GET | `/api/v1/categories/{category}` | Public |
| GET/POST | `/api/v1/cart` | Sanctum |
| POST | `/api/v1/cart/items` | Sanctum |
| PATCH/DELETE | `/api/v1/cart/items/{cartItem}` | Sanctum |
| POST | `/api/v1/checkout` | Sanctum |
| GET | `/api/v1/orders` | Sanctum |
| GET | `/api/v1/orders/{order}` | Sanctum |
| POST | `/api/v1/orders/{order}/cancel` | Sanctum |
| GET | `/api/v1/wishlist` | Sanctum |
| POST/DELETE | `/api/v1/wishlist/{product}` | Sanctum |
| POST | `/api/v1/products/{product}/reviews` | Sanctum |
| GET | `/api/v1/admin/dashboard` | Admin |
| POST/PUT/DELETE | `/api/v1/admin/products...` | Admin |
| POST/PUT/DELETE | `/api/v1/admin/categories...` | Admin |

All API responses are JSON. Product list supports `search`, `category_id`, `featured`,
`sort` (`newest`, `price_asc`, `price_desc`, `best_selling`), `min_price`, `max_price`,
and `per_page`.

## Authentication

The API uses Laravel Sanctum personal access tokens. Send:

```http
Authorization: Bearer YOUR_TOKEN
Accept: application/json
```

For a separate Vue SPA on another domain, configure Sanctum stateful domains and CORS
if using cookie-based SPA authentication. This scaffold uses bearer tokens by default.

## Checkout behavior

- The server reloads products and prices from the database.
- Inventory rows are locked inside a database transaction.
- Stock is deducted only after the order data is created in the same transaction.
- Order item names, SKU, unit price and quantity are snapshotted.
- Cancellation restores stock only for eligible, non-terminal orders.
- COD is supported as a payment method.
- Online payment must be confirmed by a provider-signed webhook before marking paid.

## AWS EC2 deployment

1. Provision Ubuntu 24.04 EC2 and allow inbound TCP 80/443 only; restrict SSH to your IP.
2. Install Docker Engine and the Compose plugin from Docker's official repository.
3. Clone the private GitHub repository on EC2.
4. Create `.env` on the server; never commit it.
5. Set `APP_ENV=production`, `APP_DEBUG=false`, strong DB credentials, and a secure admin password.
6. Set `APP_URL=https://your-domain.example`.
7. Run `docker compose build` and `docker compose up -d`.
8. Run `docker compose exec app php artisan key:generate` only for a brand-new deployment.
   Preserve the same `APP_KEY` for later deployments.
9. Run `docker compose exec app php artisan migrate --force`.
10. Create/secure an admin account, then run `php artisan storage:link` if using public local storage.
11. Configure TLS at the host reverse proxy or add a certificate-managed HTTPS layer.
12. Verify logs: `docker compose logs -f app nginx`.

Never expose MySQL port 3306 publicly. Back up the database and uploaded files separately.
See `docs/DEPLOYMENT.md` and `SECURITY.md`.

## Project structure

```text
app/
  Http/Controllers/Api/V1/
  Http/Requests/
  Http/Resources/
  Models/
  Policies/
  Services/
database/
  migrations/
  seeders/
docker/nginx/
routes/api.php
tests/Feature/
docs/
```
