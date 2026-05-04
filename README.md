# Dynamic Quiz System

Production quiz platform built with Laravel 11, PostgreSQL 16, Nginx, PHP-FPM, and Docker.

## Tech Stack

- Backend: Laravel 11 / PHP 8.2
- Database: PostgreSQL 16
- Web server: Nginx
- App server: PHP-FPM
- Auth-ready package support: Sanctum
- Testing: Pest / PHPUnit
- **Deployment Target**: Render

## Project Structure

- `routes/web.php`: public pages and admin quiz management
- `routes/api.php`: versioned JSON API
- `app/Services`: quiz creation, evaluation, and scoring logic
- `app/QuestionTypes`: question strategy implementations
- `database/migrations`: quizzes, questions, options, attempts, answers, audit logs
- `docker-compose.yml`: local container stack

## Required Environment Variables

Create `.env` from `.env.example` and set:

- `APP_NAME`
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `APP_KEY`
- `APP_TIMEZONE`
- `DB_CONNECTION=pgsql`
- `DB_HOST`
- `DB_PORT=5432`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

Recommended production values:

- `APP_ENV=production`
- `APP_DEBUG=false`
- strong database passwords
- `LOG_LEVEL=info`

## Run Locally With Docker

### Windows

```bat
setup.bat
```

### Linux / macOS

```bash
chmod +x setup.sh
./setup.sh
```

### Manual Docker Steps

```bash
docker-compose up -d --build
docker-compose exec -T php composer install
docker-compose exec -T php php artisan key:generate
docker-compose exec -T php php artisan migrate --force
docker-compose exec -T php php artisan storage:link
```

Open:

- App: http://localhost:8000
- Health: http://localhost:8000/health
- API health: http://localhost:8000/api/health
- API docs: http://localhost:8000/api/docs

## Useful Commands

```bash
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f postgres
docker-compose exec -T php php artisan optimize:clear
docker-compose exec -T php php artisan test
docker-compose exec -T php php artisan migrate:fresh --seed
docker-compose down
```

## Troubleshooting

If the app does not start:

1. Confirm Docker is running.
2. Ensure ports `8000` and `5432` are free.
3. Rebuild containers with `docker-compose up -d --build`.
4. Check `docker-compose logs -f php` and `docker-compose logs -f postgres`.
5. Run `docker-compose exec -T php php artisan optimize:clear` if config or route caches are stale.

If the database is unavailable:

- Verify `DB_HOST=postgres` inside the Docker network.
- Wait for the PostgreSQL healthcheck to become healthy.
- Re-run migrations with `docker-compose exec -T php php artisan migrate --force`.
- Test connection: `docker-compose exec -T postgres psql -U quiz_user -d quiz -c "SELECT 1;"`

## Deployment

For production deployment to Render:

- **See [RENDER_DEPLOYMENT.md](docs/RENDER_DEPLOYMENT.md)** for complete step-by-step guide
- **See [POSTGRESQL_MIGRATION.md](docs/POSTGRESQL_MIGRATION.md)** for PostgreSQL migration details
- Database: Managed PostgreSQL on Render
- See [DEPLOYMENT_GUIDE.md](docs/DEPLOYMENT_GUIDE.md) for general deployment info

## Notes

- The public homepage is `/`.
- JSON API routes live under `/api` and `/api/v1`.
- Admin question CRUD now uses route-bound quiz and question parameters consistently.
