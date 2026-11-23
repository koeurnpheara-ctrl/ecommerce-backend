# Ubuntu AWS EC2 deployment

## 1. Security group

- SSH (22): your trusted public IP only.
- HTTP (80): public.
- HTTPS (443): public.
- Do not add inbound 3306, 6379, 9000, or 8080 in production unless there is a specific,
  secured reason. Prefer exposing 80/443 through a host reverse proxy or load balancer.

## 2. Install Docker

Use Docker's official Ubuntu installation instructions:
https://docs.docker.com/engine/install/ubuntu/

Install the Compose plugin:
https://docs.docker.com/compose/install/linux/

Verify:
```bash
docker --version
docker compose version
```

## 3. Deploy source

```bash
sudo apt update
sudo apt install -y git
git clone YOUR_PRIVATE_GITHUB_REPOSITORY ecommerce-backend
cd ecommerce-backend
cp .env.example .env
nano .env
```

Set production values. Use a strong database password and a secure one-time admin password.
Set `APP_DEBUG=false`, `APP_ENV=production`, and the real `APP_URL`.

## 4. Build and start

```bash
docker compose build --no-cache
docker compose up -d
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

Only run `key:generate` once on a fresh installation. Keep the key stable for future releases.
For a stricter deployment, build Composer dependencies into the image during CI and deploy
an immutable image rather than installing dependencies after startup.

## 5. HTTPS

Recommended: terminate TLS at an AWS Application Load Balancer with an ACM certificate,
or use a host-level Caddy/Nginx reverse proxy with Let's Encrypt. Forward HTTPS correctly
and configure trusted proxies. Do not expose the app's internal PHP-FPM or database ports.

## 6. Operations

```bash
docker compose ps
docker compose logs -f --tail=200 app
docker compose logs -f --tail=200 nginx
docker compose exec app php artisan about
docker compose exec app php artisan queue:work --tries=3
```

Back up MySQL and the `app_storage` volume. Test restoration regularly.
