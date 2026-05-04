# Deployment Guide

## Overview

This guide covers local, staging, and production deployment for the Dynamic Quiz System.

## 1. Prerequisites

- Docker Engine 24+
- Docker Compose v2+
- Ports `8000` (app) and `5432` (postgres) available

## 2. Environment Configuration

Copy and customize environment values:

```bash
cp .env.example .env
```

Required values in `.env`:

- `APP_NAME`
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `DB_CONNECTION=pgsql`
- `DATABASE_URL`

Recommended production values:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `LOG_LEVEL=info`
- strong DB passwords

## 3. Local Deployment

### Windows

```bat
setup.bat
```

### Linux/Mac

```bash
chmod +x setup.sh
./setup.sh
```

### Manual deployment

```bash
docker-compose up -d --build
docker-compose exec -T php composer install --no-interaction --prefer-dist
docker-compose exec -T php php artisan key:generate
docker-compose exec -T php php artisan migrate --force
docker-compose exec -T php php artisan storage:link
```

Access app at `http://localhost:8000`.

## 4. Health Verification

```bash
curl http://localhost:8000/health
curl http://localhost:8000/api/health
curl http://localhost:8000/api/status
```

Expected: HTTP 200 and JSON health payload.

## 5. Production Deployment Pattern

## 5.1 Build and start

```bash
docker-compose up -d --build
```

## 5.2 One-time setup

```bash
docker-compose exec -T php php artisan key:generate --force
docker-compose exec -T php php artisan migrate --force
docker-compose exec -T php php artisan config:cache
docker-compose exec -T php php artisan route:cache
docker-compose exec -T php php artisan view:cache
```

## 5.3 Runtime checks

```bash
docker-compose ps
docker-compose logs -f nginx
docker-compose logs -f php
docker-compose logs -f postgres
```

## 6. Zero-Downtime Update Sequence

1. Pull latest code.
2. Rebuild containers.
3. Run migrations.
4. Warm caches.
5. Run smoke tests.
6. Verify health endpoints.

Example:

```bash
git pull
docker-compose up -d --build
docker-compose exec -T php php artisan migrate --force
docker-compose exec -T php php artisan optimize
curl http://localhost:8000/api/health
```

## 7. Backup and Restore

## 7.1 Backup

```bash
docker-compose exec -T postgres pg_dump -U ${DB_USERNAME:-quiz_user} -d ${DB_DATABASE:-quiz} > backup.sql
```

## 7.2 Restore

```bash
docker-compose exec -T postgres psql -U ${DB_USERNAME:-quiz_user} -d ${DB_DATABASE:-quiz} < backup.sql
```

## 8. Security Checklist

- Set `APP_DEBUG=false` in production.
- Use non-default DB passwords.
- Restrict PostgreSQL port exposure if external access is unnecessary.
- Add TLS termination in front of Nginx (reverse proxy/load balancer).
- Rotate secrets regularly.

## 9. Rollback Plan

If a deployment fails:

1. Stop new rollout.
2. Restore previous image/version.
3. Restore database backup if migration introduced incompatible changes.
4. Clear and rebuild caches.

## 10. Useful Commands

```bash
docker-compose down
docker-compose down -v
docker-compose restart
docker-compose exec -T php php artisan test
```
