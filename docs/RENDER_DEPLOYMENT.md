# Render Deployment Guide for Quiz System with PostgreSQL

This guide provides step-by-step instructions for deploying the Laravel Quiz System to Render with PostgreSQL.

## Prerequisites

- GitHub account with your project pushed to a repository
- Render account (https://render.com)
- Docker knowledge (optional, helpful for troubleshooting)

---

## Phase 1: Prepare Your GitHub Repository

### Step 1: Push Your Project to GitHub

```bash
cd w:\Projects\quiz
git add .
git commit -m "Configure PostgreSQL for Render deployment"
git push -u origin main
```

### Step 2: Verify GitHub Repo

- Visit https://github.com/YOUR_USERNAME/quiz
- Confirm all files are present, especially:
  - `docker-compose.yml` (with PostgreSQL config)
  - `Dockerfile` (with PostgreSQL client)
  - `.env.example` (with `DB_CONNECTION=pgsql`)
  - `docker/postgres/init.sql`

---

## Phase 2: Set Up PostgreSQL Database on Render

### Step 3: Create PostgreSQL Database

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click **New +** → **PostgreSQL**
3. Configure:
   - **Name**: `quiz-db`
   - **Database**: `quiz`
   - **User**: `quiz_user`
   - **Region**: Choose based on your location (recommended: US East or EU West)
   - **Plan**: Free tier for development/testing
4. Click **Create Database**

### Step 4: Note Database Credentials

After creation, copy the **Internal Database URL** format:
```
postgresql://quiz_user:PASSWORD@quiz-db.cqrx3a.ng.render.com:5432/quiz
```

Or you can use these components separately:
- **Host**: `quiz-db.cqrx3a.ng.render.com`
- **Port**: `5432`
- **Database**: `quiz`
- **User**: `quiz_user`
- **Password**: (shown on Render dashboard)

⚠️ **Save these credentials** - you'll need them in the next steps.

---

## Phase 3: Create Web Service on Render

### Step 5: Create Web Service

1. Click **New +** → **Web Service**
2. **Connect GitHub Repository**:
   - Click **Connect your GitHub account**
   - Authorize Render
   - Select `quiz` repository
3. Configure:
   - **Name**: `quiz-app`
   - **Environment**: `Docker`
   - **Region**: Same as database (important for performance)
   - **Branch**: `main`
   - **Plan**: Standard or Pro (based on needs)

### Step 6: Configure Build Settings

In the Web Service settings:

1. **Build Command**:
   ```bash
   docker build -f Dockerfile -t quiz:latest .
   ```

2. **Start Command**:
   ```bash
   php artisan migrate --force && php-fpm
   ```

3. **Dockerfile Path**: `./Dockerfile` (default)

---

## Phase 4: Set Environment Variables

### Step 7: Add Environment Variables

In the **Environment** section of your Web Service, add all variables:

#### Required

```
APP_NAME=QuizSystem
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_URL=https://quiz-app.onrender.com

DB_CONNECTION=pgsql
DB_HOST=quiz-db.cqrx3a.ng.render.com
DB_PORT=5432
DB_DATABASE=quiz
DB_USERNAME=quiz_user
DB_PASSWORD=your_database_password_here
```

#### Optional (Recommended)

```
LOG_CHANNEL=stack
LOG_LEVEL=info
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
APP_TRUSTED_PROXIES=*
SESSION_SECURE_COOKIE=true
```

### Step 8: Generate APP_KEY

Run locally to generate a key:

```bash
php artisan key:generate --show
```

Copy the output (starts with `base64:`) and paste into `APP_KEY` variable.

---

## Phase 5: Configure Nginx

### Step 9: Create Render-Compatible Nginx Config

Create/update `docker/nginx/default.conf`:

```nginx
upstream php {
    server 127.0.0.1:9000;
}

server {
    listen 10000 default_server;
    listen [::]:10000 default_server;
    
    server_name _;
    
    root /var/www/html/public;
    index index.php;

    # Logs
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    # Performance
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

⚠️ **Important**: Render uses port 10000 by default. Ensure your Nginx listens on 10000.

### Step 10: Update Dockerfile Expose Port

Verify in `Dockerfile`:
```dockerfile
EXPOSE 10000
```

---

## Phase 6: Create Build Script

### Step 11: Create render-build.sh

Create `render-build.sh` in project root:

```bash
#!/bin/bash
set -e

echo "=== Installing Composer Dependencies ==="
composer install --no-dev --prefer-dist --no-interaction

echo "=== Generating APP Key ==="
php artisan key:generate --force

echo "=== Running Database Migrations ==="
php artisan migrate --force

echo "=== Caching Configuration ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Creating Storage Symlink ==="
php artisan storage:link || true

echo "=== Clearing Caches ==="
php artisan cache:clear
php artisan view:clear

echo "=== Build Complete! ==="
```

Make executable:
```bash
chmod +x render-build.sh
```

### Step 12: Update Build Command in Render

Change the Web Service **Build Command** to:
```bash
./render-build.sh
```

---

## Phase 7: Configure Health Checks

### Step 13: Add Health Check Endpoint

Ensure health endpoint exists in `routes/web.php`:

```php
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()], 200);
});
```

### Step 14: Set Health Check in Render

In Web Service settings:

- **Health Check Path**: `/health`
- **Health Check Protocol**: `HTTP`

---

## Phase 8: Deploy

### Step 15: Deploy to Render

1. Go to your Web Service on Render dashboard
2. Click **Manual Deploy**
3. Select **Deploy** from branch dropdown
4. Watch deployment progress in the **Logs** tab

**First deployment takes 10-20 minutes** (normal).

---

## Phase 9: Verify Deployment

### Step 16: Test Deployment

Once deployment shows "Live":

```bash
# Test health endpoint
curl https://quiz-app.onrender.com/health

# Test API status
curl https://quiz-app.onrender.com/api/health
curl https://quiz-app.onrender.com/api/status

# Should return JSON responses with HTTP 200
```

### Step 17: Check Logs

If deployment fails, check logs:

1. Go to Web Service → **Logs** tab
2. Look for error messages
3. Common issues:
   - Database connection failed → verify credentials
   - Out of memory → upgrade plan
   - Timeout → increase timeout in Render settings

---

## Phase 10: Post-Deployment Configuration

### Step 18: Enable Automatic Deployments

1. Go to Web Service → **Settings** tab
2. Under **Auto-Deploy**, select **Yes**
3. Now every git push to `main` triggers a deploy

### Step 19: Configure Custom Domain (Optional)

1. Go to Web Service → **Settings** → **Custom Domain**
2. Add your domain
3. Update DNS records per Render instructions

### Step 20: Monitor and Backup

Set up monitoring:

```bash
# View logs
curl https://quiz-app.onrender.com/api/status | jq .

# Setup automated database backups via Render dashboard
```

---

## Troubleshooting

### Issue: 503 Service Unavailable

**Solution**: 
- Check logs for database connection errors
- Verify database credentials in environment variables
- Ensure PostgreSQL service is running

```bash
# Test database from app
curl https://quiz-app.onrender.com/api/health
```

### Issue: Database Connection Refused

**Solutions**:
1. Verify database is in same region as web service
2. Check username and password are correct
3. Ensure `DB_CONNECTION=pgsql` is set
4. Wait 2-3 minutes after creating database

### Issue: Migrations Not Running

**Solution**:
```bash
# Manual migration trigger
# Via Render shell:
php artisan migrate --force
```

### Issue: Static Files Not Loading

**Solution**:
```bash
# Ensure storage symlink is created in build script
php artisan storage:link
```

### Issue: Out of Memory

**Solution**:
- Upgrade Render instance size
- Enable caching (Redis, optional)
- Optimize database queries

---

## Environment Variables Reference

| Variable | Example | Required |
|----------|---------|----------|
| `APP_NAME` | `QuizSystem` | Yes |
| `APP_ENV` | `production` | Yes |
| `APP_DEBUG` | `false` | Yes |
| `APP_KEY` | `base64:xxx` | Yes |
| `APP_URL` | `https://quiz-app.onrender.com` | Yes |
| `DB_CONNECTION` | `pgsql` | Yes |
| `DB_HOST` | `quiz-db.xxx.render.com` | Yes |
| `DB_PORT` | `5432` | Yes |
| `DB_DATABASE` | `quiz` | Yes |
| `DB_USERNAME` | `quiz_user` | Yes |
| `DB_PASSWORD` | `secure_password` | Yes |
| `LOG_LEVEL` | `info` | No |
| `CACHE_DRIVER` | `file` | No |

---

## Performance Tips

1. **Use Redis for Caching** (optional, paid add-on):
   ```
   CACHE_DRIVER=redis
   REDIS_URL=redis://...
   ```

2. **Enable Database Connection Pooling**:
   Add `?connect_timeout=10` to `DB_HOST`

3. **Use PostgreSQL Full-Text Search** for better search performance

4. **Monitor Database Performance**:
   - Keep table statistics updated
   - Use `EXPLAIN ANALYZE` for slow queries

---

## Backup and Restore

### Backup Database

```bash
# Via Render dashboard or CLI
pg_dump postgresql://quiz_user:password@host:5432/quiz > backup.sql
```

### Restore Database

```bash
psql postgresql://quiz_user:password@host:5432/quiz < backup.sql
```

---

## Security Checklist

- ✅ `APP_DEBUG=false` in production
- ✅ Strong database password (20+ characters)
- ✅ Use HTTPS only (Render provides auto SSL)
- ✅ Update `SESSION_SECURE_COOKIE=true`
- ✅ Set `APP_TRUSTED_PROXIES=*`
- ✅ Rotate secrets monthly
- ✅ Monitor logs for errors
- ✅ Enable database backups

---

## Next Steps

1. Monitor deployment in production
2. Set up error tracking (optional: Sentry, Rollbar)
3. Configure email notifications
4. Implement database backups
5. Plan for scaling (Redis, CDN)

---

## Support

- Render Docs: https://render.com/docs
- Laravel Docs: https://laravel.com/docs
- PostgreSQL Docs: https://postgresql.org/docs
- Project GitHub: https://github.com/YOUR_USERNAME/quiz

---

**Last Updated**: May 4, 2026  
**Tested On**: Render, Laravel 11, PostgreSQL 16, Docker
