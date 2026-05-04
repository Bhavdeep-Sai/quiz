# Troubleshooting Guide

## 1. Docker Compose Warning About `version`

Symptom:

- Compose warns that `version` is obsolete.

Status:

- Already fixed in `docker-compose.yml` by removing the top-level `version` key.

## 2. Containers Not Starting

Check:

```bash
docker-compose ps
docker-compose logs -f
```

Fixes:

- Ensure ports `8000` and `3306` are free.
- Rebuild containers:

```bash
docker-compose down
docker-compose up -d --build
```

## 3. MySQL Connection Refused

Check:

```bash
docker-compose logs -f mysql
```

Fixes:

- Wait until MySQL healthcheck is healthy.
- Confirm `.env` values:
  - `DB_HOST=mysql`
  - `DB_PORT=3306`
  - DB credentials match compose env.

## 4. Migration Fails

Run:

```bash
docker-compose exec -T php php artisan migrate:status
docker-compose exec -T php php artisan migrate --force
```

If schema is inconsistent in local dev:

```bash
docker-compose exec -T php php artisan migrate:fresh
```

## 5. 500 Internal Server Error

Check logs:

```bash
docker-compose logs -f php
docker-compose logs -f nginx
```

Common fixes:

- Clear caches:

```bash
docker-compose exec -T php php artisan optimize:clear
```

- Rebuild optimized caches:

```bash
docker-compose exec -T php php artisan config:cache
docker-compose exec -T php php artisan route:cache
docker-compose exec -T php php artisan view:cache
```

## 6. Permission Issues for `storage` or `bootstrap/cache`

Fix:

```bash
docker-compose exec -T php chown -R appuser:appuser /var/www/storage
docker-compose exec -T php chown -R appuser:appuser /var/www/bootstrap/cache
```

## 7. Tests Failing Due to Environment Drift

Run in clean state:

```bash
docker-compose exec -T php php artisan config:clear
docker-compose exec -T php php artisan test
```

If needed:

```bash
docker-compose down -v
docker-compose up -d --build
docker-compose exec -T php composer install
```

## 8. API Returns Validation Errors (422)

Expected behavior when request payload is invalid.

Action:

- Inspect `errors` object in JSON response.
- Ensure request structure matches `API_DOCUMENTATION.md`.

## 9. Quiz Not Visible in Public Listing

Checklist:

- Quiz `is_published` is true.
- Quiz has questions.
- You are calling the correct endpoint (`/quizzes` or `/api/v1/quizzes`).

## 10. Reset Local Environment Safely

```bash
docker-compose down -v
docker-compose up -d --build
docker-compose exec -T php composer install
docker-compose exec -T php php artisan key:generate
docker-compose exec -T php php artisan migrate
```
